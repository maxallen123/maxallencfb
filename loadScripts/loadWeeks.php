<?php 
	require('../phpFunctions/functions.php');
	$dbConn = sqlConnect();
	
	// Set up queries
	$chkQuery = 'SELECT *
					FROM weeks
					WHERE year = ? AND week = ?';
	$addQuery = 'INSERT INTO weeks
					(year, week, startDate, endDate, weekName)
					VALUES
					(?, ?, ?, ?, ?)';
	
	for($year = 2001; $year <= 2021; $year++) {								// Go through each year, adding weeks
		$scoreboard = pullScoreboard($year, 20);							// Get the ESPN scoreboard - it has all the weeks for the year
		foreach($scoreboard->leagues[0]->calendar as $calendar) {			// Go through the seasonTypes
			if($calendar->value == 2 || $calendar->value == 3) {			// Make sure it's regular or postseason, we don't care about other ones
				foreach($calendar->entries as $entry) {						// Go through each week
					if(!($entry->value != 1 && $calendar->value == 3)) {	// If postseason, make sure its bowls
						if($calendar->value == 3) {
							$week = 20;										// If bowls, set week = 20
						} else {
							$week = $entry->value;							// Otherwise week = the week in ESPN
						}
						$chkArray = array($year, $week);					// Check and make sure we don't already have this
						if(!sqlsrv_has_rows(sqlsrv_query($dbConn, $chkQuery, $chkArray))) {
							$addArray = array($year, 						// Create array to add week to database
											  $week, 
											  date('Y-m-d H:i:s', strtotime($entry->startDate)),
											  date('Y-m-d H:i:s', strtotime($entry->endDate)),
											  $entry->label);
							sqlsrv_query($dbConn, $addQuery, $addArray);	// Add week to databse
							echo "Added year " . $year . " week " . $week . " to database\n";
						}
					}
				}
			}
		}
	}
?>