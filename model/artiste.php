<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes_2_server
 * PATH: Third_Type_Tapes_2_server/model/
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
    public function getAllInfos($data = array())
    {
        global $db;
        $conditions = "1 = 1";
        if (isset($data['id'])) {
            $id = $this->securite_bdd($data['id']);
            $conditions = $this->table.".id_".$this->table." = :id";
        } elseif (isset($data['conditions'])) {
            $conditions = $this->securite_bdd($data['conditions']);
        }
        $group = "";
        if (isset($data['groupBy'])) {
            $group = $this->securite_bdd($data['groupBy']);
            $group = " GROUP BY ".$this->table.".".$group;
        }
        $order = " ORDER BY ".$this->table.".id_artiste DESC";
        if (isset($data['order'])) {
            $order = $this->securite_bdd($data['order']);
            $order = " ORDER BY ".$this->table.".".$order;
        }
        $limit = "";
        if (isset($data['limit'])) {
            $limit = $this->securite_bdd($data['limit']);
            $limit = " LIMIT ".$limit;
        }
        $sql = "SELECT * FROM ".$this->table." INNER JOIN produire ON ".$this->table.".id_".$this->table." = produire.id_".$this->table." INNER JOIN cassette ON produire.id_cassette = cassette.id_cassette WHERE ".$this->table.".".$this->notArchive." AND cassette.".$this->notArchive." AND ".$conditions.$group.$order.$limit;
        $pdoObj = $db->prepare($sql);
        if (isset($id)) {
            $pdoObj->bindParam(':id', $id, PDO::PARAM_INT);
        }
        $success = $pdoObj->execute();
        if ($success) {
            $tabFind = array();
            while ($infos = $pdoObj->fetch()) {
                $tabFind[] = $infos;
            }
            $pdoObj->closeCursor();           
            return $this->securiteHtml($tabFind);
        } else {
            return false;
        }
    }

    /**
     *  affiche le nombre d'éléments définit par le paramètre
     *  @param string $limit restreint les résultats retournés
     */
    public function index($limit = "0, 10")
    {
        $d['totalArtistes'] = $this->findAll(array('fields' => 'COUNT(*) as total'));
        $d['artistes'] = $this->getAllInfos(array('groupBy' => "id_".$this->table,
                                                    'limit' => $limit));
        $length = sizeof($d['artistes']);
        for ($i = 0; $i < $length; $i++) {
            $imgResize = explode('.', $d['artistes'][$i]['image_artiste']);
            $d['artistes'][$i]['image_artiste_resize'] = $imgResize[0].'-resize.'.$imgResize[1];
        }
        return $d;
    }

    /**
     *  affiche les détails d'un élément particulier
     *  @param int|string $id l'id de l'élément dont on souhaite visualiser les détails
     */
    public function view($id)
    {
        if ($this->exist('id_'.$this->table,$id)) {
            $d['artiste'] = $this->getAllInfos(array('id' => $id));
            $d['id']['min'] = $this->getDataMaxMin("id_artiste", "MIN")["min"];
            $d['id']['max'] = $this->getDataMaxMin("id_artiste", "MAX")["max"];
            if ($id > $d['id']['min']) {
                $d['artPrev'] = $this->getAllInfos(array("conditions" => $this->table.".id_artiste < ".$d['artiste'][0]['id_artiste'],
                                                              "order" => "id_artiste DESC",
                                                              "limit" => 1));
                $d['artPrev'] = $d['artPrev'][0];
            }
            if ($id < $d['id']['max']) {
                $d['artNext'] = $this->getAllInfos(array("conditions" => $this->table.".id_artiste > ".$d['artiste'][0]['id_artiste'],
                                                              "order" => "id_artiste ASC",
                                                              "limit" => 1));
                $d['artNext'] = $d['artNext'][0];
            }
            return $d;
        } else {
            return false;
        }
    }
    
    /**
    *  vérifie la/les donnée(s) passée(s) en argument
    *  @param array $data donnée(s) à vérifier
    *  @param array $fichier fichier à controler
    **/
    public function verifications($data, $fichier)
    {
        $isOk = true;
        if (empty($data["nom"])) {
            $_SESSION["info"] = "Veuillez renseigner un nom";
            $isOk = false;
        } elseif (empty($data['id_artiste'])) {
            if ($this->exist('nom', $data["nom"])) {
                $_SESSION["info"] = "Cet artiste existe déjà";
                $isOk = false;
            }
        }
        if (empty($data["lien_artiste"])) {
            $_SESSION["info"] = "Veuillez renseigner un lien";
            $isOk = false;
        } elseif (!$this->isValidUrl($data["lien_artiste"])) {
            $_SESSION["info"] = "L'adresse du lien est invalide";
            $isOk = false;
        }
        if (empty($data["bio"])) {
            $_SESSION["info"] = "Veuillez renseigner une bio";
            $isOk = false;
        }
        if (!isset($data['image_artiste']) && empty($fichier['name'])) {
            $_SESSION["info"] = "Veuillez selectionner une image";
            $isOk = false;
        }
        return $isOk;    
    }
    
    /**
    *  vérifie le fichier passé en argument
    *  @param array $fichier fichier à vérifier
    **/
    public function verifFile($fichier)
    {
        $isOk = true;
        if (empty($fichier["name"])) {
            $isOk = false;
        } else {
            if ($fichier['error'] === 1 || $fichier['size'] > MAX_IMG_SIZE) {
                $_SESSION["info"] = "l'imge est trop lourde";
                $isOk = false;
            } elseif ($this->contSensChars($fichier["name"])) {
                $_SESSION["info"] = "le nom de l'image contient au moins un caractère sensible";
                $isOk = false;
            } elseif (strlen($fichier["name"]) > MAX_STR_LEN) {
                $_SESSION["info"] = "le nom de l'image est trop long";
                $isOk = false;
            } elseif (!$this->extImgOk($fichier["name"])) {
                $_SESSION["info"] = "les extensions valides pour l'image sont jpg, jpeg, png";
                $isOk = false;
            } elseif (!$this->isImage($fichier['tmp_name'])) {
                $_SESSION["info"] = "le fichier n'est pas une image";
                $isOk = false;
            }            
        }
        return $isOk;    
    }
}

