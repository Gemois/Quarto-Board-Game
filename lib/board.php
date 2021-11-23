<?php

		
function show_board($input) {
		header('Content-type: application/json');
		print json_encode(read_board(), JSON_PRETTY_PRINT);
}



function reset_board() {
	global $mysqli;
	$sql = 'call clean_board()';
	$mysqli->query($sql);
	
}


function read_board() {
	global $mysqli;
	$sql = 'select * from board';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	return($res->fetch_all(MYSQLI_ASSOC));
}



// piece selector 

function pick_piece($piece_id,$input){


	if($token==null || $token=='') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"token is not set."]);
		exit;
	}
	$player_id = current_player($token);
	if($player_id==null ) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"You are not a player of this game."]);
		exit;
	}
	$status = read_status();
	if($status['status']!='started') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"Game is not in action."]);
		exit;
	}
	if($status['p_turn']!=$player_id) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"It is not your turn."]);
		exit;
	}
    if(check_available_piece($piece_id)){
        do_pick_piece($x,$y);
    }
	header("HTTP/1.1 400 Bad Request");
	print json_encode(['errormesg'=>"You cant place your piece here."]);
	exit;

}


//checks if the piece we chose is available     [ note : maybe i dont need this function  ]


function check_available_piece($piece_id){
    global $mysqli;

    $sql = 'SELECT available from pieces where pieces_id=$piece_id';
    $st = $mysqli->prepare($sql);
    $st->execute();
    $res =$st->get_result();
    $res->fetch_row(MYSQLI_ASSOC);

if ($res){
    return true;
}else 
return false;
}


//changes the availability of the piece , sets the current piece  and sets the next player .

function do_pick_piece($piece_id){
    make_piece_unavelable($piece_id);
    set_current_piece($piece_id);
    global $mysqli;
    $next_player=next_player($token);
    $sql = 'UPDATE game_status SET p_turn=? WHERE pieces_id=piece_id;';
    $st = $mysqli->prepare($sql);
    $st->bind_param('i',$next_player);
    $st->execute();
}


//makes a piece unvalaible for the rest of the game

function make_piece_unavelable($piece_id){

    global $mysqli;
    $sql = 'UPDATE pieces SET available="false" WHERE pieces_id=piece_id;';
    $st = $mysqli->prepare($sql);
    $st->execute();

}


//stores the piece about to be played

function set_current_piece($piece_id){
    global $mysqli;
    $sql = 'UPDATE game_status  SET current_piece=? ';
    $st = $mysqli->prepare($sql);
    $st->bind_param('i',$piece_id);
    $st->execute();
}


//returns next player id

function next_player($token){

    global $mysqli;
    $sql = 'SELECT user_id from users where token!=$token';
    $st = $mysqli->prepare($sql);
    $st->execute();
    $res =$st->get_result();
    $res->fetch_row(MYSQLI_ASSOC);
    return $res;
}


// ckecks all the restraints in order to place the  piece on the board 


function place_piece($x,$y,$token) {
	
	if($token==null || $token=='') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"token is not set."]);
		exit;
	}
	
	$player_id = current_player($token);
	if($player_id==null ) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"You are not a player of this game."]);
		exit;
	}
	$status = read_status();
	if($status['status']!='started') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"Game is not in action."]);
		exit;
	}
	if($status['p_turn']!=$player_id) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"It is not your turn."]);
		exit;
	}
	
	if(!check_empty_box($x,$y)){
        do_place_piece($x,$y);
    }
	header("HTTP/1.1 400 Bad Request");
	print json_encode(['errormesg'=>"You cant place your piece here."]);
	exit;
}
		




// executes the placement ont the piece on the board 

function do_place_piece($x,$y){

    $piece_id =curent_selected_piece();

        global $mysqli;
        $sql = 'call `place_piece`(?,?,?);';
        $st = $mysqli->prepare($sql);
        $st->bind_param('iii',$x,$y,$piece_id );
        $st->execute();
        header('Content-type: application/json');
        print json_encode(read_board(), JSON_PRETTY_PRINT);
    
    change_role($token);
}



//changes the role of the user to his current role , in this case pick 

function change_role_pick($token){


    global $mysqli;
    $sql = 'UPDATE users SET role="pick"' ;
    $st = $mysqli->prepare($sql);
    $st->execute();
}


//changes the role of the user to his current role , in this case place 



function change_role_place($token){


    global $mysqli;
    $sql = 'UPDATE users SET role="place"' ;
    $st = $mysqli->prepare($sql);
    $st->execute();
}



//retrieves and returns the selected pice to be played


function curent_selected_piece(){

    global $mysqli;
	$sql = 'select current_piece from game_status ';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res =$st->get_result();
    $res->fetch_row(MYSQLI_ASSOC);
    return $res;
}



//retrieves and returns the role [pick / place ] of the user 
 


function current_role($token){

    global $mysqli;
	$sql = 'select role from player where token=?';
	$st = $mysqli->prepare($sql);
    $st->bind_param('s',$token);
	$st->execute();
	$res =$st->get_result();
    $res->fetch_row(MYSQLI_ASSOC);
    return $res;
}




//checks if the box is empty  [ legal to place ]


function check_empty_box($x,$y){
    global $mysqli;
	$sql = 'select piece from player where x=?and y=?';
	$st = $mysqli->prepare($sql);
    $st->bind_param('ii',$x,$y);
	$st->execute();
	$res =$st->get_result();
    if($res){
        return false ;
    }else{
    return true;
    }
}





// check after winning placement of a piece


function check_winning_placement($x,$y){

    $light_pieces=array(1,2,3,4,5,6,7,8,9);
    $dark_pieces=array(10,11,12,13,14,15,16);

    $round_pieces=array(5,6,7,8,13,14,14,16);
    $square_pieces=array(1,2,3,9,10,11,12);


    $hollow_pieces=array(2,4,6,8,10,12,14,16);
    $solid_pieces=array(1,3,5,7,9,11,13,15);

    $short_pieces=array(3,4,7,8,11,12,15,16);
    $tall_pieces=array(1,2,5,6,9,10,13,14);

 
 

    $hl = count(array_intersect(horisontal_pieces($x,$y), $light_pieces)) === 4;
    $vl = count(array_intersect(vertical_pieces($x,$y), $light_pieces)) === 4;
    $ldl = count(array_intersect(check_left_diagonal_pieces($x,$y), $light_pieces)) === 4;
    $rdl = count(array_intersect(check_right_diagonal_pieces($x,$y), $light_pieces)) === 4;


    $hd = count(array_intersect(horisontal_pieces($x,$y), $dark_pieces)) === 4;
    $vd = count(array_intersect(vertical_pieces($x,$y), $dark_pieces)) === 4;
    $ldd = count(array_intersect(check_left_diagonal_pieces($x,$y), $dark_pieces)) === 4;
    $rdd = count(array_intersect(check_right_diagonal_pieces($x,$y), $dark_pieces)) === 4;


    $hr = count(array_intersect(horisontal_pieces($x,$y), $round_pieces)) === 4;
    $vr = count(array_intersect(vertical_pieces($x,$y), $round_pieces)) === 4;
    $ldr = count(array_intersect(check_left_diagonal_pieces($x,$y), $round_pieces)) === 4;
    $rdr = count(array_intersect(check_right_diagonal_pieces($x,$y), $round_pieces)) === 4;



    $hsq = count(array_intersect(horisontal_pieces($x,$y), $square_pieces)) === 4;
    $vsq = count(array_intersect(vertical_pieces($x,$y), $square_pieces)) === 4;
    $ldsq = count(array_intersect(check_left_diagonal_pieces($x,$y), $square_pieces)) === 4;
    $rdsq = count(array_intersect(check_right_diagonal_pieces($x,$y), $square_pieces)) === 4;




    $hh = count(array_intersect(horisontal_pieces($x,$y), $hollow_pieces)) === 4;
    $vh = count(array_intersect(vertical_pieces($x,$y), $hollow_pieces)) === 4;
    $ldh = count(array_intersect(check_left_diagonal_pieces($x,$y), $hollow_pieces)) === 4;
    $rdh = count(array_intersect(check_right_diagonal_pieces($x,$y), $hollow_pieces)) === 4;



    $hs = count(array_intersect(horisontal_pieces($x,$y), $solid_pieces)) === 4;
    $vs = count(array_intersect(vertical_pieces($x,$y), $solid_pieces)) === 4;
    $lds = count(array_intersect(check_left_diagonal_pieces($x,$y), $solid_pieces)) === 4;
    $rds = count(array_intersect(check_right_diagonal_pieces($x,$y), $solid_pieces)) === 4;



    $hsh = count(array_intersect(horisontal_pieces($x,$y), $short_pieces)) === 4;
    $vsh = count(array_intersect(vertical_pieces($x,$y), $short_pieces)) === 4;
    $ldsh = count(array_intersect(check_left_diagonal_pieces($x,$y), $short_pieces)) === 4;
    $rdsh = count(array_intersect(check_right_diagonal_pieces($x,$y), $short_pieces)) === 4;




    $ht = count(array_intersect(horisontal_pieces($x,$y), $tall_pieces)) === 4;
    $vt = count(array_intersect(vertical_pieces($x,$y), $tall_pieces)) === 4;
    $ldt = count(array_intersect(check_left_diagonal_pieces($x,$y), $tall_pieces)) === 4;
    $rdt = count(array_intersect(check_right_diagonal_pieces($x,$y), $tall_pieces)) === 4;



if ($hl || $vl|| $ldl ||$rdl|| $hd|| $vd ||  $ldd  || $rdd  || $hr  || $vr  || $ldr  || $rdr  ||  $hsq ||   $vsq  ||  $ldsq   || $rdsq ||  $hh ||  $vh  || $ldh ||   $rdh  || $hs  || $vs ||  $lds  || $rds ||   $hsh  ||  $vsh ||  $ldsh  || $rdsh ||   $ht ||  $vt ||  $ldt ||  $rdt){
return true;
}else{
return false;
}
}


// returns the values of the row with the last piece placement

function horisontal_pieces($x,$y){

    $res=array();
    for($i = 1; $i<=4; $i++){
    global $mysqli;
	$sql = 'select piece from board where x=? and y=?';
	$st = $mysqli->prepare($sql);
    $st->bind_param('i',$i,$y);
	$st->execute();
    $res = array_push(fetch_row(MYSQLI_ASSOC));
}
return $res;
}

// returns the valus of the column with the last piece placement


function vertical_pieces($x,$y){


    $res=array();
    for($i = 1; $i<=4; $i++){
    global $mysqli;
	$sql = 'select piece from board where x=? and y=?';
	$st = $mysqli->prepare($sql);
    $st->bind_param('i',$x,$i);
	$st->execute();
    $st->get_result();
	$res = array_push(fetch_row(MYSQLI_ASSOC));
    }
    return $res;
}




// returns the values of the primary diagonal with the last piece placement


function check_left_diagonal_pieces($x,$y){
    if ($x=$y){
        $res=array();
        for($i = 1; $i<=4; $i++){
            for($j = 1; $j<=4; $j++){
                if($x=$y){
                    global $mysqli;
	                $sql = 'select piece from board where x=? and y=?';
	                $st = $mysqli->prepare($sql);
                    $st->bind_param('i',$x,$i);
	                $st->execute();
                    $st->get_result();
	                $res = array_push(fetch_row(MYSQLI_ASSOC));
                }
            }
        }
    return $res;
    }else 
    return null;
}

// returns the values of the secondary diagonal with the last piece placement


function check_right_diagonal_pieces($x,$y){
    if ($x+$y=4){
        $res=array();
        for($i = 1; $i<=4; $i++){
            for($j = 1; $j<=4; $j++){
                if($x+$y=4){
                    global $mysqli;
                    $sql = 'select piece from board where x=? and y=?';
                    $st = $mysqli->prepare($sql);
                    $st->bind_param('i',$x,$i);
                    $st->execute();
                    $res = array_push($st->get_result());
                }
    
            }
        }
    return $res;
    }else
        return null;
}