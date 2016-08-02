<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes
 * PATH: Third_Type_Tapes/core/
 * NAME: core.php
 */

session_start();
try{
    $db = new PDO("mysql:host=localhost;dbname=thirdtypetapes", "ttt", "A/B/G/G/D/", array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
}
catch(Exception $e){
    die('Error : '.$e->getMessage());
}

require("core/model.php");

