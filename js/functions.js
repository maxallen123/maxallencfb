function setPick(gameId, userId) {
	// Prep variables
	selectId = '#pick-' + userId + '-' + gameId;
	selectVal = $(selectId).find(":selected").val();
	week = $('#week').val();
	year = $('#year').val();
	
	// Call AJAX update
	$.ajax({
		method: "GET",
		url: "../ajax/ajax.php",
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
		url: "../ajax/ajax.php",
		data: {
			function: 'updatePicks',
			week: week,
			year: year
		},
		datatype: 'json',
		success:
			function(picks) {
				//console.log(picks) // Debug info
				// Go through each game and see what is in the DB
				$.each(picks, function(gameId, pick) {
					// Go through each user
					for(let userId = 0; userId <= 3; userId++) {
						// Get the ID of the select
						selectId = '#pick-' + userId + '-' + gameId;
						// Set the select
						$(selectId).val(pick[userId]);
					}
				});
			}

	});
}