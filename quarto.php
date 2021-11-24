<?php


require_once "lib/dbconnect.php";
require_once "lib/users.php";
require_once "lib/board.php";
require_once "lib/status.php";

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

  switch ($b=array_shift($request)) {
      
    case pick: if($method=='PUT'){
                    pick_piece($input['piece_id']);
              }else{
                      piece_list();
               }
                break;

    case place: if($method=='GET') {
                    show_piece($input['x'],$input['y']);
               }else if ($method=='PUT') {
		                place_piece($input['x'],$input['y'],$input['token']);
               } 
              break;
  }
}





function handle_player($method, $request,$input) {
	switch ($b=array_shift($request)) {
		case '':
		case null: if($method=='GET') {show_users($method);}
				   else {header("HTTP/1.1 400 Bad Request"); 
            print json_encode(['errormesg'=>"Method $method not allowed here."]);    
            }
                    break;
  
    case login:  handle_user($method,$input);
                  break; 
    default:  header("HTTP/1.1 404 Not Found");
				      print json_encode(['errormesg'=>"Player $b not found."]);
                 break;
	}
}
 


?>