<html>
    <body bgcolor='#888888'>
<?php
    require('phpFunctions/functions.php');

    $dbConn = sqlConnect();

    $query = 'SELECT * FROM teams ORDER BY slug ASC';
    $teams = sqlsrv_query($dbConn, $query);

?>
<table>
    <tr>
        <th>id</th>
        <th>uid</th>
        <th>slug</th>
        <th>location</th>
        <th>name</th>
        <th>nickname</th>
        <th>abbreviation</th>
        <th>displayName</th>
        <th>shortDisplayName</th>
        <th>color/altColor</th>
        <th>color (hex)</th>
        <th>alternateColor (hex)</th>
    </tr>
<?php
    while($team = sqlsrv_fetch_array($teams)) {
        ?>
        <tr>
            <td><?=$team['id']?></td>
            <td><?=$team['uid']?></td>
            <td><?=$team['slug']?></td>
            <td><?=$team['location']?></td>
            <td><?=$team['name']?></td>
            <td><?=$team['nickname']?></td>
            <td><?=$team['abbreviation']?></td>
            <td><?=$team['displayName']?></td>
            <td><?=$team['shortDisplayName']?></td>
            <?php
                if($team['color'] != NULL) {
                    echo '<td bgcolor=\'#' . $team['color']. '\'><font color=\'#';
                    if($team['alternateColor'] != NULL) {
                        echo $team['alternateColor'];
                    } else {
                        $red   = hexdec(substr($team['color'], 0, 2));
                        $green = hexdec(substr($team['color'], 2, 2));
                        $blue  = hexdec(substr($team['color'], 4, 2));
                        if(($red + $green + $blue)/3 < 128) {
                            echo 'FFFFFF';
                        } else {
                            echo '000000';
                        }
                    }
                    echo '\'>Color</font></td>';
                }
            ?>
            <td><?=$team['color']?></td>
            <td><?=$team['alternateColor']?></td>
        </tr>
        <?php
        $query = "SELECT img, desc_2 FROM teamLogos WHERE teamId = ?";
        $queryArray = array($team['id']);
        $logos = sqlsrv_query($dbConn, $query, $queryArray);
        if(sqlsrv_has_rows($logos)) {
            ?>
            <tr>
                <td colspan="12">
                    <table>
                        <tr>
                            <?php
                                while($logo = sqlsrv_fetch_array($logos)) {
                            ?>
                            <td><?=$logo['desc_2']?></td>
                            <td><img height=100px width=100px src="<?=$logo['img']?>"></td>
                            <?php
                                }
                            ?>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php
        }

        $query = 'SELECT href, text FROM teamLinks WHERE teamId = ?';
        $links = sqlsrv_query($dbConn, $query, $queryArray);
        if(sqlsrv_has_rows($links)) {
            ?>
            <tr>
                <td colspan="12">
                    <table>
                        <tr>
                            <?php
                                while($link = sqlsrv_fetch_array($links)) {
                            ?>
                            <td><a href="<?=$link['href']?>"><?=$link['text']?></a></td>
                            <?php
                                }
                            ?>
                        </tr>
                    </table>
                </td>
            </tr>
            <?php
        }
    }
?>