<?php

function show_users() {
	global $mysqli;
	$sql = 'select username from players';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
}


function show_user($token) {
	global $mysqli;
	$sql = 'select username from players where token=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('s',$token);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
}




function set_user($input) {

	if(!isset($input['username'])) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"No username given."]);
		exit;
	}
	$username=$input['username'];
	global $mysqli;
	$sql = 'select count(*) as c from players ';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$r = $res->fetch_all(MYSQLI_ASSOC);
	if($r[0]['c']=0) {
        register_first_player($input);
	}elseif($r[0]['c']=1){
        register_second_player($input);
    }
	
	update_game_status();
	
}

register_first_player($input);{
	global $mysqli;
	$sql = 'update players set username=?, token=md5(CONCAT( ?, NOW())) ,role="pick"';
	$st2 = $mysqli->prepare($sql);
	$st2->bind_param('s',$username);
	$st2->execute();

    $sql = 'select token from players';
	$st2 = $mysqli->prepare($sql);
	$st2->bind_param('s',$username);
	$st2->execute();
    $res = $st->get_result();
    $res->fetch_all(MYSQLI_ASSOC)
    set_current_turn($res);


    show_user($res);
}



set_current_turn($token){
    $sql = 'update game_status set current_player="$token"';
	$st2 = $mysqli->prepare($sql);
	$st2->bind_param('s',$username);
	$st2->execute();
}



register_second_player($input){
	global $mysqli;
    $sql = 'update players set username=?, token=md5(CONCAT( ?, NOW())) ,role="place"';
	$st2 = $mysqli->prepare($sql);
	$st2->bind_param('s',$username);
	$st2->execute();


    $sql = 'select token from players';
	$st2 = $mysqli->prepare($sql);
	$st2->bind_param('s',$username);
	$st2->execute();
    $res = $st->get_result();
    $res->fetch_all(MYSQLI_ASSOC)
  
    show_user($res);
}




function handle_user($method, $b,$input) {
	if($method=='GET') {
		show_user($b);
	} else if($method=='PUT') {
        set_user($b,$input);
    }
}





?>