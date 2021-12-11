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
			<th colspan = 3>
				Favorite
			</th>
			<th>
				W-L
			</th>
			<th colspan = 3>
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
			// Load teams, games, logos
			$teamArray = loadTeamArray($dbConn);
			$gameArray = loadGames($dbConn, 2021, 20);
			
			foreach($gameArray as $game) {
				if($game->favorite == -1 || $game->favorite == NULL) {
					$game->favorite = $game->homeId;
					$game->underdog = $game->awayId;
					$favoriteRank   = $game->homeRank;
					$underdogRank   = $game->awayRank;
				} else {
					if($game->favorite == $game->homeId) {
						$favoriteRank = $game->homeRank;
						$underdogRank = $game->awayRank;
					} else {
						$favoriteRank = $game->awayRank;
						$underdogRank = $game->homeRank;
					}
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
						<?= $game->network ?>
					</td>
					<td>
						<?= $favoriteRank ?>
					</td>
					<td>
						<img height=25px width=25px src="<?=fetchLogo($dbConn, $game->favorite)?>">
					</td>
					<td>
						<?= $teamArray[$game->favorite]->displayName ?>
					</td>
					<td>
						<?= $teamArray[$game->favorite]->wins . '-' . $teamArray[$game->favorite]->losses ?>
					</td>
					<td>
						<?= $underdogRank ?>
					</td>
					<td>
						<img height=25px width=25px src="<?=fetchLogo($dbConn, $game->underdog)?>">
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

