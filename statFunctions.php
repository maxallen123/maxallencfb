<?php

	// Return the mean of an array
	function mean($array) {
		$total = 0;
		$elements = 0;
		foreach($array as $element) {
			$total += $element;
			$elements++;
		}
		return $total/$elements;
	}

	// Return the median of an array
	function median($array) {
		sort($array);
		$elements = count($array);
		if($elements % 2 == 0) {
			return(($array[($elements / 2) - 1] + $array[$elements/2])/ 2);
		} else {
			return($array[($elements / 2) - 0.5]);
		}
	}

	function getPercentile($value, $array, $reverse) {
		sort($array);
		$count = count($array);
		while($value <= array_pop($array) && count($array) != 0);
		$endCount = count($array) + 1;
		if($reverse == 0) {
			return ($endCount/$count);
		} else {
			return (1 - $endCount/$count);
		}
	}
	
	function findPercentile($percentile, $array) {
		sort($array);
		$count = count($array);
		$indexA = floor($count * $percentile);
		$indexB = $indexA + 1;
		return ($array[$indexA] + $array[$indexB]) / 2;
	}

	function stdDev($array) {
		$mean = mean($array);
		$total = 0;
		foreach($array as $element) {
			$total += ($element - $mean) ** 2;
		}
		return sqrt($total/count($array));
	}

	function predictor($dbConn, $teamA, $teamB) {
		$teamArray = loadTeamArray($dbConn);
		
		$teamId[0] = $teamA;
		$teamId[1] = $teamB;
		$teamId[2] = -1;

		for($team = 0; $team < 3; $team++) {
			$teamRushPct[$team] = ($teamArray[$teamId[$team]]->offStats['RushingPlays']) / ($teamArray[$teamId[$team]]->offStats['RushingPlays'] + $teamArray[$teamId[$team]]->offStats['PassingAttempts']);
			$teamPassPct[$team] = 1 - $teamRushPct[$team];
			$teamYPR[$team] = array();
			$teamYPP[$team] = array();
			$teamYPRDef[$team] = array();
			$teamYPPDef[$team] = array();
			$games = count($teamArray[$teamId[$team]]->opponents);
			for($game = 0; $game < $games; $game++) {
				if($teamArray[$teamId[$team]]->offStatsArrays['RushingPlays'][$game] != 0) {
					array_push($teamYPR[$team]   , $teamArray[$teamId[$team]]->offStatsArrays['RushingYards'][$game] / $teamArray[$teamId[$team]]->offStatsArrays['RushingPlays'][$game]);
				}
				if($teamArray[$teamId[$team]]->offStatsArrays['PassingAttempts'][$game] != 0)  {
					array_push($teamYPP[$team]   , $teamArray[$teamId[$team]]->offStatsArrays['PassingYards'][$game] / $teamArray[$teamId[$team]]->offStatsArrays['PassingAttempts'][$game]);
				}
				if($teamArray[$teamId[$team]]->defStatsArrays['RushingPlays'][$game] != 0) {
					array_push($teamYPRDef[$team], $teamArray[$teamId[$team]]->defStatsArrays['RushingYards'][$game] / $teamArray[$teamId[$team]]->defStatsArrays['RushingPlays'][$game]);
				}
				if($teamArray[$teamId[$team]]->defStatsArrays['PassingAttempts'][$game] != 0) {
					array_push($teamYPPDef[$team], $teamArray[$teamId[$team]]->defStatsArrays['PassingYards'][$game] / $teamArray[$teamId[$team]]->defStatsArrays['PassingAttempts'][$game]);
				}
			}
			$teamYPRMean[$team] = mean($teamYPR[$team]);
			$teamYPPMean[$team] = mean($teamYPP[$team]);
			$teamYPRDefMean[$team] = mean($teamYPRDef[$team]);
			$teamYPPDefMean[$team] = mean($teamYPPDef[$team]);
		}
		for($team = 0; $team < 2; $team++) {
			if($team == 0) {
				$oppt = 1;
			} else {
				$oppt = 0;
			}

			$teamYPRPct[$team] = getPercentile($teamYPRMean[$team], $teamYPR[2], 0);
			$teamYPPPct[$team] = getPercentile($teamYPPMean[$team], $teamYPP[2], 0);
			$teamYPRDefPct[$team] = getPercentile($teamYPRDefMean[$team], $teamYPRDef[2], 1);
			$teamYPPDefPct[$team] = getPercentile($teamYPPDefMean[$team], $teamYPPDef[2], 1);
		}

		$predictionPercentile[0] = (0.5 + (((($teamYPRPct[0] - 0.5) - ($teamYPRDefPct[1] - 0.5)) * $teamRushPct[0]) + ((($teamYPPDefPct[0] - 0.5) - ($teamYPPDefPct[1] - 0.5)) * $teamPassPct[0])) / 2);
		$predictionPercentile[1] = (0.5 + (((($teamYPRPct[1] - 0.5) - ($teamYPRDefPct[0] - 0.5)) * $teamRushPct[1]) + ((($teamYPPDefPct[1] - 0.5) - ($teamYPPDefPct[0] - 0.5)) * $teamPassPct[1])) / 2);
		
		echo $teamArray[$teamId[0]]->displayName . "," . $predictionPercentile[0] . "," . findPercentile($predictionPercentile[0], $teamArray[-1]->offStatsArrays['Score']) . "," . $teamArray[$teamId[1]]->displayName . "," . $predictionPercentile[1] . "," . findPercentile($predictionPercentile[1], $teamArray[-1]->offStatsArrays['Score']) . "\n";
	}
?>