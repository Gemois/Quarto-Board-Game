var me = { username: null, token: null ,role: null};



$(function () {
	draw_empty_board();
	$('#quatro_login').click( login_to_game);

});

/**
 * draws board on webpage
 * creates table and inserts it in a div dynamically 
 */

function draw_empty_board() {
	var t = '<table id="quarto_table">';
	for (var i = 4; i > 0; i--) {
		t += '<tr>';
		for (var j = 1; j <= 4; j++) {
			t += '<td class="quarto_square" id="square_' + i + '_' + j + '">'+i+','+j+' </td>';
		}
		t += '</tr>';
	}
	t += '</table>';
	$('#quarto_board').html(t);
}

/**
 * makes request for player login 
 */

function login_to_game() {
	if ($('#username').val() == '') {
		alert('You have to set a username');
		return;
	}
	draw_empty_board();
	$.ajax({
		url: "quarto.php/players/login",
		method: 'PUT',
		dataType: "json",
		headers: { "X-Token": me.token },
		contentType: 'application/json',
		data: JSON.stringify({ username: $('#username').val() }),
		success: login_result,
		error: login_error
	});
}

/**
 * stores the player info loacaly
 * @param {json} data 
 * cals update_info
 */

function login_result(data) {
	me = data[0];
	$("#game_login_input").hide();
	update_info();
	game_status_update();
}

/**
 * error information handling
 * @param {json} data
 */

function login_error(data) {
	var x = data.responseJSON;
	//alert(x.errormesg);
}

/**
 * updates the webpage dynamically
 * 
 */

function update_info() {
	$('#player_info').html("<h2>Player Info</h2></br>" +
		"<h3>Username: </h3>" + me.username + "</br>" +
		"<h3>token: </h3>" + me.token + "</br>" +
		"<h3>Game state: </h3>" + game_status.status + "</br>" +
		"<h3>Player turn: </h3>" + game_status.p_turn + "</br>");
}

/**
 * updates the webpage dynamically
 * 
 */

function game_status_update() {
	clearTimeout(timer);
	$.ajax({ url: "quarto.php/status/",
	 		 success: update_status,
	  		 headers: { "X-Token": me.token }
			 });
}

/**
 * updates  local users info
 * 
 */

function update_user(){
	$.ajax({ url: "quarto.php/player/login",
		     method: 'GET',
		     dataType: "json",
		     headers: { "X-Token": me.token },
		     contentType: 'application/json',
			 success: update_status
   });

}

/**
 * enables and disables the ui elements responsible for gameplay
 * according to player turn and role
 * 
 * turn is defined with player token
 * role can be either pick or place 
 */

function update_status(data) {
	last_update=new Date().getTime();
	var game_stat_old = game_status;
	game_status=data[0];
	update_user();
	update_info();
	clearTimeout(timer);
	if(game_status.p_turn==me.token && me.role!=null) {
		fill_board();
		if (me.role=="pick"){
			$('#piece_selector_input').show(1000);
			timer=setTimeout(function() { game_status_update();}, 15000);
		}else{
			$('#piece_coordinates_input').show(1000);
			timer=setTimeout(function() { game_status_update();}, 15000);
		}	


	}else{
		$('#piece_selector_input').hide(1000);
		$('#piece_coordinates_input').hide(1000);
		timer=setTimeout(function() { game_status_update();}, 4000);
	}
}

/**
 * makes request to place piece
 * sends x , y as coordinates retrieved 
 * from webpage
 */

function do_place() {
	var s = $('#piece_coordinates').val();

	var a = s.trim().split(/[ ]+/);
	if (a.length != 2) {
		alert('Must give 2 numbers');
		return;
	}
	$.ajax({
		url: "quarto.php/board/piece/place/",
		method: 'PUT',
		dataType: "json",
		contentType: 'application/json',
		data: JSON.stringify({ x: a[0], y: a[1] }),
		headers: { "X-Token": me.token },
		success: move_result,
		error: move_error
	});

}

/**
 * if place request succesful 
 * updates status
 */

function move_result(data) {
	game_status_update();
	fill_board_by_data(data);
}

/**
 *error information handling
 * @param {json} data
 */

function move_error(data) {
	var x = data.responseJSON;
	alert(x.errormesg);

}

/**
 *makes a request to retrieve all available pieces
 */

function piece_list() {

	$.ajax({
		url: "quarto.php/board/pick",
		headers: { "X-Token": me.token },
		success: update_piece_selector
	});


}

/**
 *updates selector element on webpage 
 *with all available pieces retrived by 
 *piece_list() function
 */

function update_piece_selector(list) {
	$piece_list = list;
	for (var i = 0; i < $piece_list.length; i++) {
		$('#piece_selector').append(new Option($piece_list[i][piece_id], $piece_list[i][piece_id]))
	}
}

/**
 * makes request to pick piece
 * sends piece_id to identify which piece we need
 * from webpage
 */

function pick(piece) {
	var s = $('#piece_coordinates').val();
	$.ajax({
		url: "quarto.php/board/piece/pick/",
		method: 'PUT',
		dataType: "json",
		contentType: 'application/json',
		data: JSON.stringify({ piece_id: piece }),
		headers: { "X-Token": me.token },
		success: pick_result,
		error: pick_error
	});
}

/**
 * if place request succesful 
 * updates status
 */

function pick_result(data) {
	game_status_update();
}

/**
 *error information handling
 * @param {json} data
 */

function pick_error(data) {
	var x = data.responseJSON;
	alert(x.errormesg);
}


/**
 *makes request to get current state of board
 * calls fill_board_by_data on succes 
 */

function fill_board() {
	$.ajax({url: "quarto.php/board/", 
		headers: {"X-Token": me.token},
		success: fill_board_by_data });
}

/**
 *fills table cells of web page dynamically with given state of board 
 *and adds image representation of the piece
 */

function fill_board_by_data(data){
	board=data;
	for(var i=0;i<data.length;i++) {
		var o = data[i];
		var id = '#square_'+ o.x +'_' + o.y;
		if(o.piece==null){
			var im ='<img class="piece" src="images/'+ p +'.png">';
		}else{
			var im ='<img class="piece" src="images/'+'p'+ o.piece-1 +'.png">';
		}
		$(id).html(im);
	}
}







