<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes_2_server
 * PATH: Third_Type_Tapes_2_server/
 * NAME: index.php
 */

$headers = array(
	'Access-Control-Allow-Origin' => '*',
	'Access-Control-Allow-Methods' => 'POST, GET',
	'Access-Control-Max-Age' => '3600',
	'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept',
	'Content-Type' => 'application/json',
);

foreach ($headers as $headerType => $headerValue)
{
	header($headerType . ': ' . $headerValue);
}

define('WEBROOT', str_replace('index.php','',$_SERVER['SCRIPT_NAME']));
define('ROOT', str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']));
define('MAX_IMG_SIZE', 2097152);
define('MAX_RAR_SIZE', 524288000);
define('MAX_STR_LEN', 40);
define('NBRE_EX', 75);

require_once(ROOT."core/core.php"); 

/* if (isset($_SESSION['info'])) {
    unset($_SESSION['info']);
}
if (isset($_SESSION['infoSave'])) {
    unset($_SESSION['infoSave']);
}
if (isset($_SESSION['infoLog'])) {
    unset($_SESSION['infoLog']);
} */

function deliver_response($status, $status_message, $postData)
{
	$response['status'] = $status;
	$response['status_message'] = $status_message;
	$response['data'] = $postData;

	$json_response = json_encode($response);
	echo $json_response;

	exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') { // récupération du model (1er paramètre de l'url) pour une requête de type GET
	if (isset($_GET['p']) && !empty($_GET['p'])) {
		$par = htmlspecialchars($_GET['p']);
		$tabParam = explode('/', $par);
		$model = $tabParam[0];
	} else {
		deliver_response(400, "Invalid Request", "Invalid Model");
	}
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') { // récupération des données passées en POST et du model pour une requête de type POST
	$postData = json_decode(file_get_contents('php://input'), true);
	if (isset($postData['model']) && !empty($postData['model'])) {
		$model = $postData['model'];
		unset($postData['model']);
	} else {
		deliver_response(400, "Invalid Request", "Invalid Model");
	}
} else {
	deliver_response(400, "Invalid Request", "Request Method not available");
}

// création d'un tableau contenant tous les models
$tabFiles = scandir(ROOT.'model');
foreach ($tabFiles as $key => $value) {
	if ($value == '.' || $value == '..') {
		unset($tabFiles[$key]);
	}
}

$tabModel= str_replace('.php','',$tabFiles);

//vérification de l'existence du model
if (in_array($model, $tabModel)) {
	// instancie le model demandé et permet son utilisation sous forme d'objet
	require_once(ROOT.'/model/'.strtolower($model).'.php');
	$model = (isset($postData) && !empty($postData)) ? new $model($postData) : new $model(); // on appelle le constructeur du model avec les données passées en POST si elles existent

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$actPost = isset($postData['action']) ? $postData['action'] : ""; //action par défaut pour requête de type POST
		if (method_exists($model, $actPost)) {
			$responsePost = $model->$actPost();
			$json_response = json_encode($responsePost);
			echo $json_response;
		} else {
			deliver_response(400, "Invalid Request", $actPost . " is an Invalid Action");
		}
	} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
		$action = isset($tabParam[1]) ? $tabParam[1] : 'index'; //action par défaut pour requête de type GET

		if (method_exists($model, $action)) {
			array_splice($tabParam, 3);
			unset($tabParam[0], $tabParam[1]);

			$response = call_user_func_array(array($model, $action), $tabParam);

			if ($response) {
				$json_response = json_encode($response);
				echo $json_response;
			} else {
				deliver_response(400, "Invalid Request", $tabParam . "is an Invalid Param");
			}
		} else {
			deliver_response(400, "Invalid Request", $action . "is an Invalid Action");
		}
	}
} else {
	deliver_response(400, "Invalid Request", $model . " is an Invalid Model");
}
