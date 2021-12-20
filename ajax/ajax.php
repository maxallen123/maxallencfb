<?php
	header('Content-Type: application/json; charset=utf-8');

	// Load functions, connect to DB
	require('../phpFunctions/functions.php');
	$dbConn = sqlConnect();

	function setPick($dbConn) {
		// Set up queries
		$checkPick      = 'SELECT gameId 
							FROM picks 
							WHERE gameId = ? AND userId = ?';
		$newPick        = 'INSERT INTO picks 
							(gameId, userId, pick, year, week, lastChange) 
							VALUES 
							(?, ?, ?, ?, ?, ?)';
		$updatePick     = 'UPDATE picks SET
							pick = ?, lastChange = ? 
							WHERE gameId = ? AND userId = ?';
		$deletePick     = 'DELETE FROM picks
							WHERE gameId = ? AND userId = ?';
		$checkStartTime = 'SELECT date 
							FROM games
							WHERE id = ?';
		
		// Get the current time to check for cheating
		$curTime = new DateTime('NOW');

		// Let's check and make sure the game hasn't already started
		$timeRsrc = sqlsrv_query($dbConn, $checkStartTime, array($_GET['gameId']));
		$time = sqlsrv_fetch_array($timeRsrc);
		if($curTime > $time['date']) {
			die();
		}


		// Set up arrays
		$checkArray  = array($_GET['gameId'], $_GET['userId']);
		$newArray    = array($_GET['gameId'], $_GET['userId'], $_GET['pick'], $_GET['year'], $_GET['week'], $curTime);
		$updateArray = array($_GET['pick'], $curTime, $_GET['gameId'], $_GET['userId']);
		$deleteArray = array($_GET['gameId'], $_GET['userId']);

		// Query if rows exist, then determine what to do next
		$picks = sqlsrv_query($dbConn, $checkPick, $checkArray);

		if(sqlsrv_has_rows($picks)) {		// If a pick already exists
			if($_GET['pick'] == -1) {		// If the blank row was picked, need to delete
				$cmd = sqlsrv_query($dbConn, $deletePick, $deleteArray);
			} else {						// Otherwise a team was picked, row exists - update
				$cmd = sqlsrv_query($dbConn, $updatePick, $updateArray);
			}
		} else {							// Pick doesn't exist then
			if($_GET['pick'] != -1) {		// Make sure the blank wasn't picked (if it was, we don't do anything)
				$cmd = sqlsrv_query($dbConn, $newPick, $newArray);
			}
		}
		
		// I think this will force the query to complete before exiting
		if(isset($cmd)) {
			sqlsrv_rows_affected($cmd);
		}
		echo json_encode(array("status"=>"done"));
	}

	function updatePicks($dbConn) {
		$teamArray = loadTeamArray($dbConn, 0, $_GET['year'], 0);
		echo json_encode(loadPicks($dbConn, $teamArray, $_GET['year'], $_GET['week']));
	}
	


	switch($_GET['function']) {
		case 'setPick':
			setPick($dbConn);
			break;
		case 'updatePicks':
			updatePicks($dbConn);
			break;
	}

?>