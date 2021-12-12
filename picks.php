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
					$gameArray = loadGames($dbConn, 2021, 14);
					
					foreach($gameArray as $game) {
						?>
						<tr>
							<td id="day-<?= $game->id ?>">
								<?= $game->day ?>
							</td>
							<td id="name-<?= $game->id ?>">
								<?= $game->name ?>
							</td>
							<td id="time-<?= $game->id ?>">
								<?= $game->time ?>
							</td>
							<td id="network-<?= $game->id ?>">
								<?= $game->network ?>
							</td>
							<td id="rankFav-<?= $game->id ?>">
								<?= $game->rankFav ?>
							</td>
							<td id="logoFav-<?= $game->id ?>">
								<img height=25px width=25px src="<?=fetchLogo($dbConn, $game->tableFav)?>">
							</td>
							<td id="nameFav-<?= $game->id ?>">
								<?= $teamArray[$game->tableFav]->displayName ?>
							</td>
							<td id="WLorPointsFav-<?= $game->id ?>">
								<?php
									if($game->winnerId != NULL) {
										echo $game->scoreFav;
									} else {
										echo $teamArray[$game->tableFav]->wins . '-' . $teamArray[$game->tableFav]->losses;
									}
								?>
							</td>
							<td id="rankDog-<?= $game->id ?>">
								<?= $game->rankDog ?>
							</td>
							<td id="logoDog-<?= $game->id ?>">
								<img height=25px width=25px src="<?=fetchLogo($dbConn, $game->tableDog)?>">
							</td>
							<td id="nameDog-<?= $game->id ?>">
								<?= $teamArray[$game->tableDog]->displayName ?>
							</td>
							<td id="WLorPointsDog-<?= $game->id ?>">
								<?php 
									if($game->winnerId != NULL) {
										echo $game->scoreDog;
									} else {
										echo $teamArray[$game->tableDog]->wins . '-' . $teamArray[$game->tableDog]->losses;
									}
								?>
							</td>
							<td id="spread-<?= $game->id ?>">
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