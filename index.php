<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes_2_server
 * PATH: Third_Type_Tapes_2_server/
 * NAME: index.php
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type:application/json");

define('WEBROOT', str_replace('index.php','',$_SERVER['SCRIPT_NAME']));
define('ROOT', str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']));
define('MAX_IMG_SIZE', 2097152);
define('MAX_RAR_SIZE', 524288000);
define('MAX_STR_LEN', 40);
define('NBRE_EX', 75);

require_once(ROOT."core/core.php"); 

/* if(isset($_SESSION['info'])){
    unset($_SESSION['info']);
}
if(isset($_SESSION['infoSave'])){
    unset($_SESSION['infoSave']);
}
if(isset($_SESSION['infoLog'])){
    unset($_SESSION['infoLog']);
} */

function deliver_response($status, $status_message, $data)
{
	header("HTTP/1.1 $status $status_message");

	$response['status'] = $status;
	$response['status_message'] = $status_message;
	$response['data'] = $data;
	$json_response = json_encode($response);
	echo $json_response;
}

if(isset($_GET['p']) && !empty($_GET['p'])){

    $par = htmlspecialchars($_GET['p']);
	$tabParam = explode('/', $par);
	$model = $tabParam[0];
	
	//vérification de l'existence du model
	$tabFiles = scandir(ROOT.'model');
	foreach ($tabFiles as $key => $value){
		if($value == '.' || $value == '..'){        
			unset($tabFiles[$key]);
		}
	}

	$tabModel= str_replace('.php','',$tabFiles);

	if(in_array($model, $tabModel)){
		// instancie le model demandé et permet son utilisation sous forme d'objet  
		require_once(ROOT.'/model/'.strtolower($model).'.php');
		$model = new $model();
		
		$action = isset($tabParam[1]) ? $tabParam[1] : 'index'; //action par défaut
		
		if(method_exists($model, $action)){
            array_splice($tabParam, 3);
			unset($tabParam[0], $tabParam[1]);

			$response = call_user_func_array(array($model, $action), $tabParam);

			if($response){
				$json_response = json_encode($response);
				echo $json_response;
			} else {
				deliver_response(400, "Invalid Request", "Invalid Param");
			}
		} else {
			deliver_response(400, "Invalid Request", "Invalid Action");
		}
	} else {
		deliver_response(400, "Invalid Request", "Invalid Object");
	}
} else {
    deliver_response(301, "Moved Permanently", "Invalid Request");
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
