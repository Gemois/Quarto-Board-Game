var me = { username: null, token: null ,role: null};
var game_status={status:null,p_turn:null,current_piece:null,result:null,last_change:null};
var last_update=new Date().getTime();
var timer=null;


$(function () {
	draw_empty_board();	
$('#quatro_login').click(login_to_game);
$('#start_reset_game').click(reset_game);
$('piece_selected').click(pick);
current_piece;
	
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
			t += '<td  class="quarto_square" id="square_' + i + '_' + j + '"> <img class="piece" src="images/p.png"></BR> '+i +','+j+'  </img></td>';
		}
		t += '</tr>';
	}
	t += '</table>';
	$('#quarto_board').html(t);
}


function reset_game(){
	$.ajax({ url: "quarto.php/board/",
	method: "POST",
	success: (function(){location.reload();}),
	 headers: { "X-Token": me.token }
   });


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
		data: JSON.stringify({username: $('#username').val() }),
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

	$('#player_info').html("<h3>Player info =></h3><strong> Username:</strong>"
							+me.username+"<strong> token: </strong>"
							+me.token+"<strong> Player role: </strong> "
							+me.role+ "<strong> Game state: </strong>"
							+game_status.status+"<strong> Player turn: </strong>"
							+game_status.p_turn+"<strong> Current Piece: </strong>"
							+game_status.current_piece);
}

/**
 * updates the webpage dynamically
 * 
 */

function game_status_update() {
	clearTimeout(timer);
	$.ajax({ url: "quarto.php/status/",
			 method: 'GET',
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
			$('#piece_selector_input').show(10000);
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



function current_piece(){
	$('#current_piece').attr("src","\"images/p"+game_status.current_piece+".png\"");
	}



/**
 * makes request to place piece
 * sends x , y as coordinates retrieved 
 * from webpage
 */

function do_place() {
	current_piece;
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
		method:'GET',
		url: "quarto.php/board/piece/pick",
		contentType: 'application/json',
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
	var piece_list = JSON.parse(list);
	for (var i = 0; i < piece_list.length; i++) {
		$('#piece_selector').append("<option value=\""+piece_list[i]+"\">"+piece_list[i]+"</option>");
	}
	update_pieces_remaining_images(piece_list);
}

function update_pieces_remaining_images(list){
	for (var i = 0; i < list.length; i++) {
$('#piece_images').append("<img src=\"images/p"+list[i]+".png\" alt=\"Piece :"+list[i]+"\">");
	}
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
	
	for(var i=0;i<data.length;i++) {
		var o = data[i];
		var id = '#square_'+ o.x +'_' + o.y ;
		if(o.piece==null){
			var im ='<img class="piece" src="images/p.png"></BR> '+o.x +','+o.y+'  </img>';
		}else{
			var im ='<img class="piece" src="images/p'+ o.piece +'.png"></BR> '+o.x +','+o.y+'  </img>';
		}
		$(id).html(im);
	}
}