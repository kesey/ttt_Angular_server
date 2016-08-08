<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes
 * PATH: Third_Type_Tapes/model/
 * NAME: exemplaire.php
 */

class Exemplaire extends Model{
    public $id;
    var $table = "exemplaire";
    
    //utile pour ne pas prendre en compte les lignes archivées
    var $notArchive = "1 = 1";
    
   /**
    *  vérifie la/les donnée(s) passée(s) en argument
    *  @param array $data donnée(s) à vérifier
    **/
    public function verifications($data) {
        $isOk = TRUE;
        $i = 1;
        foreach($data as $key => $value){           
            if(!empty($data[$key]['prix_vente_euros'])){
                if(!$this->isPosDec($data[$key]['prix_vente_euros'])){
                    $_SESSION["info"] = "le prix de l'exemplaire numéro ".$i." est invalide";
                    $isOk = FALSE;
                }
            }
            if(!empty($data[$key]['montant_frais_de_port'])){
                if(!$this->isPosDec($data[$key]['montant_frais_de_port'])){
                    $_SESSION["info"] = "le montant des frais de port de l'exemplaire numéro ".$i." est invalide";
                    $isOk = FALSE;
                }
            }
            if(!empty($data[$key]['date_vente'])){
                if(!$this->isDateFr($data[$key]['date_vente'])){
                    $_SESSION["info"] = "la date de l'exemplaire numéro ".$i." est invalide";
                    $isOk = FALSE;
                }
            }
            $i++;
        }
        return $isOk;    
    }
}
