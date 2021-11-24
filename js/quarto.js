
var me={username:null,token:null};



$(function () {





});




function login_to_game() {
	if($('#username').val()=='') {
		alert('You have to set a username');
		return;
	}
	draw_empty_board();
	
	
	$.ajax({url: "chess.php/players/login", 
			method: 'PUT',
			dataType: "json",
			headers: {"X-Token": me.token},
			contentType: 'application/json',
			data: JSON.stringify( {username: $('#username').val()}),
			success: login_result,
			error: login_error});
}



function login_result(data) {
	me = data[0];
	$("#game_login_input").hide();
	update_info();
	game_status_update();
}

function login_error(data,y,z,c) {
	var x = data.responseJSON;
	alert(x.errormesg);
}


function update_info(){
	$('#player_info').html("<h2>Player Info</h2></br>"+ 
                            "<h3>Username: </h3>"+ me.username+"</br>"+
                            "<h3>token: </h3>"+me.token+"</br>"+
                            "<h3>Game state: </h3>"+game_status.status+"</br>"+
                            "<h3>Player turn: </h3>"+game_status.p_turn+"</br>");	
}



function game_status_update() {
	
	clearTimeout(timer);
	$.ajax({url: "chess.php/status/", success: update_status,headers: {"X-Token": me.token} });
}






function do_place() {
	var s = $('#piece_coordinates').val();
	
	var a = s.trim().split(/[ ]+/);
	if(a.length!=2) {
		alert('Must give 2 numbers');
		return;
	}
	$.ajax({url: "chess.php/board/piece/place/", 
			method: 'PUT',
			dataType: "json",
			contentType: 'application/json',
			data: JSON.stringify( {x: a[0], y: a[1]}),
			headers: {"X-Token": me.token},
			success: move_result,
			error: login_error});
	
}











