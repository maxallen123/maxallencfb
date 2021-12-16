<?php
    require('../functions.php');
    $dbConn = sqlConnect();

    $teamQuery = 'SELECT id, displayName FROM teams';

    // Load array of teams to verify games are D1 vs D1
    $teams = sqlsrv_query($dbConn, $teamQuery);
    while($team = sqlsrv_fetch_array($teams)) {
        $teamArray[$team['id']] = $team['displayName'];
    }

    // Make sure year is defined
    if(!isset($argv[1])) {
        die("No year argument\n");
    }
    if(!($argv[1] > 2000 && $argv[1] < 2030)) {
        die("Not a valid year\n");
    }

    $year = $argv[1];

    // Game loading loop - first regular season, then bowls
    for($seasonType = 2; $seasonType <= 3; $seasonType++) {
        // Load weeks 1-16 if regular season, otherwise just week 1 for bowls
        for($week = 1; $week <= 16 || ($week == 1 && $seasonType == 3); $week++) {
            if($seasonType == 3) {
                $week = 20;
            }
            updateWeek($dbConn, $year, $week);
        }
    }
?>