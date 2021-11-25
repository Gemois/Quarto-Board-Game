<?php

require_once "lib/dbconnect.php";
require_once "lib/users.php";
require_once "lib/board.php";
require_once "lib/status.php";

/**
 * stores http request method  to local variable
 * String handling to get the path contents as a table without white space in front and back 
 * store http request body raw data using json format to local variable
 */

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);

if(isset($_SERVER['HTTP_X_TOKEN'])) {
	$input['token']=$_SERVER['HTTP_X_TOKEN'];
}

/**
 * The following switch controls and redirects the proper actions to be made as specified with the http request 
 * using $request can identify the path levels  
 * using $method can identify the type of the action to be made at the specific path level 
 * using $input can read and pass as parameters raw data from the http request body 
 */

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
    case 'status': if(sizeof($request)==0){
                        show_status();
                   }
		   else{
                   header("HTTP/1.1 404 Not Found");
                   }
		break;
    case 'players': handle_player($method, $request,$input);
                break;

    default:  header("HTTP/1.1 404 Not Found");
                exit;
}


/**
 * selects the appropriate board action(function call ) acording http request  
 * @param string $method can be either GET or POST 
 * @param json $input  body of http request
 * 
 */

function handle_board($method,$input) {
    if($method=='GET') {
            show_board($input);
    } else if ($method=='POST') {
            reset_board();
            show_board($input);
    }
    
}

/**
 * selects the appropriate piece action(function call ) acording http request  
 * @param string $method can be either GET or PUT
 * @param json $input  body of http request
 * 
 */

function handle_piece($method,$input) {
  switch ($b=array_shift($request)) {
    case pick: if($method=='PUT'){
                    pick_piece($input['piece_id']);
               }
               else{
                      piece_list();
               }
                break;
    case place: if($method=='GET') {
                    show_piece($input['x'],$input['y']);
                }
                else if ($method=='PUT') {
		                place_piece($input['x'],$input['y'],$input['token']);
                } 
                break;
  }
}

/**
 * selects the appropriate player action(function call ) acording http request  
 * @param array $request contains the http request path as a table with its level on a cell 
 * @param string $method can be either GET or PUT
 * @param json $input  body of http request
 * 
 */

function handle_player($request,$method,$input) {
	switch ($b=array_shift($request)) {
		case '':
		case null: if($method=='GET'){
                                show_users($method);
                           }
		           else{
                                header("HTTP/1.1 400 Bad Request"); 
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