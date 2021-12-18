<?php
	require('functions.php');
	$dbConn = sqlConnect();
	$week = 20;
	$year = 2021
?>
<html>
	<head>
		<link rel="stylesheet" href="./css/bootstrap.min.css">
		<link rel="stylesheet" href="./css/style.css">
	</head>
	<body>
		<input type="hidden" id="week" value="<?= $week ?>">
		<input type="hidden" id="year" value="<?= $year ?>">
		<table class="table table-striped table-dark">
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
					<th colspan = 4>
						Favorite
					</th>
					<th colspan = 4>
						Underdog
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
					// Load teams, games, picks
					$teamArray = loadTeamArray($dbConn);
					$gameArray = loadGames($dbConn, $year, $week);
					$picksArray = loadPicks($dbConn, $year, $week);

					foreach($gameArray as $game) {
						?>
						<tr id="game-<?= $game->id ?>">
							<input type="hidden" id="next-<?= $game->id ?>" value="<?= $game->next ?>">
							<td id="day-<?= $game->id ?>">
								<?= $game->day ?>
							</td>
							<td id="name-<?= $game->id ?>">
								<?= $game->name ?>
							</td>
							<td id="time-<?= $game->id ?>" class="time">
								<?= $game->time ?>
							</td>
							<td id="network-<?= $game->id ?>">
								<?= $game->network ?>
							</td>
							<td id="rankFav-<?= $game->id ?>" class="rank">
								<?= $game->rankFav ?>
							</td>
							<td id="logoFav-<?= $game->id ?>">
								<img height="25" width="25" src="<?=fetchLogo($dbConn, $game->tableFav)?>">
							</td>
							<td id="nameFav-<?= $game->id ?>"><?= $teamArray[$game->tableFav]->displayName ?></td>
							<td id="WLorPointsFav-<?= $game->id ?>"></td>
							<td id="rankDog-<?= $game->id ?>" class="rank">
								<?= $game->rankDog ?>
							</td>
							<td id="logoDog-<?= $game->id ?>">
								<img height="25" width="25" src="<?=fetchLogo($dbConn, $game->tableDog)?>">
							</td>
							<td id="nameDog-<?= $game->id ?>"><?= $teamArray[$game->tableDog]->displayName ?></td>
							<td id="WLorPointsDog-<?= $game->id ?>">
							</td>
							<td class="spread" id="spread-<?= $game->id ?>">
								<?= formatSpread($game->spread) ?>
							</td>
							<?php
								// Picks Loop
								for($userId = 0; $userId <= 3; $userId++) {
									if(isset($picksArray[$game->id][$userId])) {
										$pick = $picksArray[$game->id][$userId];
									} else {
										$pick = -1;
									}
									echo 
									"<td id='tdpick-" . $userId . "-" . $game->id . "'>
										<select class='pick form-select' id='pick-" . $userId . "-" . $game->id . "' autocomplete='off' onChange='setPick(" . $game->id . ", " . $userId . ")'>
											<option value='-1'";
									if($pick == -1) {
										echo " selected";
									}
									echo
												"></option>
											<option value='" . $game->tableFav . "'";
									if($pick == $game->tableFav) {
										echo " selected";
									}
									echo
												">" . $teamArray[$game->tableFav]->displayName . "</option>
											<option value='" . $game->tableDog . "'";
									if($pick == $game->tableDog) {
										echo " selected";
									}
									echo
												">" . $teamArray[$game->tableDog]->displayName . "</option>
										</select>
									</td>
									<td id='scoretd-" . $userId . "-" . $game->id . "'><span id='score-" . $userId . "-" . $game->id . "' class='hidden'></span></td>";
								}
							?>
						</tr>
						<?php
					}
				?>
			</tbody>
		</table>
		<script src="./js/jquery-3.6.0.min.js"></script>
		<script src="./js/functions.js"></script>
		<script language="javascript" type="text/javascript">
			// Update picks every 5 seconds
			setInterval(() => updatePicks(), 5000);
			$(document).ready(function() {
				updatePicks();
			})
		</script>
	</body>
</html>