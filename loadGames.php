<?php
    require('functions.php');
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
            $limit = 199;
            $json = '';
            do {
                $limit++;       // Increment limit each time to force ESPN to try again
                $espnUrl = 'http://site.api.espn.com/apis/site/v2/sports/football/college-football/scoreboard?groups=90&dates=' . $year . '&seasontype='. $seasonType .'&week=' . $week . '&limit=' . $limit;
                $json = @file_get_contents($espnUrl);
            } while(strlen($json) < 1000);     // Check and make sure that data is valid, if we didn't pull at least 1k characters than ESPN errored out
            $games = json_decode($json);

            // Cycle through the games
            foreach($games->events as $game) {
                // Get Home ID and Away ID to make check cleaner
                $homeId = $game->competitions[0]->competitors[0]->id;
                $awayId = $game->competitions[0]->competitors[1]->id;
                // Check and make sure its D1 vs D1, if not then we don't count it
                if(isset($teamArray[$homeId]) && isset($teamArray[$awayId])) {
                    //echo "SeasonType: " . $seasonType . ", Week " . $week . ": " . $teamArray[$homeId] . " vs. ". $teamArray[$awayId] . "\n";
                    addGame($dbConn, $game, $year, $seasonType, $week);
                }
            }
        }
    }
?>