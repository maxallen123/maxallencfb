function setPick(gameId, userId) {
	// Prep variables
	selectId = '#pick-' + userId + '-' + gameId;
	selectVal = $(selectId).find(":selected").val();
	week = $('#week').val();
	year = $('#year').val();
	
	// Call AJAX update
	$.ajax({
		method: "GET",
		url: "./ajax/ajax.php",
		data: {
			function: 'setPick',
			week: week,
			year: year,
			gameId: gameId,
			userId: userId,
			pick: selectVal
		},
		datatype: 'json',
		success: 
			function (string) {
				updatePicks();
			}
	});
}

function updatePicks() {
	week = $('#week').val();
	year = $('#year').val();

	$.ajax({
		method: "GET",
		url: "./ajax/ajax.php",
		data: {
			function: 'updatePicks',
			week: week,
			year: year
		},
		datatype: 'json',
		success:
			function(picks) {
				console.log(picks) // Debug info
				// Go through each game and see what is in the DB
				$.each(picks, function(gameId, pick) {
					addClassesStatus(gameId, pick['game']['status']);
					WLorPointsUpdate(gameId, pick['game']);
					winnerUpdate(gameId, pick['game']);
					// Go through each user
					for(let userId = 0; userId <= 3; userId++) {
						// Get the ID of the select
						selectId = '#pick-' + userId + '-' + gameId;
						// Set the select
						$(selectId).val(pick[userId]);
						if((pick['game']['status'] > 1)) {
							$(selectId).prop('disabled', true);
						}
					}
					if(pick['game']['last'] == 0) {
						updateScores(gameId, pick['game'], picks);
					}
				});
			}
	});
}

function addClassesStatus(gameId, status) {
	switch(status) {
		case 1:
			addClass    = 'pregame';
			removeClass = 'in-progress completed';
			break;
		case 2:
			addClass    = 'in-progress';
			removeClass = 'pregame completed';
			break;
		default:
			addClass    = 'completed';
			removeClass = 'pregame in-progress'; 
	}
	trId = '#game-' + gameId;
	if(!$(trId).hasClass(addClass)) {
		$(trId).removeClass(removeClass).addClass(addClass);
	}
}

function WLorPointsUpdate(gameId, game) {
	// Set cell IDs
	favCellId = '#WLorPointsFav-' + game['id'];
	dogCellId = '#WLorPointsDog-' + game['id'];

	// Depending on game status
	if(game['status'] == 1 && ($(favCellId).html() != (game['fav']['wins'] + '-' + game['fav']['losses']))) {		// If game hasn't been played yet, show W-L record
		$(favCellId).html(game['fav']['wins'] + '-' + game['fav']['losses']);
		$(dogCellId).html(game['dog']['wins'] + '-' + game['dog']['losses']);
	}

	if(game['status'] > 3 && $(favCellId).html() != '') {		// If game wasn't played, show nothing
		$(favCellId).html('');
		$(dogCellId).html('');
	}

	if(game['status'] == 2 || game['status'] == 3)  {	// Game in progress or game complete - show score
		// Probably unnecessary but if score is null then set to 0-0
		if(game['scoreFav'] == null) {
			game['scoreFav'] = 0;
		}
		if(game['scoreDog'] == null) {
			game['scoreDog'] = 0;
		} 
		$(favCellId).html(game['scoreFav']);
		$(dogCellId).html(game['scoreDog']);
	}
}

function winnerUpdate(gameId, game) {
	if(game['winnerId'] != null) {
		favNameCell = '#nameFav-' + gameId;
		dogNameCell = '#nameDog-' + gameId;
		if(game['fav']['displayName'] == $(favNameCell).html()) {
			$('#rankFav-' + gameId).addClass('winner');
			$('#logoFav-' + gameId).addClass('winner');
			$('#nameFav-' + gameId).addClass('winner');
			$('#WLorPointsFav-' + gameId).addClass('winner');
			$('#rankDog-' + gameId).addClass('loser');
			$('#logoDog-' + gameId).addClass('loser');
			$('#nameDog-' + gameId).addClass('loser');
			$('#WLorPointsDog-' + gameId).addClass('loser');
		} else {
			$('#rankFav-' + gameId).addClass('loser');
			$('#logoFav-' + gameId).addClass('loser');
			$('#nameFav-' + gameId).addClass('loser');
			$('#WLorPointsFav-' + gameId).addClass('loser');
			$('#rankDog-' + gameId).addClass('winner');
			$('#logoDog-' + gameId).addClass('winner');
			$('#nameDog-' + gameId).addClass('winner');
			$('#WLorPointsDog-' + gameId).addClass('winner');
		}
		for(let userId = 0; userId <= 3; userId++) {
			$('#score-' + userId + '-' + gameId).removeClass('hidden');
		}
	}
}

function updateScores(gameId, game, picks) {
	// Set preliminary variables
	curGame = game;
	curId = gameId;
	userScore = new Array();
	for(let userId = 0; userId <= 3; userId++) {
		userScore[userId] = 0;
	}

	// Loop through each one
	do {
		if(curGame['winnerId'] != null) {
			// Go through each user, set new score
			for(let userId = 0; userId <= 3; userId++) {
				if(picks[curId][userId] == curGame['winnerId']) {
					userScore[userId]++;
				}
			}
		}

		// Update cells - set score and leader class
		for(let userId = 0; userId <= 3; userId++) {
			$('#score-' + userId + '-' + curId).text(userScore[userId]);
			if(userScore[userId] == Math.max(...userScore)) {
				$('#score-' + userId + '-' + curId).addClass('leader');
			}
		}
		
		// Step through to the next game
		curId = curGame['next'];
		if(curId != 0) {
			curGame = picks[curId]['game'];
		}
	} while (curId);
}