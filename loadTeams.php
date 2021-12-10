<?php

	// Load SQL function, connect to SQL
	require('functions.php');
	$dbConn = sqlConnect();

	// Download teams JSON from ESPN
	$espnUrl = 'http://site.api.espn.com/apis/site/v2/sports/football/college-football/teams?groups=90&limit=1000';
	$teams = json_decode(file_get_contents($espnUrl))->sports[0]->leagues[0]->teams;

	foreach($teams as $team) {
		$team = $team->team;
		processTeam($dbConn, $team);
	}
?>