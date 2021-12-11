<?php
    require('functions.php');
    $dbConn = sqlConnect();
    header('Content-type: Application/JSON');

    $teamArray = loadTeamArray($dbConn);
    //echo json_encode($teamArray);
    foreach($teamArray as $team) {
        if($team->wins/($team->wins + $team->losses) < .5 && ($team->pointsFor > $team->pointsAgainst)) {
            echo $team->displayName . '; W-L: ' . $team->wins . '-' . $team->losses . '; PF-PA: ' . $team->pointsFor . '-' . $team->pointsAgainst . '; Margin: ' . $team->pointsFor - $team->pointsAgainst . "\n";
        }
    }
?>