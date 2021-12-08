<?php

/**
 * prints game status table 
 * @return json prints table data  [status , p_turn , current_piece , result , last_change]
 */

function show_status()
{
	global $mysqli;
	$sql = 'select * from game_status';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
}

/**
 * reads game status 
 * @return array $status  
 * table data  [status , p_turn , current_piece , result , last_change]*
 */

function read_status()
{
	global $mysqli;
	$sql = 'select * from game_status';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$status = $res->fetch_assoc();
	return ($status);
}

/**
 * updates game status table   
 * 
 * checks all possible scenarios and sets the appropriate status 
 * status can be either ( active , initialized , started , ended , aborded )
 */

function update_game_status()
{
	global $mysqli;
	$status = read_status();
	$new_status = null;
	$new_turn = null;
	$sql = 'SELECT count(*) as aborted from players WHERE last_action< (NOW() - INTERVAL 5 MINUTE)';
	$st3 = $mysqli->prepare($sql);
	$st3->execute();
	$res3 = $st3->get_result();
	$aborted = $res3->fetch_assoc();
	if ($aborted['aborted'] > 0) {
		$sql = 'DELETE players  WHERE last_action< (NOW() - INTERVAL 5 MINUTE)';
		$st2 = $mysqli->prepare($sql);
		$st2->execute();
		if ($status['status'] == 'started') {
			$new_status = 'aborted';
		}
	}
	$sql = 'select count(*) as c from players where username is not null';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$active_players = $res->fetch_assoc();

	switch ($active_players['c']) {
		case 0:
			$new_status = 'not active';
			$new_turn = null;
			break;
		case 1:
			$new_status = 'initialized';
			break;
		case 2:
			$new_status = 'started';
			break;
	}

	$sql = 'update game_status set status=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('s', $new_status);  
	$st->execute();
}
?>