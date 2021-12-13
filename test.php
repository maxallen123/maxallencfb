<?php
	require('functions.php');
	$dbConn = sqlConnect();
	updateWeek($dbConn, 2021, 8);
?>