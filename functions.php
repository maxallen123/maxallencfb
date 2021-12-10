<?php
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

	function addGame($dbConn, $game, $year, $seasonType, $week) {
		// Set up query strings
		$checkQuery          = 'SELECT * FROM games 
									WHERE 
									id = ?';
		$newQuery            = 'INSERT INTO games 
									(id, year, week, date, name, homeId, awayId)
									VALUES 
									(?, ?, ?, ?, ?, ?, ?)';
		$updateCompleteQuery = 'UPDATE games SET 
									completed                 = ?,	-- 0
									isCancelled               = ?,	-- 1
									isNeutral                 = ?,	-- 2
									isConference              = ?,	-- 3
									homeScore                 = ?,	-- 4
									awayScore                 = ?,	-- 5
									winnerId                  = ?,	-- 6
									loserId                   = ?,	-- 7
									homeYards                 = ?,	-- 8
									homeRushingPlays          = ?,	-- 9
									homeRushingYards          = ?,	-- 10
									homePassingAttempts       = ?,	-- 11
									homePassingComp           = ?,	-- 12
									homePassingYards          = ?,	-- 13
									homePenalties             = ?,	-- 14
									homePenaltyYards          = ?,	-- 15
									homeTurnoversFumble       = ?,	-- 16
									homeTurnoversInt          = ?,	-- 17
									homeFirstDowns            = ?,	-- 18
									homeThirdDownAttempts     = ?,	-- 19
									homeThirdDownConversions  = ?,	-- 20
									homeFourthDownAttempts    = ?,	-- 21
									homeFourthDownConversions = ?,	-- 22
									homePossessionTime        = ?,	-- 23
									awayYards                 = ?,	-- 24
									awayRushingPlays          = ?,	-- 25
									awayRushingYards          = ?,	-- 26
									awayPassingAttempts       = ?,	-- 27
									awayPassingComp           = ?,	-- 28
									awayPassingYards          = ?,	-- 29
									awayPenalties             = ?,	-- 30
									awayPenaltyYards          = ?,	-- 31
									awayTurnoversFumble       = ?,	-- 32
									awayTurnoversInt          = ?,	-- 33
									awayFirstDowns            = ?,	-- 34
									awayThirdDownAttempts     = ?,	-- 35
									awayThirdDownConversions  = ?,	-- 36
									awayFourthDownAttempts    = ?,	-- 37
									awayFourthDownConversions = ?,	-- 38
									awayPossessionTime        = ? 	-- 39
									WHERE id = ?					-- 40';
		
		// Set some common preliminary variables
		$gameId = $game->id;
		$homeId = $game->competitions[0]->competitors[0]->id;
		$awayId = $game->competitions[0]->competitors[1]->id;
		echo $gameId . "\n";

		// Check and make sure game isn't already in DB, if not, add the game
		$sqlGame = sqlsrv_query($dbConn, $checkQuery, array($gameId));
		if(!sqlsrv_has_rows($sqlGame)) {
			$queryArray = array();

			// Set Variables
			$queryArray[0] = $gameId;										// Game ID
			$queryArray[1] = $year;											// Season
			if($seasonType == 3) {											// If bowl, assign week 20
				$week = 20;
			}
			$queryArray[2] = $week;											// Week
			$queryArray[3] = date('Y-m-d H:i:s', (strtotime($game->date) + 60 * 60));
			if(isset($game->competitions[0]->notes[0]->headline)) {			// Name, if exists
				$queryArray[4] = $game->competitions[0]->notes[0]->headline;
			} else {
				$queryArray[4] = NULL;
			}
			$queryArray[5] = $homeId;	// Home Team ID
			$queryArray[6] = $awayId;	// Away Team ID

			// Add Game
			sqlsrv_query($dbConn, $newQuery, $queryArray);
			$queryArray = array();
			$completed = 0;													// Use this later to check if game was already completed
		} else {
			if(sqlsrv_fetch_array($sqlGame)['completed'] == 1) {
				$completed = 1;
			} else {
				$completed = 0;
			}
		}

		$gameStatus = $game->status->type->id;
		/* Status Flags:
			1: Scheduled
			2: ??? (In progress?)
			3: Complete
			4: Forfeit
			5: Cancelled
			6: Postponed */
		// If game is completed...
		if($gameStatus >= 3 && $completed != 1) {
			// Prep Array
			$queryArray = array();
			for($x = 0; $x <=39; $x++) {
				$queryArray[$x] = NULL;
			}

			// Set variables
			$queryArray[40] = $gameId;											// Set the game ID first
			$queryArray[0] = true;													// Game is completed
			if($gameStatus != 3) {													// If status isn't 3, then game didn't happen
				$queryArray[1] = true;
			} else {
				$queryArray[1] = false;
			}
			// Don't need to set other variables if game didn't happen...
			if(!$queryArray[1]) {
				$queryArray[2] = $game->competitions[0]->neutralSite; 				// Set isNeutral
				$queryArray[3] = $game->competitions[0]->conferenceCompetition;		// Set whether it's in conference or OOC
				$queryArray[4] = $game->competitions[0]->competitors[0]->score;		// Home score
				$queryArray[5] = $game->competitions[0]->competitors[1]->score;		// Away score
				if($queryArray[4] > $queryArray[5]) {								// Set winners and losers
					$queryArray[6] = $homeId;
					$queryArray[7] = $awayId;
				} else {
					$queryArray[6] = $awayId;
					$queryArray[7] = $homeId;
				}

				// Get game details
				$gameUrl = "http://site.api.espn.com/apis/site/v2/sports/football/college-football/summary?event=" . $gameId;
				$sum = json_decode(file_get_contents($gameUrl));
				for($x = 0; $x <= 1; $x++) {										// Cycle through each team
					if($sum->boxscore->teams[$x]->team->id == $homeId) {			// Set offset for query params
						$offset = 0;
					} else {
						$offset = 16;
					}
					if(isset($sum->boxscore->teams[$x]->statistics[ 3])) { 										// Check to see if we even have stats!
						$queryArray[ 8 + $offset] = $sum->boxscore->teams[$x]->statistics[ 3]->displayValue;	// Set total yards
						$queryArray[ 9 + $offset] = $sum->boxscore->teams[$x]->statistics[ 8]->displayValue;	// Set rushing plays
						$queryArray[10 + $offset] = $sum->boxscore->teams[$x]->statistics[ 7]->displayValue;	// Set rushing yards
						$passing = explode('-',     $sum->boxscore->teams[$x]->statistics[ 5]->displayValue);	// Need to explode the passing stat because it's comp-attempts
						$queryArray[11 + $offset] = $passing[1];												// Set Passing Attempts
						$queryArray[12 + $offset] = $passing[0];												// Set Passing Completions
						$queryArray[13 + $offset] = $sum->boxscore->teams[$x]->statistics[ 4]->displayValue;	// Set Passing Yards
						$penalties = explode('-',   $sum->boxscore->teams[$x]->statistics[10]->displayValue);	// Need to explode penalties because its # penalties-yards
						$queryArray[14 + $offset] = $penalties[0];												// Set Penalties
						$queryArray[15 + $offset] = $penalties[1];												// Set Penalty Yards
						$queryArray[16 + $offset] = $sum->boxscore->teams[$x]->statistics[12]->displayValue;	// Set fumbles
						$queryArray[17 + $offset] = $sum->boxscore->teams[$x]->statistics[13]->displayValue;	// Set INTs
						$queryArray[18 + $offset] = $sum->boxscore->teams[$x]->statistics[ 0]->displayValue;	// Set first downs
						$thirdDowns = explode('-',  $sum->boxscore->teams[$x]->statistics[ 1]->displayValue);	// Explode third downs, conversions-attempts
						$queryArray[19 + $offset] = $thirdDowns[1];												// Set third down attempts
						$queryArray[20 + $offset] = $thirdDowns[0];												// Set third down conversions
						$fourthDowns = explode('-', $sum->boxscore->teams[$x]->statistics[ 2]->displayValue);	// Explode fourth downs, conversions-attempts
						$queryArray[21 + $offset] = $fourthDowns[1];											// Set fourth down attempts
						$queryArray[22 + $offset] = $fourthDowns[0];											// Set fourth down conversions
						$possession = explode(':', $sum->boxscore->teams[$x]->statistics[14]->displayValue);	// Explode time of possession
						$possessionTime = $possession[0] * 60 + $possession[1];									// Set time of possesion in seconds
						$queryArray[23 + $offset] = $possessionTime;											// Store time of possession
					}
				}
			}
			// We're done setting variables
			// FINALLY STORE IN DATABASE
			sqlsrv_query($dbConn, $updateCompleteQuery, $queryArray);
		}
	}

	class team {
		function __construct($id, $displayName, $shortDisplayName) {
			$this->id               = $id;
			$this->displayName      = $displayName;
			$this->shortDisplayName = $shortDisplayName;
			$this->pointsFor        = array();
		}
	}
?>