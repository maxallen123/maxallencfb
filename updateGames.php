<?php
	require('phpFunctions/functions.php');
	$dbConn = sqlConnect();
	updateWeek($dbConn, 2021, 20);
?>