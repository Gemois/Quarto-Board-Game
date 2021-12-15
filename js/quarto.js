var me = { username: null, player_id: null, token: null, role: null };
var game_status = { status: null, p_turn: null, current_piece: null, result: null, win_direction: null, last_change: null };
var last_update = new Date().getTime();
var timer = null;

$(function () {
	draw_empty_board();
	$('#piece_selector_input').hide();
	$('#piece_coordinates_input').hide();
	$('#winner').hide();
	$('#loser').hide();
	$('#draw').hide();
	$('#quatro_login').click(login_to_game);
	$('#start_reset_game').click(reset_game);
	$('#piece_selected').click(pick);
	$('#waiting').hide();
	$('#place_piece').click(do_place);


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
			t += '<td  class="quarto_square" id="square_' + i + '_' + j + '"> <img class="piece" src="images/p.png"></BR> ' + i + ',' + j + '  </img></td>';
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
		url: "quarto.php/players/login/",
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
	piece_list();
	update_info();
	game_status_update();
}

/**
 * error information handling
 * @param {json} data
 */

function login_error(data) {
	var x = data.responseJSON;
	alert(x.errormesg);
}

/**
 * updates the webpage dynamically
 * 
 */

function update_info() {
	$('#player_info').html("<h4>Player info</h4><strong> Username:</strong>"
		+ me.username + "<strong> id: </strong>"
		+ me.player_id + "<strong> token: </strong>"
		+ me.token + "<strong> Player role: </strong> "
		+ me.role + "<br><h4>Game info</h4><strong> Game state: </strong>"
		+ game_status.status + "<strong> Player turn: </strong>"
		+ game_status.p_turn + "<strong> Current Piece: </strong>"
		+ game_status.current_piece + "<strong> Result: </strong>"
		+ game_status.result + "<strong> win_direction: </strong>"
		+ game_status.win_direction + "<strong> Last_change: </strong>"
		+ game_status.last_change);
}

/**
 * updates the webpage dynamically
 * 
 */

function game_status_update() {
	clearTimeout(timer);
	$.ajax({
		url: "quarto.php/status/",
		method: 'GET',
		success: update_status,
		headers: { "X-Token": me.token }
	});
}

/**
 * makes http request to get player info
 * updates local player info in case of changes in the database
 */

function update_user() {
	$.ajax({
		url: "quarto.php/players/login/",
		method: 'GET',
		dataType: "json",
		headers: { "X-Token": me.token },
		contentType: 'application/json',
		success: save_player_info
	});

}

/**
 * updates  local users info
 * stores the changes
 */

function save_player_info(data) {
	me = data[0];
}


/**
 * enables and disables the ui elements responsible for gameplay
 * according to player turn and role
 * 
 * turn is defined with player token
 * role can be either pick or place 
 */

function update_status(data) {
	last_update = new Date().getTime();
	game_status = data[0];
	if (game_status.result == "W") {
		$('#piece_selector_input').hide();
		$('#piece_coordinates_input').hide();
		$('#waiting').hide();
		if (game_status.p_turn != me.player_id) {
			highlight_winning_pieces(game_status.win_direction);
			$('#loser').show(1000);
		} else {
			highlight_winning_pieces(game_status.win_direction);
			$('#winner').show(1000);
		}
		fill_board();
		update_info();
		exit();
	} else if (game_status.result == "D") {
		$('#piece_selector_input').hide();
		$('#piece_coordinates_input').hide();
		$('#waiting').hide();
		$('#draw').show(1000);
		fill_board();
		update_info();
		exit();
	}
	update_user();
	update_info();
	clearTimeout(timer);
	if (game_status.p_turn == me.player_id && me.role != null) {
		fill_board();
		if (me.role == "pick") {

			$('#piece_selector_input').show(1000);
			$('#piece_coordinates_input').hide();
			timer = setTimeout(function () { game_status_update(); }, 1000);
		} else {
			$('#waiting').hide();
			current_piece();
			$('#piece_coordinates_input').show(1000);
			timer = setTimeout(function () { game_status_update(); }, 1000);
		}
	} else {
		fill_board();
		$('#waiting').show();
		$('#piece_selector_input').hide(1000);
		$('#piece_coordinates_input').hide(1000);
		timer = setTimeout(function () { game_status_update(); }, 1000);
	}
}

/**
 *Changes background color to winning squares
 * * @param {string} win_direction
 */

function highlight_winning_pieces(win_direction) {
	direction = win_direction.substring(0, win_direction.length - 1);
	switch (direction) {
		case 'vertical':
			var y = win_direction.charAt(win_direction.length - 1);
			for (var x = 1; x <= 4; x++) {
				var id = '#square_' + x + '_' + y;
				$(id).css('background-color', '#FC7E7E');
			}
			break;
		case 'horisontal':
			var x = win_direction.charAt(win_direction.length - 1);
			for (var y = 1; y <= 4; y++) {
				var id = '#square_' + x + '_' + y;
				$(id).css('background-color', '#FC7E7E');
			}
			break;
		case 'left diagonal':
			for (var i = 1; i <= 4; i++) {
				for (j = 1; j <= 4; j++) {
					if (i == j) {
						var id = '#square_' + i + '_' + j;
						$(id).css('background-color', '#FC7E7E');
					}
				}
			}
			break;
		case "right diagonal":

			for (i = 1; i <= 4; i++) {
				for (j = 1; j <= 4; j++) {
					if (i + j == 5) {
						var id = '#square_' + i + '_' + j;
						$(id).css('background-color', '#FC7E7E');
					}
				}
			}
			break;
		default: break;
	}
}



/**
 *makes request to get current state of board
 * calls fill_board_by_data on succes 
 */

function fill_board() {
	$.ajax({
		url: "quarto.php/board/",
		headers: { "X-Token": me.token },
		success: fill_board_by_data
	});
}

/**
 *fills table cells of web page dynamically with given state of board 
 *and adds image representation of the piece
 */

function fill_board_by_data(data) {

	for (var i = 0; i < data.length; i++) {
		var o = data[i];
		var id = '#square_' + o.x + '_' + o.y;
		if (o.piece == null) {
			var im = '<img class="piece" src="images/p.png"></BR> ' + o.x + ',' + o.y + '  </img>';
		} else {
			var im = '<img class="piece" src="images/p' + o.piece + '.png"></BR> ' + o.x + ',' + o.y + '  </img>';
		}
		$(id).html(im);
	}
}

function current_piece() {
	$('#curent_piece_img').html("<img src=\"images/p" + game_status.current_piece + ".png\"></img>");
}


/**
* makes request to pick piece
* sends piece_id to identify which piece we need
* from webpage
*/

function pick() {
	var s = $('#piece_selector').val();

	$.ajax({
		url: "quarto.php/board/piece/pick/",
		method: 'PUT',
		dataType: "json",
		contentType: 'application/json',
		data: JSON.stringify({ piece_id: s }),
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
 * makes request to place piece
 * sends x , y as coordinates retrieved 
 * from webpage
 */

function do_place() {
	empty_piece_list();
	piece_list();
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
		success: place_result,
		error: place_error
	});

}

/**
 * if place request succesful 
 * updates status
 */

function place_result(data) {
	game_status_update();
	fill_board_by_data(data);
}

/**
 *error information handling
 * @param {json} data
 */

function place_error(data) {
	var x = data.responseJSON;
	alert(x.errormesg);

}

/**
 *makes a request to retrieve all available pieces
 */

function piece_list() {
	$.ajax({
		method: 'GET',
		url: "quarto.php/board/piece/pick",
		contentType: 'application/json',
		headers: { "X-Token": me.token },
		success: update_piece_selector
	});


}

/**
 * erases all options from piece selector 
 *erases all images corresponding to those options
 *in order to be dynamically refilled with new piece list
 */

function empty_piece_list() {
	$('#piece_selector').html("<option value=\"\">---Choose piece---</option>");
	$('#piece_images').html(" ");
}

/**
 *updates selector element on webpage 
 *with all available pieces retrived by 
 *piece_list() function
 */

function update_piece_selector(list) {
	var piece_list = JSON.parse(list);
	for (var i = 0; i < piece_list.length; i++) {
		$('#piece_selector').append("<option value=\"" + piece_list[i] + "\">" + piece_list[i] + "</option>");
	}
	update_pieces_remaining_images(piece_list);
}

/**
*updates images of remaining available pieces 
*
*/

function update_pieces_remaining_images(list) {
	for (var i = 0; i < list.length; i++) {
		$('#piece_images').append("<img src=\"images/available_images/p" + list[i] + ".png\" alt=\"Piece :" + list[i] + "\">");
	}
}

/**
*send http request to reset all essential db tables
*/

function reset_game() {
	$.ajax({
		url: "quarto.php/board/",
		method: "POST",
		success: (function () { location.reload(); }),
		headers: { "X-Token": me.token }
	});
}