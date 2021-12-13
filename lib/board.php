<?php

/**
 * prints board
 * @param json $input 
 * prints board using json format 
 */

function show_board($input)
{
    header('Content-type: application/json');
    print json_encode(read_board(), JSON_PRETTY_PRINT);
}

/**
 * reads board table
 * @return array with table contents
 *  table data (x , y , piece)
 */

function read_board()
{
    global $mysqli;
    $sql = 'select * from board';
    $st = $mysqli->prepare($sql);
    $st->execute();
    $res = $st->get_result();
    return ($res->fetch_all(MYSQLI_ASSOC));
}

/**
 * resets board 
 * calls mysql store procidures  
 * to reset all board rows 
 * to reset players rows
 * to reset piece availability
 * to reset game_status information
 */

function reset_board()
{
    global $mysqli;
    $sql = 'call clean_board()';
    $mysqli->query($sql);
    $sql1 = 'call reset_players()';
    $mysqli->query($sql1);
    $sql2 = 'call reset_pieces()';
    $mysqli->query($sql2);
    $sql3 = 'call clean_game_status()';
    $mysqli->query($sql3);
}

/**
 * prints the available pieces
 * availability can be true or false
 * @return json  all pieces_id 
 */

function piece_list()
{
    global $mysqli;
    $sql = 'SELECT pieces_id from pieces where available=true';
    $st = $mysqli->prepare($sql);
    $st->execute();
    $res = $st->get_result();
    print json_encode($res->fetch_all(), JSON_PRETTY_PRINT);
}

/**
 * checks if player meets all requirmets to pick piece
 * @param $input json  
 * and then calls do_pick_piece
 */

function pick_piece($input)
{
    if ($input['piece_id'] == "") {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "piece is not set."]);
        exit;
    }
    if ($input['token'] == null) {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "token is not set."]);
        exit;
    }
    $status = read_status();
    if ($status['status'] != 'started') {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "Game is not in action."]);
        exit;
    }
    if ($status['p_turn'] != $input['token']) {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "It is not your turn."]);
        exit;
    }
    do_pick_piece($input);
}

/**
 * picks the piece for other player to place
 * @param int $piece_id contains the id of the piece
 * id is [1-16] 
 * calls make_piece_unavelable
 * calls set_current_piece
 */

function do_pick_piece($input)
{
    make_piece_unavelable($input['piece_id']);
    set_current_piece($input['piece_id']);
    change_role_place($input['token']);
    next_player($input['token']);
}


/**
 * flags chosen piece unvelable to pick
 * @param int $piece_id contains the id of the piece
 */

function make_piece_unavelable($piece_id)
{
    global $mysqli;
    $sql = 'UPDATE pieces SET available=false WHERE pieces_id=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('i', $piece_id);
    $st->execute();
}

/**
 * stores in game status table the piece about to be played for the next player
 * @param int $piece_id contains the id of the piece
 */

function set_current_piece($piece_id)
{
    global $mysqli;
    $sql = 'UPDATE game_status  SET current_piece=? ';
    $st = $mysqli->prepare($sql);
    $st->bind_param('i', $piece_id);
    $st->execute();
}

/**
 * sets user role to place
 * @param string $token user unique identifier
 */

function change_role_place($token)
{
    global $mysqli;
    $sql = 'UPDATE players SET `role`="place" WHERE token=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('s', $token);
    $st->execute();
}

/**
 * checks if player meets all requirmets to place piece
 * @param $input json  
 * and then calls do_place_piece
 */

function place_piece($input)
{
    if ($input['token'] == null || $input['token'] == '') {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "token is not set."]);
        exit;
    }
    $status = read_status();
    if ($status['status'] != 'started') {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "Game is not in action."]);
        exit;
    }
    if ($status['p_turn'] != $input['token']) {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "It is not your turn."]);
        exit;
    }
    if (check_empty_square($input['x'], $input['y'])) {
        do_place_piece($input);
    } else {
        header("HTTP/1.1 400 Bad Request");
        print json_encode(['errormesg' => "You cant place your piece here."]);
    }
    exit;
}

/**
 * check if board square is empty
 * square can contain either null or piece_id
 * @param int $x horisontal axis
 * @param int $y vertical axis
 * @return boolean 
 */

function check_empty_square($x, $y)
{
    global $mysqli;
    $sql = 'select count(piece) as c  from board where x=? and y=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('ii', $x, $y);
    $st->execute();
    $res = $st->get_result();
    $count = $res->fetch_assoc();
    if ($count['c'] > 0) {
        return false;
    } else {
        return true;
    }
}

/**
 * picks the piece for other player to place
 * @param int $piece_id contains the id of the piece
 * id is [1-16] 
 * calls curent_selected_piece
 * calls change_role
 */

function do_place_piece($input)
{
    //$piece_id = curent_selected_piece();
    $piece_id = $input['piece_id'];
    global $mysqli;
    $sql = 'call `place_piece`(?,?,?);';
    $st = $mysqli->prepare($sql);
    $st->bind_param('iii', $input['x'], $input['y'], $piece_id);
    $st->execute();
    if (check_win($input['x'], $input['y'])) {
        global $mysqli;
        $sql1 = 'UPDATE `game_status` SET `result`="W"';
        $st1 = $mysqli->prepare($sql1);
        $st1->execute();
    } else {
        change_role_to_pick($input['token']);
        header('Content-type: application/json');
        print json_encode(read_board(), JSON_PRETTY_PRINT);
    }
}

/**
 * finds current selected piece from game status table
 * @return int $res is piece_id
 */

// function curent_selected_piece()
// {

//     global $mysqli;
//     $sql = 'select current_piece from game_status ';
//     $st = $mysqli->prepare($sql);
//     $st->execute();
//     $res = $st->get_result();
//     $res->fetch_row(MYSQLI_ASSOC);
//     return $res[0];
// }

/**
 * sets user role to pick
 * @param string $token user unique identifier
 */

function change_role_to_pick($token)
{
    global $mysqli;
    $sql = 'UPDATE players SET `role`="pick" WHERE token=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('s', $token);
    $st->execute();
}

/**
 * finds the next player and stores the information to game status table
 * @param string $token user unique identifier
 */

function next_player($token)
{
    global $mysqli;
    $sql = 'SELECT token from players  where token!=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('s', $token);
    $st->execute();
    $res = $st->get_result();
    $token = $res->fetch_assoc();

    $sql = 'UPDATE game_status SET p_turn=?';
    $st = $mysqli->prepare($sql);
    $st->bind_param('s', $token['token']);
    $st->execute();
}

/**
 * checks if curent placment of a piece wins the game
 * @param string $x    
 * @param string $y
 * if the pieces on a [row/coloum/diagonal]  much one of the arrays included in the $attr_array
 * that means that the piece have at least one common attribute and therefore means that current
 * placement wins the game
 *
 *cals horisontal_pieces , vertical_pieces , check_left_diagonal_pieces , check_right_diagonal_pieces
 */

function check_win($x, $y)
{
    $attr_array = array(
        array(1, 2, 3, 4, 5, 6, 7, 8),
        array(9, 10, 11, 12, 13, 14, 15, 16),
        array(5, 6, 7, 8, 13, 14, 15, 16),
        array(1, 2, 3, 4, 9, 10, 11, 12),
        array(2, 4, 6, 8, 10, 12, 14, 16),
        array(1, 3, 5, 7, 9, 11, 13, 15),
        array(3, 4, 7, 8, 11, 12, 15, 16),
        array(1, 2, 5, 6, 9, 10, 13, 14)
    );

    $hp = horisontal_pieces($y);
    $vp = vertical_pieces($x);

    if ($x == $y) {
        $ldp = check_left_diagonal_pieces();
        $possible_win_line = array($hp, $vp, $ldp);
    } elseif ($x + $y == 5) {
        $rdp = check_right_diagonal_pieces();
        $possible_win_line = array($hp, $vp, $rdp);
    } else {
        $possible_win_line = array($hp, $vp);
    }

    for ($i = 0; $i < count($possible_win_line); $i++) {
        for ($j = 0; $j < count($attr_array); $j++) {
            if (count(array_intersect($possible_win_line[$i], $attr_array[$j])) == 4) {
                return true;
            }
        }
    }
    return false;
}

/**
 * reads all pieces on the horisontal(x) axis
 * @param string $x    
 * @param string $y
 * @return array $res
 * 
 */

function horisontal_pieces($y)
{

    $result = array();
    for ($i = 1; $i <= 4; $i++) {
        global $mysqli;
        $sql = 'select piece from board where x=? and y=?';
        $st = $mysqli->prepare($sql);
        $st->bind_param('ii', $i, $y);
        $st->execute();
        $res = $st->get_result();
        $res1 = $res->fetch_assoc();
        array_push($result, $res1['piece']);
    }
    return $result;
}

/**
 * reads all pieces on the vertical(y) axis
 * @param string $x    
 * @param string $y
 * @return array $res
 * 
 */

function vertical_pieces($x)
{
    $result = array();
    for ($i = 1; $i <= 4; $i++) {
        global $mysqli;
        $sql = 'select piece from board where x=? and y=?';
        $st = $mysqli->prepare($sql);
        $st->bind_param('ii', $x, $i);
        $st->execute();
        $res = $st->get_result();
        $res1 = $res->fetch_assoc();
        array_push($result, $res1['piece']);
    }
    return $result;
}

/**
 * reads all pieces on the left diagonal 
 * @param string $x    
 * @param string $y
 * @return array $res
 * 
 */


function check_left_diagonal_pieces()
{
    $result = array();
    for ($i = 1; $i <= 4; $i++) {
        for ($j = 1; $j <= 4; $j++) {
            if ($i == $j) {
                global $mysqli;
                $sql = 'select piece from board where x=? and y=?';
                $st = $mysqli->prepare($sql);
                $st->bind_param('ii', $i, $j);
                $st->execute();
                $res = $st->get_result();
                if ($res) {
                    $res1 = $res->fetch_assoc();
                    array_push($result, $res1['piece']);
                } else {
                    array_push($result, 0);
                }
            }
        }
    }
    return $result;
}

/**
 * reads all pieces on the right diagonal 
 * @param string $x    
 * @param string $y
 * @return array $res
 * 
 */

function check_right_diagonal_pieces()
{
    $result = array();
    for ($i = 1; $i <= 4; $i++) {
        for ($j = 1; $j <= 4; $j++) {
            if ($i + $j == 5) {
                global $mysqli;
                $sql = 'select piece from board where x=? and y=?';
                $st = $mysqli->prepare($sql);
                $st->bind_param('ii', $i, $j);
                $st->execute();
                $res = $st->get_result();
                if ($res) {
                    $res1 = $res->fetch_assoc();
                    array_push($result, $res1['piece']);
                } else {
                    array_push($result, 0);
                }
            }
        }
    }
    return $result;
}
?>