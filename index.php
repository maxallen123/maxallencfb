<?php
	require('functions.php');
	$dbConn = sqlConnect();
	$week = 20;
	$year = 2021
?>
<html>
	<head>
		<link rel="stylesheet" href="./css/bootstrap.min.css">
	</head>
	<body>
		<input type="hidden" id="week" value="<?= $week ?>">
		<input type="hidden" id="year" value="<?= $year ?>">
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
					// Load teams, games, picks
					$teamArray = loadTeamArray($dbConn);
					$gameArray = loadGames($dbConn, $year, $week);
					$picksArray = loadPicks($dbConn, $year, $week);

					foreach($gameArray as $game) {
						?>
						<tr id="game-<?= $game->id ?>">
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
								<img height="25" width="25" src="<?=fetchLogo($dbConn, $game->tableFav)?>">
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
								<img height="25" width="25" src="<?=fetchLogo($dbConn, $game->tableDog)?>">
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
										<select class='pick' id='pick-" . $userId . "-" . $game->id . "' autocomplete='off' onChange='setPick(" . $game->id . ", " . $userId . ")'>
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
									<td id='score-" . $userId . "-" . $game->id . "'>
									</td>";
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