<?php

	// Class for teams
	class team {
		function __construct($id, $displayName, $shortDisplayName, $abbreviation, $comedyName) {
			$this->id                 = $id;
			$this->displayName        = $displayName;
			$this->shortDisplayName   = $shortDisplayName;
			$this->abbreviation       = $abbreviation;
			$this->wins               = 0;
			$this->losses             = 0;
			$this->opponents          = array();
			$this->gameIds            = array();
			$this->offStats           = array();
			$this->offStatsArrays     = array();
			$this->defStats           = array();
			$this->defStatsArrays     = array();

			if($comedyName != NULL) {
				$this->displayName = $comedyName;
			}

			$statsArray = statsArray();
			foreach($statsArray as $stat) {
				$this->offStats[$stat]       = 0;
				$this->offStatsArrays[$stat] = array();
				$this->defStats[$stat]       = 0;
				$this->defStatsArrays[$stat] = array();
			}
		}

		function addGame($game, $stats) {
			// Determine if we are home or away (or all teams)
			if($game['homeId'] == $this->id) {
				$us = 'home';
				$them = 'away';
			} else {
				$us = 'away';
				$them = 'home';
			}

			// Did we win? Checking all games now so make sure that we won and wasn't cancelled, but include forfeits
			if($game['winnerId'] == $this->id) {
				$this->wins++;
			} else if($game['winnerId'] != NULL) {
				$this->losses++;
			}

			$statsArray = statsArray();

			$max = 0;
			if($this->id == -1) {
				$max = 1;
			}

			if($stats == 1) {		// Only do this if we are collecting stats
				for($x = 0; $x <= $max; $x++) {
					// If $x == 1 then we are -1 and we need to cycle through again, but opposite
					if($x == 1) {
						$us   = 'home';
						$them = 'away';
					}
					if($game['isCancelled'] == 0 && $game['completed'] == 1 && $game[$us . 'Yards'] != NULL) {	// Only run stats for actual played games
						array_push($this->opponents, $game[$them . 'Id']);										// Add opponent to list
						foreach($statsArray as $stat) {
							$statUs   = $us   . $stat;
							$statThem = $them . $stat;
							$this->offStats[$stat] += $game[$statUs];
							array_push($this->offStatsArrays[$stat], $game[$statUs]);
							$this->defStats[$stat] += $game[$statThem];
							array_push($this->defStatsArrays[$stat], $game[$statThem]);
							array_push($this->gameIds, $game['id']);
						}
					}
				}
			}
		}
	}

	/* Function to create array of team classes
	 * Type:
	 *  0 - All games for a given year
	 *  1 - Last $period number of games
	 * Period:
	 *  If Type = 0: Year for games to load
	 *  If Type = 1: Last X games for this team
	 * Stats:
	 *  Whether to calculate stats for teams
	 */
	function loadTeamArray($dbConn, $type, $period, $stats) {
		// Prep Queries
		$loadQuery = 'SELECT 
						id, displayName, shortDisplayName, abbreviation, comedyName 
						FROM teams';
		switch($type) {
			case 0:
				$gamesQuery = 'SELECT 
								* 
								FROM games WHERE 
								completed = 1 AND year = ? ORDER BY date DESC';
				$gamesArray = array($period);
				break;
		}
		
		// Get teams from SQL
		$teamRsrc = sqlsrv_query($dbConn, $loadQuery);

		// Proceed through the list
		$teams = array();
		while($team = sqlsrv_fetch_array($teamRsrc)) {
			$teams[$team['id']] = new team($team['id'], $team['displayName'], $team['shortDisplayName'], $team['abbreviation'], $team['comedyName']);
		}
		// Add the all teams if we're collecting stats
		if($stats == 1) {
			$teams[-1] = new team(-1, 'All Teams', 'All Teams', 'ALLT', NULL);
		}

		// Get games from SQL
		$gameRsrc = sqlsrv_query($dbConn, $gamesQuery, $gamesArray);
		
		// Proceed through the list
		while($game = sqlsrv_fetch_array($gameRsrc)) {
			$teams[$game['homeId']]->addGame($game, $stats);
			$teams[$game['awayId']]->addGame($game, $stats);
			if($stats == 1) {										// No reason to do this if we're not collecting stats
				$teams[-1]->addGame($game, $stats);
			}
		}

		return $teams;
	}

	// Return array of stats
	function statsArray() {
		return array(
			"Score",
			"Yards",
			"RushingPlays",
			"RushingYards",
			"PassingAttempts",
			"PassingComp",
			"PassingYards",
			"Penalties",
			"PenaltyYards",
			"TurnoversFumble",
			"TurnoversInt",
			"FirstDowns",
			"ThirdDownAttempts",
			"ThirdDownConversions",
			"FourthDownAttempts",
			"FourthDownConversions",
			"PossessionTime"
		);
	}
?>