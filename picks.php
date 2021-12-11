<?php
	require('functions.php');
	$dbConn = sqlConnect();
?>
<html>
	<head>
		<link rel="stylesheet" href="./css/bootstrap.min.css">
	</head>
	<body>
		<table class="table">
			<thead>
				<tr>
					<th>
						Date
					</th>
					<th>
						Name
					</th>
					<th>
						Time
					</th>
					<th>
						TV
					</th>
					<th colspan = 3>
						Favorite
					</th>
					<th>
					</th>
					<th colspan = 3>
						Underdog
					</th>
					<th>
					</th>
					<th>
						Spread
					</th>
					<th colspan = 2>
						Bubba
					</th>
					<th colspan = 2>
						Maxallen
					</th>
					<th colspan = 2>
						Pnut
					</th>
					<th colspan = 2>
						Akaishi
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
					// Load teams, games, logos
					$teamArray = loadTeamArray($dbConn);
					$gameArray = loadGames($dbConn, 2021, 15);
					
					foreach($gameArray as $game) {
						?>
						<tr>
							<td>
								<?= $game->day ?>
							</td>
							<td>
								<?= $game->name ?>
							</td>
							<td>
								<?= $game->time ?>
							</td>
							<td>
								<?= $game->network ?>
							</td>
							<td>
								<?= $game->rankFav ?>
							</td>
							<td>
								<img height=25px width=25px src="<?=fetchLogo($dbConn, $game->tableFav)?>">
							</td>
							<td>
								<?= $teamArray[$game->tableFav]->displayName ?>
							</td>
							<td>
								<?php
									if($game->winnerId != NULL) {
										echo $game->scoreFav;
									} else {
										echo $teamArray[$game->tableFav]->wins . '-' . $teamArray[$game->tableFav]->losses;
									}
								?>
							</td>
							<td>
								<?= $game->rankDog ?>
							</td>
							<td>
								<img height=25px width=25px src="<?=fetchLogo($dbConn, $game->tableDog)?>">
							</td>
							<td>
								<?= $teamArray[$game->tableDog]->displayName ?>
							</td>
							<td>
								<?php 
									if($game->winnerId != NULL) {
										echo $game->scoreDog;
									} else {
										echo $teamArray[$game->tableDog]->wins . '-' . $teamArray[$game->tableDog]->losses;
									}
								?>
							</td>
							<td>
								<?= $game->spread ?>
							</td>
						</tr>
						<?php
					}
				?>
			</tbody>
		</table>
	</body>
</html>