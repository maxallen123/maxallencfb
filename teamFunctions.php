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

		function addGame($game) {
			// Determine if we are home or away
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

			// Only run stats for actual played games
			if($game['isCancelled'] == 0) {
				array_push($this->opponents, $game[$them . 'Id']);					// Add opponent to list
				foreach($statsArray as $stat) {
					$statUs   = $us   . $stat;
					$statThem = $them . $stat;
					$this->offStats[$stat] += $game[$statUs];
					array_push($this->offStatsArrays[$stat], $game[$statUs]);
					$this->defStats[$stat] += $game[$statThem];
					array_push($this->defStatsArrays[$stat], $game[$statThem]);
				}
			}

		}
	}

	// Function to create array of team classes
	function loadTeamArray($dbConn) {
		// Prep Queries
		$loadQuery = 'SELECT 
						id, displayName, shortDisplayName, abbreviation, comedyName 
						FROM teams';
		$gamesQuery = 'SELECT 
						* 
						FROM games WHERE 
						completed = 1';
		
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