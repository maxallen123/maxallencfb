<?php
    require('functions.php');
    $dbConn = sqlConnect();
    header('Content-type: Application/JSON');

    $teamArray = loadTeamArray($dbConn);
    //echo json_encode($teamArray);
    foreach($teamArray as $team) {
    }
?>