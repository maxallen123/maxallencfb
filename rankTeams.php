<?php
    require('functions.php');
    $dbConn = sqlConnect();
    //header('Content-type: Application/JSON');

    $teamArray = loadTeamArray($dbConn);
    //echo json_encode($teamArray);
    echo "<table><tr><th>Name</th><th>W</th><th>L</th><th>Games</th>";
    foreach($teamArray[2]->offStats as $name => $stat) {
        echo "<th>" . $name . "</th>";
    }
    foreach($teamArray[2]->offStats as $name => $stat) {
        echo "<th>" . $name . "</th>";
    }
    echo "</tr>";
    foreach($teamArray as $team) {
        echo "<tr><td>" . $team->displayName . "</td><td>" . $team->wins . "</td><td>" . $team->losses . "</td><td>" . count($team->opponents) . "</td>";
        foreach($team->offStats as $stat) {
            echo "<td>" . $stat . "</td>";
        }
        foreach($team->defStats as $stat) {
            echo "<td>" . $stat . "</td>";
        }
        echo "</tr>";
    }
?>