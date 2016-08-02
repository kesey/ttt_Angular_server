<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes_2_server
 * PATH: Third_Type_Tapes_2_server/
 * NAME: index.php
 */
 
header("Content-Type:application/json");
header("Access-Control-Allow-Origin: *");
define('WEBROOT', str_replace('index.php','',$_SERVER['SCRIPT_NAME']));
define('ROOT', str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']));
/* define('MAX_IMG_SIZE', 2097152);
define('MAX_RAR_SIZE', 524288000);
define('MAX_STR_LEN', 40);
define('NBRE_EX', 75); */

require_once(ROOT."core/core.php");
require_once(ROOT."functions.php"); 

/* if(isset($_SESSION['info'])){
    unset($_SESSION['info']);
}
if(isset($_SESSION['infoSave'])){
    unset($_SESSION['infoSave']);
}
if(isset($_SESSION['infoLog'])){
    unset($_SESSION['infoLog']);
} */

if(isset($_GET['p']) && !empty($_GET['p'])){
    $name = htmlspecialchars($_GET['p']);
	$price = get_price($name);
	
	if(empty($price)) {
		deliver_response(200, "$name book not found", NULL);
	} else {
		deliver_response(200, "$name book found", $price);
	}
} else {
    deliver_response(400, "Invalid Request", NULL);
}

function deliver_response($status, $status_message, $data)
{
	header("HTTP/1.1 $status $status_message");
	
	$response['status'] = $status;
	$response['status_message'] = $status_message;
	$response['data'] = $data;
	$json_response = json_encode($response);
	echo $json_response;
}

/* $parametres = explode('/', $par);

if(!$parametres[0]){
    //quand on arrive sur la page la première fois
    $parametres[0] = 'cassettes';
}

//vérification de l'existence du controleur
$tabFiles = scandir(ROOT.'controller');
foreach ($tabFiles as $key => $value){
    if($value == '.' || $value == '..'){        
        unset($tabFiles[$key]);
    }
}
$tabControllers = str_replace('.php','',$tabFiles);
if(in_array($parametres[0], $tabControllers)){
    $controller = $parametres[0];
    
    $action = isset($parametres[1]) ? $parametres[1] : 'index';//action par défaut

    require(ROOT.'controller/'.strtolower($controller).'.php');
    $controller = new $controller();
    
    if(!empty($_POST)){
        $actPost = isset($_POST['action']) ? $_POST['action'] : "";
        if(method_exists($controller, $actPost)){            
            $controller->$actPost();
        }
    }

    if(method_exists($controller, $action)){
        array_splice($parametres, 3);   
        unset($parametres[0]);
        unset($parametres[1]);    
        call_user_func_array(array($controller, $action), $parametres);
    } else {
        require(ROOT."view/erreur404.php");
    }
} else {
    require(ROOT."view/erreur404.php");
} */