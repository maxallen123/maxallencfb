<?php
    require('phpFunctions/functions.php');
    $dbConn = sqlConnect();
    header('Content-type: Application/JSON');

    $teamArray = loadTeamArray($dbConn);
    echo json_encode($teamArray);
    /* echo "<table><tr><th>Name</th><th>W</th><th>L</th><th>Games</th>";
    foreach($teamArray[2]->offStats as $name => $stat) {
        echo "<th>" . $name . "</th>";
        echo "<th>Mean</th>";
        echo "<th>Median</th>";
        echo "<th>Percentile</th>";
        echo "<th>Std Dev</th>";
    }
    foreach($teamArray[2]->offStats as $name => $stat) {
        echo "<th>" . $name . "</th>";
        echo "<th>Mean</th>";
        echo "<th>Median</th>";
        echo "<th>Percentile</th>";
        echo "<th>Std Dev</th>";
    }
    echo "</tr>";
    foreach($teamArray as $team) {
        echo "<tr><td>" . $team->displayName . "</td><td>" . $team->wins . "</td><td>" . $team->losses . "</td><td>" . count($team->opponents) . "</td>";
        foreach($team->offStats as $name => $stat) {
            echo "<td>" . $stat . "</td>";
            echo "<td>" . mean($team->offStatsArrays[$name]) . "</td>";
            echo "<td>" . median($team->offStatsArrays[$name]) . "</td>";
            echo "<td>" . getPercentile(mean($team->offStatsArrays[$name]), $teamArray[-1]->offStatsArrays[$name], 0) . "</td>";
            echo "<td>" . stdDev($team->offStatsArrays[$name]) . "</td>";
        }
        foreach($team->defStats as $name => $stat) {
            echo "<td>" . $stat . "</td>";
            echo "<td>" . mean($team->defStatsArrays[$name]) . "</td>";
            echo "<td>" . median($team->defStatsArrays[$name]) . "</td>";
            echo "<td>" . getPercentile(mean($team->defStatsArrays[$name]), $teamArray[-1]->defStatsArrays[$name], 1) . "</td>";
            echo "<td>" . stdDev($team->defStatsArrays[$name]) . "</td>";
        }
        echo "</tr>";
    } */
?>