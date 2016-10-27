<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes_2_server
 * PATH: Third_Type_Tapes_2_server/model/
 * NAME: cassette.php
 */

class Cassette extends Model
{
    public $id;
    var $table = "cassette";
	var $models = array('frais_de_port');
    
    //utile pour ne pas prendre en compte les lignes archivées
    var $notArchive = "suppr != 1";

    /**
     *  récupération infos cassette et artiste(s)
     *  @param array $data contient les champs, les conditions, le group by, l'ordre et la limitation
     **/
    public function getAllInfos($data = array())
    {
        global $db;
        $fields = "*";
        $conditions = "1 = 1";
        if (isset($data["fields"])) {
            $fields = $this->securite_bdd($data['fields']);
        }
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
        $order = " ORDER BY ".$this->table.".date_sortie DESC";
        if (isset($data['order'])) {
            $order = $this->securite_bdd($data['order']);
            $order = " ORDER BY ".$this->table.".".$order;
        }
        $limit = "";
        if (isset($data['limit'])) {
            $limit = $this->securite_bdd($data['limit']);
            $limit = " LIMIT ".$limit;
        }
        $sql = "SELECT ".$fields." FROM ".$this->table." INNER JOIN produire ON ".$this->table.".id_".$this->table." = produire.id_".$this->table." INNER JOIN artiste ON produire.id_artiste = artiste.id_artiste WHERE ".$this->table.".".$this->notArchive." AND artiste.".$this->notArchive." AND ".$conditions.$group.$order.$limit;
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
        $d['totalCassettes'] = $this->findAll(array('fields' => 'COUNT(*) as total'));
        $d['cassettes'] = $this->getAllInfos(array('groupBy' => "id_".$this->table,
                                                     'limit' => $limit));
        $d['artistes'] = $this->getAllInfos(array('fields' => "artiste.nom, ".$this->table.".id_".$this->table));
        $length = sizeof($d['cassettes']);
        for ($i = 0; $i < $length; $i++) {
            $imgResize = explode('.', $d['cassettes'][$i]['image_pochette']);
            $d['cassettes'][$i]['image_pochette_resize'] = $imgResize[0].'-resize.'.$imgResize[1];
        }
        return $d;
    }
	
	/**
    *  affiche les détails d'un élément particulier
    *  @param int|string $id l'id de l'élément dont on souhaite visualiser les détails
    */
    public function view($id)
    {
		$model = $this->models[0];
        if ($this->exist('id_'.$this->table,$id)) {
            $d['cassette'] = $this->getAllInfos(array('id' => $id));            
            $d['shipInfos'] = $this->$model->findAll();
            $d['date']['min'] = $this->getDataMaxMin("date_sortie", "MIN")["min"];
            $d['date']['max'] = $this->getDataMaxMin("date_sortie", "MAX")["max"];
            if ($d['cassette'][0]['date_sortie'] > $d['date']['min']) {
                $d['cassPrev'] = $this->getAllInfos(array("conditions" => $this->table.".date_sortie <= '".$d['cassette'][0]['date_sortie']."' AND ".$this->table.".id_".$this->table." != ".$id,
															   "order" => "date_sortie DESC, ".$this->table.".id_".$this->table." DESC",
															   "limit" => 1));
                $d['cassPrev'] = $d['cassPrev'][0];
            }
            if ($d['cassette'][0]['date_sortie'] < $d['date']['max']) {
                $d['cassNext'] = $this->getAllInfos(array("conditions" => $this->table.".date_sortie >= '".$d['cassette'][0]['date_sortie']."' AND ".$this->table.".id_".$this->table." != ".$id,
															   "order" => "date_sortie ASC, ".$this->table.".id_".$this->table." ASC",
															   "limit" => 1));                
                $d['cassNext'] = $d['cassNext'][0];
            }
            return $d;
        } else {
            return false;
        }
    }
	
	// lance le telechargement d'un fichier 
    public function download()
    {
        $this->telecharger_fichier($this->data['nomFichier']);
    }

   /**
    *  vérifie la/les donnée(s) passée(s) en argument
    *  @param array $data donnée(s) à vérifier
    *  @param array $fichier fichier à controler
    **/
    public function verifications($data, $fichier)
    {
        $isOk = true;
        if (empty($data["titre"])) {
            $_SESSION["info"] = "Veuillez renseigner un titre";
            $isOk = false;
        } elseif (empty($data['id_cassette'])) {
            if ($this->exist('titre', $data["titre"])) {
                $_SESSION["info"] = "Cette cassette existe déjà";
                $isOk = false;
            }
        }
        if (empty($data["date_sortie"])) {
            $_SESSION["info"] = "Veuillez renseigner une date";
            $isOk = false;
        } elseif (!$this->isDateFr($data["date_sortie"])) {
            $_SESSION["info"] = "La date est invalide";
            $isOk = false;
        }
        if (empty($data["code"])) {
            $_SESSION["info"] = "Veuillez renseigner le code (ref catalogue)";
            $isOk = false;
        } elseif (!$this->refCodeOk($data["code"])) {
            $_SESSION["info"] = "Le code (ref catalogue) est invalide";
            $isOk = false;
        } elseif (empty($data['id_cassette'])) {
            if ($this->exist('code', $data["code"])) {
                $_SESSION["info"] = "Cette référence existe déjà";
                $isOk = false;
            }
        }
        if (empty($data["longueur"])) {
            $_SESSION["info"] = "Veuillez renseigner une longueur";
            $isOk = false;
        } elseif (!$this->longueurOk($data["longueur"])) {
            $_SESSION["info"] = "Le format de la longueur est invalide";
            $isOk = false;
        }
        if (!empty($data["prix"])) {
            if (!$this->isPosDec($data["prix"])) {
                $_SESSION["info"] = "Le prix est invalide";
                $isOk = false;
            }
        }
        if (!empty($data["lien_soundcloud"])) {
            if (!$this->isValidUrl($data["lien_soundcloud"])) {
                $_SESSION["info"] = "Le lien soundcloud est invalide";
                $isOk = false;
            }
        }
        if (!empty($data["lien_youtube"])) {
            if (!$this->isValidUrl($data["lien_youtube"])) {
                $_SESSION["info"] = "Le lien youtube est invalide";
                $isOk = false;
            }
        }
        if (empty($data["description"])) {
            $_SESSION["info"] = "Veuillez renseigner une description";
            $isOk = false;
        }
        if (!isset($data['image_pochette']) && empty($fichier['name'])) {
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
                $_SESSION["info"] = "l'image est trop lourde";
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
    
    /**
    *  vérifie le fichier (.rar) passé en argument
    *  @param array $fichier fichier à vérifier
    **/
    public function verifRar($fichier)
    {
        $isOk = true;
        if (empty($fichier["name"])) {
            $isOk = false;
        } else {
            if ($fichier['error'] === 1 || $fichier['size'] > MAX_RAR_SIZE) {
                $_SESSION["info"] = "le fichier rar est trop lourd";
                $isOk = false;
            } elseif ($this->contSensChars($fichier["name"])) {
                $_SESSION["info"] = "le nom du fichier rar contient au moins un caractère sensible";
                $isOk = false;
            } elseif (strlen($fichier["name"]) > MAX_STR_LEN) {
                $_SESSION["info"] = "le nom du fichier rar est trop long";
                $isOk = false;
            } elseif (!$this->extRarOk($fichier["name"])) {
                $_SESSION["info"] = "l'extension valide pour le fichier est rar";
                $isOk = false;
            } elseif (!$this->isRar($fichier['type'])) {
                $_SESSION["info"] = "le fichier n'est pas un .rar";
                $isOk = false;
            }            
        }
        return $isOk;
    }
}
