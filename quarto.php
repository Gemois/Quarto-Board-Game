<?php


require_once "lib/dbconnect.php";

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);

if(isset($_SERVER['HTTP_X_TOKEN'])) {
	$input['token']=$_SERVER['HTTP_X_TOKEN'];
}




switch ($r=array_shift($request)) {

    case 'board' : 
            switch ($b=array_shift($request)) {
                case '':
                case null: handle_board($method,$input);
                            break;
                case 'piece': handle_piece($method, $request[0],$input);
                            break;
                default: header("HTTP/1.1 404 Not Found");
                            break;
			}
			break;

    case 'status': 
			if(sizeof($request)==0) {show_status();}
			else {header("HTTP/1.1 404 Not Found");}
			break;

	case 'players': handle_player($method, $request,$input);
            break;

    default:  header("HTTP/1.1 404 Not Found");
                        exit;
}




function handle_board($method,$input) {
 
    if($method=='GET') {
            show_board($input);
    } else if ($method=='POST') {
            reset_board();
            show_board($input);
    }
    
}




function handle_piece($method,$piece_id,$input) {
	if($method=='GET') {
        show_piece($piece_id);
    } else if ($method=='PUT') {
		place_piece($piece_id,$input['x'],$input['y'],$input['token']);
    }    
}




/*
Alternative : without piece presentation only the placement
[in case i use only select menu input at the gui ]


function handle_piece($method,$input) {
	if($method=='GET') {
        header("HTTP/1.1 404 Not Found");
    } else if ($method=='PUT') {
		place_piece($input['piece_id'],$input['x'],$input['y'],$input['token']);    
    }    
}

!! note : in this scenario i can enclose piece_id in the post body
and remove it from the handle_player args in the switch . 
*/




function handle_player($method, $request,$input) {

}



?>