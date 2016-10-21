<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes_2_server
 * PATH: Third_Type_Tapes_2_server/model/
 * NAME: event.php
 */

class Event extends Model
{
    public $id;
    var $table = "event";
    
    //utile pour ne pas prendre en compte les lignes archivées
    var $notArchive = "suppr != 1";

    /**
     *  affiche le nombre d'éléments définit par le paramètre
     *  @param string $limit restreint les résultats retournés
     */
    public function index($limit = "0, 10")
    {
        $d['totalEvents'] = $this->findAll(array('fields' => 'COUNT(*) as total'));
        $d['events'] = $this->findAll(array("order" => "date_event DESC",
                                            'limit' => $limit));
        foreach ($d['events'] as $key => $event){
            $d['events'][$key]['date_event'] = $this->dateFr($event['date_event']);
            $imgResize = explode('.', $d['events'][$key]['image_event']);
            $d['events'][$key]['image_event_resize'] = $imgResize[0].'-resize.'.$imgResize[1];
        }
        return $d;
    }

    /**
     *  affiche les détails d'un élément particulier
     *  @param int|string $id l'id de l'élément dont on souhaite visualiser les détails
     */
    public function view($id)
    {
        if($this->exist('id_'.$this->table,$id)){
            $d['event'] = $this->findAll(array("conditions" => "id_".$this->table." = '".$id."'"));
            $d['event'] = $d['event'][0];
            $d['event']['date_event_fr'] = $this->dateFr($d['event']['date_event']);
            $d['event']['lieu'] = $this->adresseGMaps($d['event']['lieu']);
            $d['date']['min'] = $this->getDataMaxMin("date_event", "MIN")["min"];
            $d['date']['max'] = $this->getDataMaxMin("date_event", "MAX")["max"];
            if($d['event']['date_event'] > $d['date']['min']){
                $d['eventPrev'] = $this->findAll(array("conditions" => "date_".$this->table." <= '".$d['event']['date_event']."' AND id_".$this->table." != ".$id,
                    "order" => "date_".$this->table." DESC, id_".$this->table." DESC",
                    "limit" => 1));
                $d['eventPrev'] = $d['eventPrev'][0];
                $d['eventPrev']['date_event_fr'] = $this->dateFr($d['eventPrev']['date_event']);
            }
            if($d['event']['date_event'] < $d['date']['max']){
                $d['eventNext'] = $this->findAll(array("conditions" => "date_".$this->table." >= '".$d['event']['date_event']."' AND id_".$this->table." != ".$id,
                    "order" => "date_".$this->table." ASC, id_".$this->table." ASC",
                    "limit" => 1));
                $d['eventNext'] = $d['eventNext'][0];
                $d['eventNext']['date_event_fr'] = $this->dateFr($d['eventNext']['date_event']);
            }
            return $d;
        } else {
            return FALSE;
        }
    }

   /**
    *  vérifie la/les donnée(s) passée(s) en argument
    *  @param array $data donnée(s) à vérifier
    *  @param array $fichier fichier à controler
    **/
    public function verifications($data, $fichier)
    {
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
    public function verifFile($fichier)
    {
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

