<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes
 * PATH: Third_Type_Tapes/model/
 * NAME: produire.php
 */

class Produire extends Model
{
    public $id;
    var $table = "produire";
    
   /**
    *  efface une/des ligne(s) dans la table produire
    *  @param string|int $idArtiste contient l'id_artiste 
    */
    public function deleteProd($idArtiste = null)
    {
        global $db;
        $conditions = "1 = 0";
        if ($idArtiste) {
            $idArt = $this->securite_bdd($idArtiste);
            $conditions = "id_artiste = :idArtiste";
        }
        $sql = "DELETE FROM ".$this->table." WHERE ".$conditions;
        $pdoObj = $db->prepare($sql);
        $pdoObj->bindParam(':idArtiste', $idArt, PDO::PARAM_INT);
        return $pdoObj->execute();
    }
    
   /**
    *  vérifie que la/les production(s) d'un artiste a/ont bien été renseignée(s)
    *  @param array $data tableau contenant la/les production(s) d'un artiste 
    */
    public function verifProd($data = null)
    {
        if (!$data[0]) {
            $_SESSION["info"] = "Veuillez renseigner la/les production(s) de cet artiste";
            return false;
        } else {            
            return true;
        }         
    }
}
