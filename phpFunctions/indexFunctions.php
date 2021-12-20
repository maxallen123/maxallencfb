<?php
	// Get logo for team
	function fetchLogo($dbConn, $teamId) {
		// Query
		$logoQuery = "SELECT 
						href
						FROM teamLogos WHERE
						desc_2 = 'dark' AND teamId = ?";
		
		$logo = sqlsrv_query($dbConn, $logoQuery, array($teamId));
		
		return sqlsrv_fetch_array($logo)['href'];
	}

	// Return spread with either decimal places if it's a number or return whatever it was instead
	function formatSpread($spread) {
		if(is_numeric($spread)) {
			return number_format($spread, 1);
		} else {
			return $spread;
		}
	}

	// Return current week info
	function currentWeek($dbConn) {
		$weekQuery = 'SELECT TOP (1) 
						year, week, weekName
						FROM weeks
						WHERE startDate < GETDATE()
						ORDER BY startDate DESC';
		return sqlsrv_fetch_array(sqlsrv_query($dbConn, $weekQuery));
	}

	// Get the link for the team page on ESPN
	function fetchClubhouse($dbConn, $teamId) {
		// Query
		$linkQuery = "SELECT
						href
						FROM teamLinks WHERE
						text = 'Clubhouse' AND teamId = ?";
		$link = sqlsrv_query($dbConn, $linkQuery, array($teamId));

		return sqlsrv_fetch_array($link)['href'];
	}

?>