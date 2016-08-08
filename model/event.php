<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes
 * PATH: Third_Type_Tapes/model/
 * NAME: event.php
 */

class Event extends Model{
    public $id;
    var $table = "event";
    
    //utile pour ne pas prendre en compte les lignes archivées
    var $notArchive = "suppr != 1";
    
   /**
    *  vérifie la/les donnée(s) passée(s) en argument
    *  @param array $data donnée(s) à vérifier
    *  @param array $fichier fichier à controler
    **/
    public function verifications($data, $fichier) {
        $isOk = TRUE;
        if(empty($data["titre_event"])){
            $_SESSION["info"] = "Veuillez renseigner un titre";
            $isOk = FALSE;
        } else if(empty($data['id_event'])){
            if($this->exist('titre_event', $data["titre_event"])){
                $_SESSION["info"] = "Ce titre existe déjà";
                $isOk = FALSE;
            }
        }
        if(empty($data["date_event"])){
            $_SESSION["info"] = "Veuillez renseigner une date";
            $isOk = FALSE;
        } else if(!$this->isDateFr($data["date_event"])){
            $_SESSION["info"] = "la date est invalide";
            $isOk = FALSE;
        }
        if(empty($data["description_event"])){
            $_SESSION["info"] = "Veuillez renseigner une description";
            $isOk = FALSE;
        }
        if(!isset($data['image_event']) && empty($fichier['name'])){
            $_SESSION["info"] = "Veuillez selectionner une image";
            $isOk = FALSE;
        }
        return $isOk;    
    }
    
   /**
    *  vérifie le fichier passé en argument
    *  @param array $fichier fichier à vérifier
    **/
    public function verifFile($fichier) {
        $isOk = TRUE;        
        if(empty($fichier["name"])){
            $isOk = FALSE;
        } else {
            if($fichier['error'] === 1 || $fichier['size'] > MAX_IMG_SIZE){
                $_SESSION["info"] = "l'image est trop lourde";
                $isOk = FALSE;
            } else if($this->contSensChars($fichier["name"])){
                $_SESSION["info"] = "le nom de l'image contient au moins un caractère sensible";
                $isOk = FALSE;
            } else if(strlen($fichier["name"]) > MAX_STR_LEN){
                $_SESSION["info"] = "le nom de l'image est trop long";
                $isOk = FALSE;
            } else if(!$this->extImgOk($fichier["name"])){
                $_SESSION["info"] = "les extensions valides pour l'image sont jpg, jpeg, png";
                $isOk = FALSE;           
            } else if(!$this->isImage($fichier['tmp_name'])){
                $_SESSION["info"] = "le fichier n'est pas une image";
                $isOk = FALSE;
            }             
        }
        return $isOk;    
    }
}

