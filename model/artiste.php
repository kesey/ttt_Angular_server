<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes
 * PATH: Third_Type_Tapes/model/
 * NAME: artiste.php
 */

class Artiste extends Model
{
    public $id;
    var $table = "artiste";
    
    //utile pour ne pas prendre en compte les lignes archivées
    var $notArchive = "suppr != 1";
    
   /**
    *  récupération infos cassette(s) et artiste(s)
    *  @param array $data contient les conditions, le group by, l'ordre et la limitation
    **/  
    public function getAllInfos($data = array()) {
        global $db;
        $conditions = "1 = 1";
        if(isset($data['id'])){
            $id = $this->securite_bdd($data['id']);
            $conditions = $this->table.".id_".$this->table." = :id";
        } else if(isset($data['conditions'])){
            $conditions = $this->securite_bdd($data['conditions']);
        }
        $group = "";
        if(isset($data['groupBy'])){
            $group = $this->securite_bdd($data['groupBy']);
            $group = " GROUP BY ".$this->table.".".$group;
        }
        $order = " ORDER BY ".$this->table.".id_artiste DESC";
        if(isset($data['order'])){
            $order = $this->securite_bdd($data['order']);
            $order = " ORDER BY ".$this->table.".".$order;
        }
        $limit = "";
        if(isset($data['limit'])){
            $limit = $this->securite_bdd($data['limit']);
            $limit = " LIMIT ".$limit;
        }
        $sql = "SELECT * FROM ".$this->table." INNER JOIN produire ON ".$this->table.".id_".$this->table." = produire.id_".$this->table." INNER JOIN cassette ON produire.id_cassette = cassette.id_cassette WHERE ".$this->table.".".$this->notArchive." AND cassette.".$this->notArchive." AND ".$conditions.$group.$order.$limit;
        $pdoObj = $db->prepare($sql);
        if(isset($id)){
            $pdoObj->bindParam(':id', $id, PDO::PARAM_INT);
        }
        $success = $pdoObj->execute();
        if($success){
            $tabFind = array();
            while ($infos = $pdoObj->fetch()){
                $tabFind[] = $infos;
            }
            $pdoObj->closeCursor();           
            return $this->securiteHtml($tabFind);
        } else {
            return FALSE;
        }
    }
    
    /**
    *  vérifie la/les donnée(s) passée(s) en argument
    *  @param array $data donnée(s) à vérifier
    *  @param array $fichier fichier à controler
    **/
    public function verifications($data, $fichier) {
        $isOk = TRUE;
        if(empty($data["nom"])){
            $_SESSION["info"] = "Veuillez renseigner un nom";
            $isOk = FALSE;
        } else if(empty($data['id_artiste'])){
            if($this->exist('nom', $data["nom"])){
                $_SESSION["info"] = "Cet artiste existe déjà";
                $isOk = FALSE;
            }
        }
        if(empty($data["lien_artiste"])){            
            $_SESSION["info"] = "Veuillez renseigner un lien";
            $isOk = FALSE;
        } else if(!$this->isValidUrl($data["lien_artiste"])){
            $_SESSION["info"] = "L'adresse du lien est invalide";
            $isOk = FALSE;
        }
        if(empty($data["bio"])){
            $_SESSION["info"] = "Veuillez renseigner une bio";
            $isOk = FALSE;
        }
        if(!isset($data['image_artiste']) && empty($fichier['name'])){
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
                $_SESSION["info"] = "l'imge est trop lourde";
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

