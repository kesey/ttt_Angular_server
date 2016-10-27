<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes
 * PATH: Third_Type_Tapes/model/
 * NAME: client.php
 */

class Client extends Model
{
    public $id;
    var $table = "client";
    
    // utile pour ne pas prendre en compte les lignes archivées
    var $notArchive = "suppr != 1";

   /**
    *  vérifie la/les donnée(s) passée(s) en argument
    *  @param array $data donnée(s) à vérifier
    **/
    public function verifications($data)
    {
        $isOk = true;
        if (empty($data["nom_client"])) {
            $_SESSION["info"] = "Veuillez renseigner un nom";
            $isOk = false;
        } elseif (empty($data["id_client"])) {
            if ($this->exist('nom_client', $data["nom_client"])) {
                $_SESSION["info"] = "Ce client existe déjà";
                $isOk = false;
            }
        }
        if (!empty($data["mail_client"])) {
            if (!$this->isEmail($data["mail_client"])) {
                $_SESSION["info"] = "L'adresse mail n'est pas valide";
                $isOk = false;
            }
            if (empty($data["id_client"])) {
                if ($this->exist('mail_client', $data["mail_client"])) {
                    $_SESSION["info"] = "Cette adresse mail existe déjà";
                    $isOk = false;
                }
            }
        }
        return $isOk;
    }
}



