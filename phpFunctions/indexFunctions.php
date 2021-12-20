<?php

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

?>