<?php

	// Generate query to update games
	function setUpdateQuery() {
		return 'UPDATE games SET 
					date                      = ?,  -- 0
					name                      = ?,  -- 1
					network                   = ?,  -- 2
					homeId                    = ?,  -- 3
					awayId                    = ?,  -- 4
					homeRank                  = ?,  -- 5
					awayRank                  = ?,  -- 6
					status                    = ?,  -- 7
					completed                 = ?,	-- 8
					isCancelled               = ?,	-- 9
					isNeutral                 = ?,	-- 10
					isConference              = ?,	-- 11
					winnerId                  = ?,	-- 12
					loserId                   = ?,	-- 13
					homeScore                 = ?,  -- 14
					homeYards                 = ?,	-- 15
					homeRushingPlays          = ?,	-- 16
					homeRushingYards          = ?,	-- 17
					homePassingAttempts       = ?,	-- 18
					homePassingComp           = ?,	-- 19
					homePassingYards          = ?,	-- 20
					homePenalties             = ?,	-- 21
					homePenaltyYards          = ?,	-- 22
					homeTurnoversFumble       = ?,	-- 23
					homeTurnoversInt          = ?,	-- 24
					homeFirstDowns            = ?,	-- 25
					homeThirdDownAttempts     = ?,	-- 26
					homeThirdDownConversions  = ?,	-- 27
					homeFourthDownAttempts    = ?,	-- 28
					homeFourthDownConversions = ?,	-- 29
					homePossessionTime        = ?,	-- 30
					awayScore                 = ?,	-- 31
					awayYards                 = ?,	-- 32
					awayRushingPlays          = ?,	-- 33
					awayRushingYards          = ?,	-- 34
					awayPassingAttempts       = ?,	-- 35
					awayPassingComp           = ?,	-- 36
					awayPassingYards          = ?,	-- 37
					awayPenalties             = ?,	-- 38
					awayPenaltyYards          = ?,	-- 39
					awayTurnoversFumble       = ?,	-- 40
					awayTurnoversInt          = ?,	-- 41
					awayFirstDowns            = ?,	-- 42
					awayThirdDownAttempts     = ?,	-- 43
					awayThirdDownConversions  = ?,	-- 44
					awayFourthDownAttempts    = ?,	-- 45
					awayFourthDownConversions = ?,	-- 46
					awayPossessionTime        = ?, 	-- 47
					favorite                  = ?,  -- 48
					underdog                  = ?,  -- 49
					spread                    = ?,  -- 50
					href                      = ?   -- 51
				WHERE id = ?						-- 52';
	}

// Return basic info on team
	function fetchTeamInfo($dbConn, $teamId) {
		$fetchQuery = 'SELECT 
						id, displayName, shortDisplayName, abbreviation
						FROM teams
						WHERE id = ?';

		$team = sqlsrv_query($dbConn, $fetchQuery, array($teamId));
		return sqlsrv_fetch_array($team);
	}

// Function to pull the ESPN scoreboard for a given week, returns decoded json object
	function pullScoreboard($year, $week) {
		// Pull the scoreboard from ESPN for the week
		if($week == 20) {		// We're doing this different from ESPN, so if we use $week = 20, we mean bowl games
			$espnWeek   = 1;
			$seasonType = 3;
		} else {
			$espnWeek   = $week;
			$seasonType = 2;
		}
		/* Note on seasonType:
			1: Preseason (doesn't exist for CFB)
			2: Regular Season
			3: Bowl Games/Post-Season
			4: All Star Games/Offseason (Senior Bowl, etc.)
			5: 2020 Only: Spring FCS Football
			6: 2020 Only: Spring Postseason FCS Football */

		$limit = 199;			// Scoreboard doesn't always respond back, so we use limit to change the request and try again
		$json  = '';
		do {
			$limit++;			// Increment each request
			$scoreboardUrl = 'http://site.api.espn.com/apis/site/v2/sports/football/college-football/scoreboard?'
								. 'groups=90'						// Group 90 = All D1, FBS and FCS
								. '&dates=' . $year 				// Dates = Year for the season
								. '&seasontype='. $seasonType 		// Season Types - see above
								. '&week=' . $espnWeek 					// Week of seasonType (1-15 or 16 for regular season, 1 for bowls)
								. '&limit=' . $limit;				// How many games to return
			$json          = @file_get_contents($scoreboardUrl);	// @ to suppress errors, get json from ESPN
		} while(strlen($json) < 1000);								// Checking to make sure we got enough data, but we can't go larger because if one of the weeks didn't happen...
		$scoreboard = json_decode($json);

		return $scoreboard;
	}

// Set gate date, name, and broadcast network
	function updateDateNameNetwork($sbGame, $queryArray) {
		$queryArray[0] = date('Y-m-d H:i:s', (strtotime($sbGame->date)));		// Date, adjust to eastern time
		if(isset($sbGame->competitions[0]->notes[0]->headline)) {						// Name, if exists
			$queryArray[1] = $sbGame->competitions[0]->notes[0]->headline;
		}
		if(isset($sbGame->competitions[0]->broadcasts[0]->names[0])) {					// Set Network name
			$queryArray[2] = $sbGame->competitions[0]->broadcasts[0]->names[0];
		}

		return $queryArray;
	}

//Set home and away id's
	function updateHomeAway($sbGame, $queryArray) {
		if($sbGame->competitions[0]->competitors[0]->homeAway == 'home') {
			$queryArray[3] = $sbGame->competitions[0]->competitors[0]->id;
			$queryArray[4] = $sbGame->competitions[0]->competitors[1]->id;
		} else {
			$queryArray[4] = $sbGame->competitions[0]->competitors[0]->id;
			$queryArray[3] = $sbGame->competitions[0]->competitors[1]->id;
		}

		return $queryArray;
	}

// Get ranks for teams
	function updateRanks($sbGame, $queryArray) {
		// Determine which competitor is home, then set rank
		if(isset($sbGame->competitions[0]->competitors[0]->curatedRank->current) && isset($sbGame->competitions[0]->competitors[1]->curatedRank->current)) {
			if($sbGame->competitions[0]->competitors[0]->id == $queryArray[3]) {
				$queryArray[5] = $sbGame->competitions[0]->competitors[0]->curatedRank->current;
				$queryArray[6] = $sbGame->competitions[0]->competitors[1]->curatedRank->current;
			} else {
				$queryArray[6] = $sbGame->competitions[0]->competitors[0]->curatedRank->current;
				$queryArray[5] = $sbGame->competitions[0]->competitors[1]->curatedRank->current;
			}
		}

		// Scoreboard uses 99 to indicate unranked. 
		if($queryArray[5] == 99) {
			$queryArray[5] = NULL;
		}
		if($queryArray[6] == 99) {
			$queryArray[6] = NULL;
		}

		return $queryArray;
	}

// Get spread/favorite/etc.
	function updateSpreadPregame($dbConn, $sbGame, $queryArray) {
		$homeTeam = fetchTeamInfo($dbConn, $queryArray[3]);
		$awayTeam = fetchTeamInfo($dbConn, $queryArray[4]);

		// Update betting lines
		if(isset($sbGame->competitions[0]->odds[0]->details)) {
			$odds = explode(' ', $sbGame->competitions[0]->odds[0]->details);
			if($odds[0] == 'EVEN') {
				$queryArray[48] = -1;		// No favorite
				$queryArray[49] = -1;		// No underdog
				$queryArray[50] = 0;		// Spread is 0
			} else {
				if($odds[0] == $homeTeam['abbreviation']) {		// If favorite is home
					$queryArray[48] = $queryArray[3];
					$queryArray[49] = $queryArray[4];
					$queryArray[50] = abs($odds[1]);
				} else {										// If favorite is away
					$queryArray[48] = $queryArray[4];
					$queryArray[49] = $queryArray[3];
					$queryArray[50] = abs($odds[1]);
				}
			}
		}

		return $queryArray;
	}

// Copy existing spread, since it is no longer in scoreboard and game isn't complete or cancelled
	function copySpread($sqlGame, $queryArray) {
		$queryArray[48] = $sqlGame['favorite'];
		$queryArray[49] = $sqlGame['underdog'];
		$queryArray[50] = $sqlGame['spread'];

		return $queryArray;
	}

// Get scores for game (in progress or complete)
	function updateScore($sbGame, $queryArray) {
		if($sbGame->competitions[0]->competitors[0]->id == $queryArray[3]) {
			$queryArray[14] = $sbGame->competitions[0]->competitors[0]->score;
			$queryArray[31] = $sbGame->competitions[0]->competitors[1]->score;
		} else {
			$queryArray[31] = $sbGame->competitions[0]->competitors[0]->score;
			$queryArray[14] = $sbGame->competitions[0]->competitors[1]->score;
		}

		return $queryArray;
	}

// Set the game as cancelled
	function setCancelled($sbGame, $queryArray) {
		$queryArray[8] = 1;		// Set complete
		$queryArray[9] = 1;		// Set cancelled

		return $queryArray;
	}

// Get the winner
	function determineWinner($sbGame, $queryArray) {
		if($sbGame->competitions[0]->competitors[0]->winner == true) {
			if($sbGame->competitions[0]->competitors[0]->id == $queryArray[3]) {
				$queryArray[12] = $queryArray[3];
				$queryArray[13] = $queryArray[4];
			} else {
				$queryArray[12] = $queryArray[4];
				$queryArray[13] = $queryArray[3];
			} 
		} else { 
			if($sbGame->competitions[0]->competitors[0]->id == $queryArray[3]) {
				$queryArray[12] = $queryArray[4];
				$queryArray[13] = $queryArray[3];
			} else {
				$queryArray[12] = $queryArray[3];
				$queryArray[13] = $queryArray[4];
			}
		}

		return $queryArray;
	}

// Mark as neutral site/conference game
	function updateGameType($sbGame, $queryArray) {
		$queryArray[10] = $sbGame->competitions[0]->neutralSite;
		$queryArray[11] = $sbGame->competitions[0]->conferenceCompetition;

		return $queryArray;
	}

// Get and set the link for the game
	function updateLink($sbGame, $queryArray) {
		$queryArray[51] = $sbGame->links[0]->href;

		return $queryArray;
	}

// Get final lines from event
	function updateFinalLine($evtGame, $queryArray) {
		if(isset($evtGame->pickcenter[0]->spread)) {
			$queryArray[50] = abs($evtGame->pickcenter[0]->spread);
			if($queryArray[50] == 0) {
				$queryArray[48] = -1;
				$queryArray[49] = -1;
			} else {
				if($evtGame->pickcenter[0]->spread < 0) {
					$queryArray[48] = $queryArray[3];
					$queryArray[49] = $queryArray[4];
				} else {
					$queryArray[48] = $queryArray[4];
					$queryArray[49] = $queryArray[3];
				}
			}
		}
		
		return $queryArray;
	}

// Update team stats ($homeAway = 0 is home, $homeAway = 1 is away)
	function updateTeamStats($evtGame, $queryArray, $homeAway) {
		$teamId = $queryArray[3 + $homeAway];																// ID of Team we're getting stats for
		$offset = 17 * $homeAway;																			// Offset for away stats (if away stats)

		if($evtGame->boxscore->teams[0]->team->id == $teamId) {													// Which team are we looking in teams[]
			$index = 0;
		} else {
			$index = 1;
		}

		if(isset($evtGame->boxscore->teams[$index]->statistics[ 3])) {										// Make sure stats were recorded
			$queryArray[15 + $offset] = $evtGame->boxscore->teams[$index]->statistics[ 3]->displayValue;	// Set total yards
			$queryArray[16 + $offset] = $evtGame->boxscore->teams[$index]->statistics[ 8]->displayValue;	// Set rushing plays
			$queryArray[17 + $offset] = $evtGame->boxscore->teams[$index]->statistics[ 7]->displayValue;	// Set rushing yards
			$passing = explode('-',     $evtGame->boxscore->teams[$index]->statistics[ 5]->displayValue);	// Need to explode the passing stat because it's comp-attempts
			$queryArray[18 + $offset] = $passing[1];														// Set Passing Attempts
			$queryArray[19 + $offset] = $passing[0];														// Set Passing Completions
			$queryArray[20 + $offset] = $evtGame->boxscore->teams[$index]->statistics[ 4]->displayValue;	// Set Passing Yards
			$penalties = explode('-',   $evtGame->boxscore->teams[$index]->statistics[10]->displayValue);	// Need to explode penalties because its # penalties-yards
			$queryArray[21 + $offset] = $penalties[0];														// Set Penalties
			$queryArray[22 + $offset] = $penalties[1];														// Set Penalty Yards
			$queryArray[23 + $offset] = $evtGame->boxscore->teams[$index]->statistics[12]->displayValue;	// Set fumbles
			$queryArray[24 + $offset] = $evtGame->boxscore->teams[$index]->statistics[13]->displayValue;	// Set INTs
			$queryArray[25 + $offset] = $evtGame->boxscore->teams[$index]->statistics[ 0]->displayValue;	// Set first downs
			$thirdDowns = explode('-',  $evtGame->boxscore->teams[$index]->statistics[ 1]->displayValue);	// Explode third downs, conversions-attempts
			$queryArray[26 + $offset] = $thirdDowns[1];														// Set third down attempts
			$queryArray[27 + $offset] = $thirdDowns[0];														// Set third down conversions
			$fourthDowns = explode('-', $evtGame->boxscore->teams[$index]->statistics[ 2]->displayValue);	// Explode fourth downs, conversions-attempts
			$queryArray[28 + $offset] = $fourthDowns[1];													// Set fourth down attempts
			$queryArray[29 + $offset] = $fourthDowns[0];													// Set fourth down conversions
			$possession = explode(':',  $evtGame->boxscore->teams[$index]->statistics[14]->displayValue);	// Explode time of possession
			$queryArray[30 + $offset] = $possession[0] * 60 + $possession[1];								// Set time of possesion in seconds
		}

		return $queryArray;
	}

	// Get final game statistics
	function updateFinalGameStats($sbGame, $queryArray) {
		$queryArray[8] = 1;								// Game is complete
		$queryArray[9] = 0;								// Game was actually played

		// Get the game details, we've gotten all we can from the scoreboard
		$evtGameUrl = "http://site.api.espn.com/apis/site/v2/sports/football/college-football/summary?event=" . $sbGame->id;
		$evtGame    = json_decode(file_get_contents($evtGameUrl));

		$queryArray = updateFinalLine($evtGame, $queryArray);
		$queryArray = updateTeamStats($evtGame, $queryArray, 0);
		$queryArray = updateTeamStats($evtGame, $queryArray, 1);

		return $queryArray;
	}

	// Update game with latest data
	function updateGame($dbConn, $sbGame, $sqlGame) {
		// Set up update query
		$updateQuery = setUpdateQuery();
		$numArgs     = substr_count($updateQuery, '?');		// Lets make it more dyanamic so I don't have to write exisitng stuff when adding arguments

		// Lets go ahead and set this since we'll need to reference a lot
		$gameId = $sbGame->id;

		// Prep Array, fill with null, set Game ID
		$queryArray = array();
		for($x = 0; $x < $numArgs; $x++) {
			$queryArray[$x] = NULL;
		}
		$queryArray[$numArgs - 1] = $gameId;

		// Lets get the status of the game and decide what we're going to do
		$gameStatus = $sbGame->status->type->id;
		if($gameStatus > 20) {
			$gameStatus = 2;
		}
		$queryArray[7] = $gameStatus;
		/* Status Flags:
			1: Scheduled
			2: In progress
			3: Complete
			4: Forfeit
			5: Cancelled
			6: Postponed */
		switch($gameStatus) {
			case 1:			// Game has not yet been played
				$queryArray = updateDateNameNetwork($sbGame, $queryArray);
				$queryArray = updateHomeAway($sbGame, $queryArray);
				$queryArray = updateRanks($sbGame, $queryArray);
				$queryArray = updateSpreadPregame($dbConn, $sbGame, $queryArray);
				$queryArray = updateGameType($sbGame, $queryArray);
				$queryArray = updateLink($sbGame, $queryArray);
				break;
			case 2:			// Game is in progress
				$queryArray = updateDateNameNetwork($sbGame, $queryArray);
				$queryArray = updateHomeAway($sbGame, $queryArray);
				$queryArray = updateRanks($sbGame, $queryArray);
				$queryArray = copySpread($sqlGame, $queryArray);
				$queryArray = updateScore($sbGame, $queryArray);
				$queryArray = updateGameType($sbGame, $queryArray);
				$queryArray = updateLink($sbGame, $queryArray);
				break;
			case 3:			// Game final
				$queryArray = updateDateNameNetwork($sbGame, $queryArray);
				$queryArray = updateHomeAway($sbGame, $queryArray);
				$queryArray = updateRanks($sbGame, $queryArray);
				$queryArray = updateGameType($sbGame, $queryArray);
				$queryArray = updateScore($sbGame, $queryArray);
				$queryArray = determineWinner($sbGame, $queryArray);
				$queryArray = updateFinalGameStats($sbGame, $queryArray);
				$queryArray = updateLink($sbGame, $queryArray);

				break;
			case 4:			// Forfeit
				$queryArray = updateDateNameNetwork($sbGame, $queryArray);
				$queryArray = updateHomeAway($sbGame, $queryArray);
				$queryArray = updateRanks($sbGame, $queryArray);
				$queryArray = copySpread($sqlGame, $queryArray);
				$queryArray = setCancelled($sbGame, $queryArray);
				$queryArray = determineWinner($sbGame, $queryArray);
				$queryArray = updateGameType($sbGame, $queryArray);
				$queryArray = updateLink($sbGame, $queryArray);

				break;
			default:		// Cancelled or Postponed (we treat both of these as cancelled)
				$queryArray = updateDateNameNetwork($sbGame, $queryArray);
				$queryArray = updateHomeAway($sbGame, $queryArray);
				$queryArray = updateRanks($sbGame, $queryArray);
				$queryArray = copySpread($sqlGame, $queryArray);
				$queryArray = setCancelled($sbGame, $queryArray);
				$queryArray = updateGameType($sbGame, $queryArray);
				$queryArray = updateLink($sbGame, $queryArray);

				break;
		}

		sqlsrv_query($dbConn, $updateQuery, $queryArray);
	}

	// Create new game
	function newGame($dbConn, $gameId, $year, $week) {
		$newGameQuery = 'INSERT INTO games 
							(id, year, week)
							VALUES
							(?, ?, ?)';
		sqlsrv_query($dbConn, $newGameQuery, array($gameId, $year, $week));
	}
?>