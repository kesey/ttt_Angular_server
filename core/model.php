<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes_2_server
 * PATH: Third_Type_Tapes_2_server/core/
 * NAME: model.php
 */

class Model
{
    protected $table;
	protected $models;
    
    // utile si le model instancié peut être archivé
    protected $notArchive = "1 = 1";
    
    // utile si le model instancié possède un champ mot de passe
    protected $psw = "";
    
    // liste les caractères sensibles
    protected $sensChars = array(' ','#','&','é','è','ê','ë','í','ì','î','ï','ú','ù','û','ü','ý','ÿ','ç','ñ','Á','À','Â','Ä','Ã','Å','Ó','Ò','Ô','Ö','Õ','É','È','Ê','Ë','Í','Ì','Î','Ï','Ú','Ù','Û','Ü','Ý','Ÿ','Ç','Ñ');
	
	/**
    *  instancie le model demandé et permet son utilisation sous forme d'objet  
    *  @param string $name nom du model à instancier
    */
    protected function loadModel($name){
        require_once(ROOT.'/model/'.strtolower($name).'.php');
        $this->$name = new $name();
    }
	
	// instancie le(s) model(s) utile(s) et assigne les données passées en POST à un tableau $this->data
    function __construct(){
        if(isset($_POST)){
            $this->data = $_POST;
        }
        if(isset($_FILES)){
            $this->files = $_FILES;
        }
        if(isset($this->models)){
            foreach($this->models as $value){
                $this->loadModel($value);
            }
        }
    }
/*------------------------------------------------------------------------------traitement mots de passe--------------------------------------------------------------------*/    
   /**
    *  hachage du mot de passe 
    *  @param string|int $mdp mot de passe à hacher
    */
    protected function hashPsw($mdp){
        global $db;
        $mdpSecure = password_hash($mdp, PASSWORD_DEFAULT);
        return $db->quote($mdpSecure);
    }
/*------------------------------------------------------------------------------verification connexion----------------------------------------------------------------------*/   
   /**
    *  vérifie si l'identifiant est présent en base de données et vérifie le mot de passe correspondant
    *  @param string $ident identifiant à vérifier
    *  @param string $mdp mot de passe à vérifier 
    **/
    public function verifLog($ident,$mdp) {
        global $db;
        $ident = $this->securite_bdd($ident);
        $sql = "SELECT * FROM admin WHERE identifiant = :ident AND ".$this->notArchive;
        $pdoObj = $db->prepare($sql);
        $pdoObj->bindParam(':ident', $ident, PDO::PARAM_STR);
        $pdoObj->execute();
        if($pdoObj->rowCount()){
            $infos = $pdoObj->fetch();
            $pdoObj->closeCursor();
            if (password_verify($mdp, $infos["mot_de_passe"])){
                $_SESSION['mais qui cela peut-il bien être ?'] = $infos["nom"];
                if(isset($_SESSION['infoLog'])){
                    unset($_SESSION['infoLog']);
                }
                return TRUE;
            } else {
                $_SESSION['infoLog'] = "mot de passe incorrect";
                return FALSE;                
            }
        } else {
            $_SESSION['infoLog'] = "identifiant incorrect";
            return FALSE;            
        }
    }
/*------------------------------------------------------------------------------utilisation d'une adresse pour iframe google maps-------------------------------------------*/
   /**
    *  permet l'utilisation des adresse présentent en base de données dans l'iframe google maps
    *  @param string $address adresse à formater
    */
    public function adresseGMaps($address){
        $formatAd = str_replace(' ','+',$address);
        return $formatAd; 
    }
/*------------------------------------------------------------------------------remplacement de caractères sensibles--------------------------------------------------------*/
   /**
    *  remplace les caractères sensibles
    *  @param string $chaine chaine à formater
    */
    public function noSensChars($chaine){
        $sensChars = array( '' => array('°'),
                           ' ' => array('#'),
                           ':' => array('/'),
                           '-' => array('.'),
                           'a' => array('á','à','â','ä','ã','å'),
                           'o' => array('ó','ò','ô','ö','õ'),
                           'e' => array('é','è','ê','ë'),
                           'i' => array('í','ì','î','ï'),
                           'u' => array('ú','ù','û','ü'),
                           'y' => array('ý','ÿ'),
                           'c' => array('ç'),
                           'n' => array('ñ'),
                           'A' => array('Á','À','Â','Ä','Ã','Å'),
                           'O' => array('Ó','Ò','Ô','Ö','Õ'),
                           'E' => array('É','È','Ê','Ë'),
                           'I' => array('Í','Ì','Î','Ï'),
                           'U' => array('Ú','Ù','Û','Ü'),
                           'Y' => array('Ý','Ÿ'),
                           'C' => array('Ç'),
                           'N' => array('Ñ'));
        
        foreach ($sensChars as $key => $value){
            $chaine = str_replace($value,$key,$chaine);
        }               
        return $chaine; 
    }
/*------------------------------------------------------------------------------conversions date----------------------------------------------------------------------------*/    
   /**
    *  convertie une date au format français
    *  @param date $date la date à convertir
    */
    public function dateFr($date){ 
        setlocale (LC_TIME, 'fr_FR');  
        date_default_timezone_set("Europe/Paris");
        mb_internal_encoding("UTF-8");
        return strftime('%d-%m-%Y',strtotime($date));
    }
    /**
    *  convertie une date au format americain
    *  @param date $date la date à convertir
    */
    public function dateUs($date){ 
        setlocale (LC_TIME, 'en_US');  
        date_default_timezone_set("America/Los_Angeles");
        mb_internal_encoding("UTF-8");
        return strftime('%Y-%m-%d',strtotime($date));
    }
/*------------------------------------------------------------------------------vérifications de données--------------------------------------------------------------------*/    
    // vérifie si $data est un nombre entier positif
    public function isPosNum($data){
        if(ctype_digit($data) && $data >= 0){
            return TRUE;
        } else {
            return FALSE;
        }
    }
    // vérifie si $data est un nombre décimal positif
    public function isPosDec($data){
        if(is_numeric($data) && $data >= 0){
            return TRUE;
        } else {
            return FALSE;
        }
    }
    // vérifie si $mail est une adresse mail valide
    public function isEmail($mail){
        $regex = '/([a-z0-9_]+|[a-z0-9_]+\.[a-z0-9_]+)@(([a-z0-9]|[a-z0-9]+\.[a-z0-9]+)+\.([a-z]{2,4}))/i';
        if(preg_match($regex,$mail)){
            return TRUE;
        } else {
            return FALSE;
        }  
    }
    // vérifie si $url est une url valide
    public function isValidUrl($url){
        if(filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)){
            return TRUE;
        } else {
            return FALSE;
        }
    }
    // vérifie si $date est une date valide au format français
    public function isDateFr($date){
        $regex = '#^([0-9]{2})([-])([0-9]{2})\2([0-9]{4})$#';
        if(preg_match($regex, $date, $m) && checkdate($m[3], $m[1], $m[4])){
            return TRUE;
        } else {
            return FALSE;
        }
    }
    // vérifie si $date est une date valide au format français ou americain
    public function isDateUsFr($date){
        $regExUs = '#^([0-9]{4})([-])([0-9]{2})\2([0-9]{2})$#';
        if($this->isDateFr($date) || preg_match($regExUs, $date, $mark) && checkdate($mark[3], $mark[4], $mark[1])){
            return TRUE;
        } else {
            return FALSE;
        }
    }
    // vérifie si $file porte une extension comprise dans le tableau $validExt
    public function extImgOk($file){
        $name = basename($file);
        $ext = pathinfo($name,PATHINFO_EXTENSION);
        $validExt = array("jpg", "jpeg", "png");
        return in_array($ext, $validExt, TRUE);
    }
    // vérifie si $file porte une extension comprise dans le tableau $validExt
    public function extRarOk($file){
        $name = basename($file);
        $ext = pathinfo($name,PATHINFO_EXTENSION);
        $validExt = array("rar");
        return in_array($ext, $validExt, TRUE);
    }
    // vérifie si $file est une image
    public function isImage($file){
        $imgOk = TRUE;
        $validTypes = array(IMAGETYPE_JPEG, IMAGETYPE_PNG);
        $type = @getImageSize($file)[2];
        if(!in_array($type, $validTypes)){
            $imgOk = FALSE;
        }
        return $imgOk;
    }
    // vérifie si $type est un type mime correspondant à un .rar
    public function isRar($type) {
        $validTypes = array("application/x-rar-compressed", "application/octet-stream");
        return in_array($type, $validTypes);
    }
    // détecte les caractères sensibles dans la chaine $string
    public function contSensChars($string){
        $sensChars = FALSE;
        foreach ($this->sensChars as $value) {
            if(strrpos($string, $value) !== FALSE){
                $sensChars = TRUE;
            }
        }
        return $sensChars;
    }
    // vérifie si $code est un code valide
    public function refCodeOk($code) {
        $regEx = '#^T{3}([0-9]{2,4})$#';
        if(preg_match($regEx,$code)){
            return TRUE;
        } else {
            return FALSE;
        } 
    }
    // vérifie si $longueur est un format de longueur de K7 valide
    public function longueurOk($longueur){
        $regEx = '#^C[0-9]{2,3}$#';
        if(preg_match($regEx,$longueur)){
            return TRUE;
        } else {
            return FALSE;
        } 
    }
/*------------------------------------------------------------------------------securiser les données à destination/en provenance de la base de données---------------------*/        
   /**
    *  securise une donnée à destination de la base de données
    *  @param string|int|dec $data donnée à securiser
    */
    public function securite_bdd($data){
        if(ctype_digit($data)){
            $data = intval($data);
        } else {
            $data = addcslashes($data, '%');
        }
        return $data;
    }
   /**
    *  securise la restitution des données entrées en base de données grâce à un wysiwyg
    *  @param string $value chaine contenant les données à securiser
    */
	protected function securiteWys($value){
        include_once(ROOT."plugIn/htmLawed/htmLawed.php");
        $elements = 'a, b, strong, i, em, li, ol, ul, br, span, p, hr, h1, h2, h3, h4, h5, h6, pre';
        return htmLawed($value, array('safe' => 1, 'elements' => $elements));   
    }
   /**
    *  applique htmlentities aux éléments d'un tableau ou appelle $this->securiteWys() en fonction de la clef
    *  @param array $array tableau contenant les éléments à securiser
    */
    public function securiteHtml($array) {
        $clefsWys = array('description_event', 'description', 'bio'); // les éléments correspondent aux noms des champs dont les données ont été insérées à l'aide d'un wysiwyg
            foreach ($array as $k => $v) {
                foreach ($array[$k] as $key => &$value) {                    
                    if(in_array($key, $clefsWys)){ 
                        $value = $this->securiteWys($value);
                    } else {
                        $value = htmlentities($value);
                    }                                
                }
                unset($value);
            }           
            return $array;
    }
/*------------------------------------------------------------------------------interactions base de données----------------------------------------------------------------*/    
   /**
    *  lit une ligne dans la base de données par rapport à l'id du model instancié
    *  @param string $fields liste des champs à récupérer
    **/
    public function read($fields = "*"){
        global $db;
        $fields = $this->securite_bdd($fields);
        $id = $this->securite_bdd($this->id);
        $sql = "SELECT ".$fields." FROM ".$this->table." WHERE ".$this->notArchive." AND id_".$this->table." = :id";
        $pdoObj = $db->prepare($sql);
        $pdoObj->bindParam(':id', $id, PDO::PARAM_INT);
        if($pdoObj->execute()){
            $infos = $pdoObj->fetch();
            $pdoObj->closeCursor();
            $clefsWys = array('description_event', 'description', 'bio'); // les éléments correspondent aux noms des champs dont les données ont été insérées à l'aide d'un wysiwyg
            foreach ($infos as $key => $value){
                if(in_array($key, $clefsWys)){ 
                        $this->$key = $this->securiteWys($value);
                    } else {
                        $this->$key = htmlentities($value);
                    }                                            
            };
            return TRUE;
        } else {
            return FALSE;
        }
    }
   /**
    *  récupère une ou plusieurs ligne(s) dans la base de données par rapport au model instancié
    *  @param array $data contient les conditions, les champs, l'ordre et la limitation
    **/
    public function findAll($data = array()){
        global $db;
        $fields = "*";
        $conditions = "1 = 1";        
        $order = "id_".$this->table." DESC";
        $limit = "";
        if(isset($data["fields"])){
            $fields = $this->securite_bdd($data['fields']);
        }
        if(isset($data["conditions"])){
            $conditions = $this->securite_bdd($data['conditions']);
        }
        if(isset($data["order"])){
            $order = $this->securite_bdd($data['order']);
        }
        if(isset($data["limit"])){
            $limit = $this->securite_bdd($data['limit']);
            $limit = " LIMIT ".$limit;
        }        
        $sql = "SELECT ".$fields." FROM ".$this->table." WHERE ".$conditions." AND ".$this->notArchive." ORDER BY ".$order.$limit;
        $pdoObj = $db->prepare($sql);
        if($pdoObj->execute()){
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
    *  recherche une/des donnée(s) par rapport au model instancié, permet l'utilisation des %
    *  @param array $data contient le champ dans lequel effectuer la recherche et la donnée à chercher
    */
    public function search($data= array()){
        global $db;
        $field = 1;
        $what = 1;
        if(isset($data["field"])){
            $field = $this->securite_bdd($data['field']);
        }
        if(isset($data["what"])){
            $what = $this->securite_bdd($data['what']);
        }
        $sql = "SELECT * FROM ".$this->table." WHERE ".$field." LIKE '%".$what."%' AND ".$this->notArchive." ORDER BY id_".$this->table." DESC";
        $pdoObj = $db->prepare($sql);
        if($pdoObj->execute()){
            $tabFind = array();
            while ($infos = $pdoObj->fetch()){
                $tabFind[] = $infos;
            }
            $pdoObj->closeCursor();
            if(empty($tabFind)){
                return FALSE;
            } else {
                return $this->securiteHtml($tabFind);
            }            
        } else {
            return FALSE;
        }
    }
   /**
    *  sauvegarde ou met à jour les données passées en paramètre dans la base de données
    *  @param array $data données à sauvegarder
    **/
    public function save($data){
        global $db;
        if(isset($data["id_".$this->table]) && !empty($data["id_".$this->table]) && $this->exist("id_".$this->table,$data["id_".$this->table])){
            $id = $this->securite_bdd($data["id_".$this->table]);
            $sql = "UPDATE ".$this->table." SET";
            foreach ($data as $key => $value){
                $key = $this->securite_bdd($key);
                $value = $this->securite_bdd($value);
                // valable si il existe une colonne "$this->psw" dans la table 
                if($key === $this->psw){
                    $v = $this->hashPsw($value);
                } else {
                // dans les autres cas
                    $v = $db->quote($value);
                } 
                if($key != "id_$this->table") {                    
                    $sql .= " ".$key." = ".$v.",";
                }
            };
            $sql = substr($sql, 0,-1);
            $sql .= " WHERE id_".$this->table." = :id";
            $pdoObj = $db->prepare($sql);
            $pdoObj->bindParam(':id', $id, PDO::PARAM_INT);
        } else {
            $sql = "INSERT INTO ".$this->table." (";
            unset($data["id_".$this->table]);
            foreach ($data as $key => $value){
                    $key = $this->securite_bdd($key);
                    $sql .= $key.",";
            };
            $sql = substr($sql, 0,-1);
            $sql .= ") VALUES (";
            foreach ($data as $key => $value){
                $value = $this->securite_bdd($value);
                if($key === $this->psw){
                    $v = $this->hashPsw($value);
                } else {
                    $v = $db->quote($value);
                }
                $sql .= $v.",";
            }
            $sql = substr($sql, 0,-1);
            $sql .= ")";
            $pdoObj = $db->prepare($sql);
        }        
        if($pdoObj->execute()){
            if(!isset($data["id_".$this->table])){
                $this->id = $db->lastInsertId();
            } else if ($this->exist("id_".$this->table,$data["id_".$this->table])){
                $this->id = $this->securite_bdd($data["id_".$this->table]);
            }
            $_SESSION["infoSave"] = "Sauvegarde réussie";
            return TRUE;
        } else {
            $_SESSION["infoSave"] = "La sauvegarde a échoué";
            return FALSE;
        }       
    }
   /**
    *  efface une ligne dans la base de données correspondant à l'id du model instancié
    *  @param string|int $id id de la ligne que l'on souhaite effacer
    **/
    public function delete($id = NULL){
        global $db;
        $id = $this->securite_bdd($id);
        if(!$id){
            $id = $this->securite_bdd($this->id);
        }
        $sql = "DELETE FROM ".$this->table." WHERE id_".$this->table." = :id";
        $pdoObj = $db->prepare($sql);
        $pdoObj->bindParam(':id', $id, PDO::PARAM_INT);
        return $pdoObj->execute();
    }    
   /**
    *  archive une ligne dans la base de données correspondant à l'id du model instancié
    *  @param string|int $id id de la ligne que l'on souhaite archiver
    **/
    public function archive($id = NULL){
        global $db;
        $id = $this->securite_bdd($id);
        if(!$id){
            $id = $this->securite_bdd($this->id);
        }
        $sql = "UPDATE ".$this->table." SET suppr = 1 WHERE id_".$this->table." = :id";
        $pdoObj = $db->prepare($sql);
        $pdoObj->bindParam(':id', $id, PDO::PARAM_INT);
        return $pdoObj->execute();
    }
   /**
    *  vérifie l'existance d'une donnée dans un champ du model instancié
    *  @param string $field le champ dans lequel on cherche la donnée 
    *  @param string|int $data la donnée dont on veut vérifier la présence  
    **/        
    public function exist($field,$data){
        global $db;
        $field = $this->securite_bdd($field);
        $data = $this->securite_bdd($data);
        $d = $db->quote($data);
        $sql = "SELECT ".$field." FROM ".$this->table." WHERE ".$this->notArchive." AND ".$field." = :data";
        $pdoObj = $db->prepare($sql);
        $pdoObj->bindParam(':data', $data, PDO::PARAM_STR);
        $pdoObj->execute();
        if($pdoObj->rowCount()){
            return TRUE;
        } else {
            return FALSE;
        }
    }   
   /**
    *  récupére la valeur maximum/minimum pour la colonne choisie de la table instanciée
    *  @param string $col permet de spécifier la colonne désirée
    *  @param string $maxMin permet de spécifier ce que l'on décide de récupérer, la valeur max ou la valeur min
    **/
    public function getDataMaxMin($col = NULL, $maxMin = "MAX"){
        global $db;
        $alias = "max";
        if(!$col){
            $col = "id_".$this->table;
        }
        if($maxMin !== "MAX"){
            $maxMin = "MIN";
            $alias = "min";
        }
        $sql = "SELECT ".$maxMin."(".$col.") AS ".$alias." FROM ".$this->table." WHERE ".$this->notArchive;
        $pdoObj = $db->prepare($sql);
        $success = $pdoObj->execute();
        if($success){
            $colMaxMin = $pdoObj->fetch();
            $pdoObj->closeCursor();
            return $colMaxMin;
        } else {
            return FALSE;
        }
    }
/*------------------------------------------------------------------------------resize image--------------------------------------------------------------------------------*/
    /**
     * easy image resize function
     * @param  $file - file name to resize
     * @param  $string - The image data, as a string
     * @param  $width - new image width
     * @param  $height - new image height
     * @param  $proportional - keep image proportional, default is no
     * @param  $output - name of the new file (include path if needed)
     * @param  $delete_original - if true the original image will be deleted
     * @param  $use_linux_commands - if set to true will use "rm" to delete the image, if false will use PHP unlink
     * @param  $quality - enter 1-100 (100 is best quality) default is 100
     * @param  $cropFromTop - if false crop will be from center, if true crop will be from top
     * @return boolean|resource
     */
    public function smart_resize_image(
        $file,
        $string             = null,
        $width              = 0,
        $height             = 0,
        $proportional       = false,
        $output             = 'file',
        $delete_original    = false,
        $use_linux_commands = false,
        $quality            = 100,
        $cropFromTop        = false
    ) {
        if ( $height <= 0 && $width <= 0 ) return false;
        if ( $file === null && $string === null ) return false;
        # Setting defaults and meta
        $info                         = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
        $image                        = '';
        $final_width                  = 0;
        $final_height                 = 0;
        list($width_old, $height_old) = $info;
        $cropHeight = $cropWidth = 0;
        # Calculating proportionality
        if ($proportional) {
            if      ($width  == 0)  $factor = $height/$height_old;
            elseif  ($height == 0)  $factor = $width/$width_old;
            else                    $factor = min( $width / $width_old, $height / $height_old );
            $final_width  = round( $width_old * $factor );
            $final_height = round( $height_old * $factor );
        }
        else {
            $final_width = ( $width <= 0 ) ? $width_old : $width;
            $final_height = ( $height <= 0 ) ? $height_old : $height;
            $widthX = $width_old / $width;
            $heightX = $height_old / $height;
            $x = min($widthX, $heightX);
            $cropWidth = ($width_old - $width * $x) / 2;
            $cropHeight = ($height_old - $height * $x) / 2;
        }
        # Loading image to memory according to type
        switch ( $info[2] ) {
            case IMAGETYPE_JPEG:  $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);  break;
            case IMAGETYPE_GIF:   $file !== null ? $image = imagecreatefromgif($file)  : $image = imagecreatefromstring($string);  break;
            case IMAGETYPE_PNG:   $file !== null ? $image = imagecreatefrompng($file)  : $image = imagecreatefromstring($string);  break;
            default: return false;
        }
        # This is the resizing/resampling/transparency-preserving magic
        $image_resized = imagecreatetruecolor( $final_width, $final_height );
        if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
            $transparency = imagecolortransparent($image);
            $palletsize = imagecolorstotal($image);
            if ($transparency >= 0 && $transparency < $palletsize) {
                $transparent_color  = imagecolorsforindex($image, $transparency);
                $transparency       = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($image_resized, 0, 0, $transparency);
                imagecolortransparent($image_resized, $transparency);
            }
            elseif ($info[2] == IMAGETYPE_PNG) {
                imagealphablending($image_resized, false);
                $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
                imagefill($image_resized, 0, 0, $color);
                imagesavealpha($image_resized, true);
            }
        }
        if ($cropFromTop){
            $cropHeightFinal = 0;
        }else{
            $cropHeightFinal = $cropHeight;
        }
        imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeightFinal, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);
        # Taking care of original, if needed
        if ( $delete_original ) {
            if ( $use_linux_commands ) exec('rm '.$file);
            else @unlink($file);
        }
        # Preparing a method of providing result
        switch ( strtolower($output) ) {
            case 'browser':
                $mime = image_type_to_mime_type($info[2]);
                header("Content-type: $mime");
                $output = NULL;
                break;
            case 'file':
                $output = $file;
                break;
            case 'return':
                return $image_resized;
                break;
            default:
                break;
        }
        # Writing image according to type to the output destination and image quality
        switch ( $info[2] ) {
            case IMAGETYPE_GIF:   imagegif($image_resized, $output);    break;
            case IMAGETYPE_JPEG:  imagejpeg($image_resized, $output, $quality);   break;
            case IMAGETYPE_PNG:
                $quality = 9 - (int)((0.9*$quality)/10.0);
                imagepng($image_resized, $output, $quality);
                break;
            default: return false;
        }
        return true;
    }
/*------------------------------------------------------------------------------upload/download-----------------------------------------------------------------------------*/
   /**
    *  lance le telechargement d'un fichier et incrémente un compteur
    *  @param string $fichier nom du fichier à telecharger
    *  @param string $sousDoss nom du sous-dossier contenant le fichier
    */
    public function telecharger_fichier($fichier, $sousDoss = ""){ 
        if($sousDoss === ""){
            $chemin = ROOT.'fichiers/'.$fichier;
        } else {
            $chemin = ROOT.'fichiers/'.$sousDoss.'/'.$fichier;
        }
        if($this->exist("download", $fichier) && file_exists($chemin) && strpos($fichier, '/') === FALSE && strpos($fichier, '.') !== 0){    
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($chemin));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: '.filesize($chemin));
            readfile($chemin);
            $cassette = $this->findAll(array(
                                            "fields" => "id_".$this->table.", nombre_de_download",
                                            "conditions" => "download = '".$fichier."'"                                            
                                            ));
            $cassette = $cassette[0];
            $compteur = $cassette['nombre_de_download']+1;
            $tab["id_".$this->table] = $cassette["id_".$this->table];
            $tab['nombre_de_download'] = $compteur;
            $this->save($tab);
            exit;  
        } else {
            return FALSE;
        }
    }
   /**
    *  lance l'upload d'un fichier
    *  @param string $fichierTemp nom du fichier temporaire
    *  @param string $fichier nom du fichier à uploader
    *  @param string $dossier nom du dossier qui devra contenir le sous-dossier ou le fichier
    *  @param string $sousDoss nom du sous-dossier qui devra contenir le fichier
    */
    public function upload_file($fichierTemp, $fichier, $dossier, $sousDoss = NULL){
        if(!preg_match('#[\x00-\x1F\x7F-\x9F/\\\\]#', $fichier)){
            if(!$sousDoss){
                $chemin = ROOT.$dossier.'/';
            } else {
                $chemin = ROOT.$dossier.'/'.$sousDoss.'/';
            }
            if($this->isImage($fichierTemp)){
                $resizeFileName = explode('.', $fichier);
                $resizeFileName = $resizeFileName[0].'-resize.'.$resizeFileName[1];
                // chemin pour stocker l'image redimmensionnée
                $resizedFilePath = $chemin.$resizeFileName;

                // redimensions et stockage des images
                $uploadResizeOk = $this->smart_resize_image($fichierTemp , null, 150, 150, true, $resizedFilePath, false, false, 100);
            } else {
                $uploadResizeOk = TRUE;
            }
            $uploadOk = move_uploaded_file($fichierTemp, $chemin.$fichier);
            return ($uploadOk && $uploadResizeOk);
        } else {
            return FALSE;
        }
    }
   /**
    *  efface un fichier
    *  @param string $fichier nom du fichier à effacer
    *  @param string $dossier nom du dossier qui devra contenir le sous-dossier ou le fichier
    *  @param string $sousDoss nom du sous-dossier qui devra contenir le fichier
    */
    public function delete_file($fichier, $dossier, $sousDoss = NULL){
        if(!$sousDoss){
            $chemin = ROOT.$dossier.'/';
        } else {
            $chemin = ROOT.$dossier.'/'.$sousDoss.'/';
        }
        if(file_exists($chemin)){
            if($this->isImage($chemin.$fichier)){
                $resizeFileName = explode('.', $fichier);
                $resizeFileName = $resizeFileName[0].'-resize.'.$resizeFileName[1];
                $resizedFilePath = $chemin.$resizeFileName;
                return (unlink($resizedFilePath) && unlink($chemin.$fichier));
            } else {
                return unlink($chemin.$fichier);
            }
        } else {
            return FALSE;
        }
    }
}