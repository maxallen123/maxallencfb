<?php
	require('updateFunctions.php');

	function sqlConnect() {
		$connectionOptions = array(
			"Database" => "football",
			"UID" => "sa",
			"PWD" => "MWXH!9j&@bS2b5M",
			"Encrypt" => 1,
			"TrustServerCertificate" => 1,
			"APP" => "football"
		);
		$conn = sqlsrv_connect("localhost", $connectionOptions);
		if( $conn === false ) {
			echo "Could not connect.\n";
			die( print_r( sqlsrv_errors(), true));
		}
		return $conn;
	}

	function processTeam($dbConn, $team) {
		// SQL Query Strings
		$checkQuery       = 'SELECT * FROM teams 
								WHERE 
								id = ?';
		$updtQuery        = 'UPDATE teams SET
								uid = ?, slug = ?, location = ?, name = ?, nickname = ?, abbreviation = ?, displayName = ?, shortDisplayName = ?, color = ?, alternateColor = ? 
								WHERE
								id = ?';
		$newQuery         = 'INSERT INTO teams 
								(uid, slug, location, name, nickname, abbreviation, displayName, shortDisplayName, color, alternateColor, id) 
								VALUES 
								(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$deleteLogosQuery = 'DELETE FROM teamLogos 
								WHERE 
								teamId = ?';
		$deleteLinksQuery = 'DELETE FROM teamLinks 
								WHERE 
								teamId = ?';
		$addLogosQuery    = 'INSERT INTO teamLogos 
								(teamId, href, width, height, desc_1, desc_2, img) 
								VALUES
								(?, ?, ?, ?, ?, ?, ?)';
		$addLinksQuery    = 'INSERT INTO teamLinks 
								(teamId, href, text) 
								VALUES 
								(?, ?, ?)';

		// Prep Variables
		$id                 = $team->id;
		$uid                = $team->uid;
		$slug               = $team->slug;
		$location           = $team->location;
		$name               = $team->name;
		$nickname           = $team->nickname;
		$abbr               = $team->abbreviation;
		$displayName        = $team->displayName;
		$shortDisplayName   = $team->shortDisplayName;

		// Load Colors, if defined
		if(isset($team->color)) {
			$color          = $team->color;
		} else {
			$color          = NULL;
		}
		if(isset($team->alternateColor)) {
			$alternateColor = $team->alternateColor;
		} else {
			$alternateColor = NULL;
		}

		// Query Arrays
		$idArray   = array($id);
		$teamArray = array(
			$uid, 
			$slug, 
			$location, 
			$name, 
			$nickname, 
			$abbr, 
			$displayName, 
			$shortDisplayName, 
			$color, 
			$alternateColor, 
			$id
		);

		// Check if row already exists for team
		if(sqlsrv_has_rows(sqlsrv_query($dbConn, $checkQuery, $idArray))) {
			// If exists, do update:
			sqlsrv_query($dbConn, $updtQuery, $teamArray);
		} else {
			// If does not exist, create new row:
			sqlsrv_query($dbConn, $newQuery, $teamArray);
		}

		// Go ahead and delete logos and links, we'll recreate them:
		sqlsrv_query($dbConn, $deleteLogosQuery, $idArray);
		sqlsrv_query($dbConn, $deleteLinksQuery, $idArray);

		// Add logos (if they exist)
		if(isset($team->logos)) {
			// Loop through each logo...
			foreach($team->logos as $logo) {
				// Download logo
				$logoImg = 'data:' . get_headers($logo->href, true)['Content-Type'] . ';base64,' . base64_encode(file_get_contents($logo->href));

				// Create array for query
				$logoArray = array(
					$id, 
					$logo->href, 
					$logo->width, 
					$logo->height, 
					$logo->rel[0], 
					$logo->rel[1],
					$logoImg
				);

				// Add logo
				sqlsrv_query($dbConn, $addLogosQuery, $logoArray);
			}
		}

		// Add links (if they exist)
		if(isset($team->links)) {
			// Go through each one
			foreach($team->links as $key => $link) {
				// Only pull valid web addresses
				if(substr($link->href, 0, 4) == 'http' && $key != 0) {
					$linkArray = array(
						$id,
						$link->href,
						$link->text
					);
					sqlsrv_query($dbConn, $addLinksQuery, $linkArray);
				}
			}
		}
	}

	class team {
		function __construct($id, $displayName, $shortDisplayName, $abbreviation, $comedyName) {
			$this->id                 = $id;
			$this->displayName        = $displayName;
			$this->shortDisplayName   = $shortDisplayName;
			$this->abbreviation       = $abbreviation;
			$this->wins               = 0;
			$this->losses             = 0;
			$this->pointsFor          = 0;
			$this->pointsForArray     = array();
			$this->pointsAgainst      = 0;
			$this->pointsAgainstArray = array();
			$this->opponents          = array();

			if($comedyName != NULL) {
				$this->displayName = $comedyName;
			}
		}

		function addGame($game) {
			// Determine if we are home or away
			if($game['homeId'] == $this->id) {
				$us = 'home';
				$them = 'away';
			} else {
				$us = 'away';
				$them = 'home';
			}

			// Did we win?
			if($game[$us . 'Score'] > $game[$them . 'Score']) {
				$this->wins++;
			} else {
				$this->losses++;
			}

			$this->pointsFor     += $game[$us . 'Score'];						// Add our points
			$this->pointsAgainst += $game[$them . 'Score'];						// Add their points
			array_push($this->pointsForArray, $game[$us . 'Score']);			// Add our score to array
			array_push($this->pointsAgainstArray, $game[$them . 'Score']);		// Add their score to array
			array_push($this->opponents, $game[$them . 'Id']);					// Add opponent to list
		}
	}

	function loadTeamArray($dbConn) {
		// Prep Queries
		$loadQuery = 'SELECT 
						id, displayName, shortDisplayName, abbreviation, comedyName 
						FROM teams';
		$gamesQuery = 'SELECT 
						id, homeId, awayId, homeScore, awayScore 
						FROM games WHERE 
						completed = 1 AND isCancelled = 0';
		
		// Get teams from SQL
		$teamRsrc = sqlsrv_query($dbConn, $loadQuery);

		// Proceed through the list
		$teams = array();
		while($team = sqlsrv_fetch_array($teamRsrc)) {
			$teams[$team['id']] = new team($team['id'], $team['displayName'], $team['shortDisplayName'], $team['abbreviation'], $team['comedyName']);
		}

		// Get games from SQL
		$gameRsrc = sqlsrv_query($dbConn, $gamesQuery);
		
		// Proceed through the list
		while($game = sqlsrv_fetch_array($gameRsrc)) {
			$teams[$game['homeId']]->addGame($game);
			$teams[$game['awayId']]->addGame($game);
		}

		return $teams;
	}

	class game {
		function __construct($game) {
			foreach($game as $columnKey => $value) {
				$this->$columnKey = $value;
			}
			$this->day       = $game['date']->format('n/j');
			$this->time      = $game['date']->format('g:i A');

			// Handle favorites if no line or if pick
			if($this->favorite == -1 || $this->favorite == NULL) {
				$this->tableFav = $this->homeId;
				$this->tableDog = $this->awayId;
			} else {									// Otherwise table should show the actual
				$this->tableFav = $this->favorite;
				$this->tableDog = $this->underdog;
			}
			if($this->spread == -1) {					// If odds are even then show PICK
				$spread = 'PICK';
			} else if($this->spread != NULL) {			// Otherwise if odds exist, set the spread to be format negative score
				$this->spread = 0 - $this->spread;
			}

			if($this->tableFav == $this->homeId) {		// Link ranks to favorite/underdog
				$this->rankFav  = $this->homeRank;
				$this->rankDog  = $this->awayRank;
				$this->scoreFav = $this->homeScore;
				$this->scoreDog = $this->awayScore;
			} else {
				$this->rankFav  = $this->awayRank;
				$this->rankDog  = $this->homeRank;
				$this->scoreFav = $this->awayScore;
				$this->scoreDog = $this->homeScore;
			}
		}
	}

	function loadGames($dbConn, $year, $week) {
		// Set up query strings
		$loadGamesQuery = 'SELECT 
							id, date, name, homeId, awayId, favorite, underdog, spread, network, homeRank, awayRank, winnerId, homeScore, awayScore, status 
							FROM games WHERE
							week = ? AND year = ?
							ORDER BY DATE DESC';

		// Get games
		$games = sqlsrv_query($dbConn, $loadGamesQuery, array($week, $year));
		$gameArray = array();
		
		// Need to do picks in order, tracking them
		$last = -1;
		$next = 0;

		while ($game = sqlsrv_fetch_array($games, SQLSRV_FETCH_ASSOC)) {
			if($last != -1) {
				$gameArray[$last]->last = $game['id'];
			}
			$game['next'] = $next;
			$last = array_push($gameArray, new game($game)) - 1;
			$next = $game['id'];
		}
		$gameArray[$last]->last = 0;
		return $gameArray;
	}

	function fetchLogo($dbConn, $teamId) {
		// Query
		$logoQuery = "SELECT 
						href
						FROM teamLogos WHERE
						desc_2 = 'dark' AND teamId = ?";
		
		$logo = sqlsrv_query($dbConn, $logoQuery, array($teamId));
		
		return sqlsrv_fetch_array($logo)['href'];
	}

	function loadPicks($dbConn, $year, $week) {
		// Set up query
		$picksQuery = 'SELECT 
						*
						FROM picks WHERE
						year = ? AND week = ?';
		
		// Load picks
		$queryArray = array($year, $week);
		$picks = sqlsrv_query($dbConn, $picksQuery, $queryArray);

		// Load games (edited to return all games, to simplify JS)
		$gamesArray = loadGames($dbConn, $year, $week);

		// Load teams
		$teamArray = loadTeamArray($dbConn, $year, $week);

		// Prep array
		$picksArray = array();
		foreach($gamesArray as $game) {
			for($userId = 0; $userId <= 3; $userId++) {
				$picksArray[$game->id][$userId] = -1;
			}
			// Load game into array for JS functions
			$picksArray[$game->id]['game'] = $game;

			// If line doesnt exist or line is NULL then replace favorite with home/dog with away
			if(($game->spread) == NULL || $game->spread == 0) {
				$game->favorite = $game->homeId;
				$game->underdog = $game->awayId;
			}

			// Load team info into array as well
			$picksArray[$game->id]['game']->fav = $teamArray[$game->favorite];
			$picksArray[$game->id]['game']->dog = $teamArray[$game->underdog];
		}

		// Load selected picks
		if(sqlsrv_has_rows($picks)) {
			while($pick = sqlsrv_fetch_array($picks)) {
				$picksArray[$pick['gameId']][$pick['userId']] = $pick['pick'];
			}
		}


		return $picksArray;
	}

// Function to update the database for a given week, does not return anything
	function updateWeek($dbConn, $year, $week) {
		// Set up query
		$pullGames   = 'SELECT * 
						FROM games 
						WHERE year = ? AND week = ?';
		
		// Set up query array to pull games
		$pullArray = array($year, $week);

		// Pull games
		$games = sqlsrv_query($dbConn, $pullGames, $pullArray);

		// Pull teamArray so we can check for D1 teams
		$teamArray = loadTeamArray($dbConn);

		// Build an array of existing games
		$gamesArray = array();
		if(sqlsrv_has_rows($games)) {
			while($sqlGame = sqlsrv_fetch_array($games, SQLSRV_FETCH_ASSOC)) {
				$gamesArray[$sqlGame['id']] = new game($sqlGame);
			}
		}
	
		$scoreboard = pullScoreboard($year, $week);

		// Go through the ESPN scoreboard...
		foreach($scoreboard->events as $game) {
			$gameId = $game->id;							// We're going to reference this a lot
			// Make sure both teams are D1
			if(isset($teamArray[$game->competitions[0]->competitors[0]->id]) && isset($teamArray[$game->competitions[0]->competitors[1]->id])) {
				if(isset($gamesArray[$gameId])) {				// If we already have the game in our database...
					if(!($gamesArray[$gameId]->completed)) {	// If we haven't completed it, we'll update (otherwise, nothing)
						updateGame($dbConn, $game, $gamesArray[$gameId]);
					}
				} else {										// If we don't have the game in our DB, we'll create it
					newGame($dbConn, $gameId, $year, $week);
					$gamesArray[$gameId]['favorite'] = NULL;	// Only place we use sql info is if copying the line if game is in progress or cancelled, so only params we need to set
					$gamesArray[$gameId]['underdog'] = NULL;
					$gamesArray[$gameId]['spread']   = NULL;
					updateGame($dbConn, $game, $gamesArray[$gameId]);
				}
			}
		}
	}

?>