<?php
	require('functions.php');
	$dbConn = sqlConnect();
?>

<table>
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
			<th>
			</th>
			<th>
				Favorite
			</th>
			<th>
				W-L
			</th>
			<th>
			</th>
			<th>
				Underdog
			</th>
			<th>
				W-L
			</th>
			<th>
				Spread
			</th>
			<th>
				winner
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
			// Load teams
			$teamArray = loadTeamArray($dbConn);
			$gamesArray = loadGames($dbConn, 2021, 20);
			foreach($gameArray as $game) {
				if($game->favorite == -1 || $game->favorite == NULL) {
					$game->favorite = $game->homeId;
					$game->underdog = $game->awayId;
				}
				if($game->spread == 0) {
					$game->spread = 'PICK';
				}
				if($game->spread == NULL) {
					$game->spread = '';
				}
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
						<!-- Need to add broadcaster -->
					</td>
					<td>
						<!-- Need to add favorite rank -->
					</td>
					<td>
						<?= $teamArray[$game->favorite]->displayName ?>
					</td>
					<td>
						<?= $teamArray[$game->favorite]->wins . '-' . $teamArray[$game->favorite]->losses ?>
					</td>
					<td>
						<!-- Need to add underdog rank -->
					</td>
					<td>
						<?= $teamArray[$game->underdog]->displayName ?>
					</td>
					<td>
						<?= $teamArray[$game->underdog]->wins . '-' . $teamArray[$game->underdog]->losses ?>
					</td>
					<td>
						<?= '-' . $game->spread ?>
					</td>
				</tr>
				<?php
			}
		?>
	</tbody>
</table>

