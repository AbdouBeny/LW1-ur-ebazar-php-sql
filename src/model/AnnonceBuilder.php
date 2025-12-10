<?php
require_once("Annonce.php");

/**
 * builder pour créer et valider les objets Annonce
 * gere la validation des données de formulaire pour les annonces
 */
class AnnonceBuilder{
    const TITLE_REF = 'title';
    const DESCRIPTION_REF = 'description';
    const PRICE_REF = 'price';
    const CATEGORY_REF = 'category';
    const DELIVERY_POSTE_REF = 'delivery_poste';
    const DELIVERY_REMISE_REF = 'delivery_remise';
    
    protected $data;
    protected $error;
    protected $photos;
    protected $uploadedPhotos = array();
    
    public function __construct($data = array(), $photos = array()){
        $this->data = $data;
        $this->photos = $photos;
        $this->error = null;
    }
    // getters
    public function getData(){ return $this->data; }
    public function getPhotos(){ return $this->photos; }
    public function getError(){ return $this->error; }
    

    /**
     * valide les données du formularire d'annonce
     * @return bool vrai si les données sont validés
     */
    public function isValid(){
        // vérifier si post dépasse post_max_size (alors on aurait pu ajouter un fichier php.ini pour augementer post_max_size)
        if (!empty($_SERVER['CONTENT_LENGTH']) && 
            $_SERVER['CONTENT_LENGTH'] > 8 * 1024 * 1024){

            $this->error = "Le formulaire est trop volumineux. Photo trop grande.";
            return false;
        }

        $title = isset($this->data[self::TITLE_REF]) ? trim($this->data[self::TITLE_REF]) : '';
        $description = isset($this->data[self::DESCRIPTION_REF]) ? trim($this->data[self::DESCRIPTION_REF]) : '';
        $price = isset($this->data[self::PRICE_REF]) ? trim($this->data[self::PRICE_REF]) : '';
        $category = isset($this->data[self::CATEGORY_REF]) ? trim($this->data[self::CATEGORY_REF]) : '';
        $deliveryPoste = isset($this->data[self::DELIVERY_POSTE_REF]);
        $deliveryRemise = isset($this->data[self::DELIVERY_REMISE_REF]);
        
        // validation titre
        if(empty($title)){
            $this->error = "le titre est requis.";
            return false;
        }
        if(strlen($title) < 5) {
            $this->error = "le titre doit contenir au moins 5 caractères.";
            return false;
        }
        if(strlen($title)> 30){
            $this->error = "le titre ne peut pas dépasser 30 caractères.";
            return false;
        }
        
        // validation description
        if(empty($description)){
            $this->error = "La description est requise.";
            return false;
        }
        if(strlen($description) < 5){
            $this->error = "la description doit contenir au moins 5 caractères.";
            return false;
        }
        if(strlen($description) > 200){
            $this->error = "La description ne peut pas dépasser 200 caractères.";
            return false;
        }
        
        // validation prix
        if (!is_numeric($price) || floatval($price) < 0){
            $this->error = "le prix doit être un nombre positif ou nul.";
            return false;
        }
        
        // validation catégorie
        if(empty($category)){
            $this->error = "la catégorie est requise.";
            return false;
        }
        
        // validation modes de livraison
        if (!$deliveryPoste && !$deliveryRemise){
            $this->error = "Au moins un mode de livraison doit être sélectionné.";
            return false;
        }
        
        // validation photos
        if (!empty($this->photos['name'][0])){
            $photoCount = count($this->photos['name']);
            if ($photoCount > 5){
                $this->error = "Vous ne pouvez pas télécharger plus de 5 photos.";
                return false;
            }
            for($i = 0; $i < $photoCount; $i++){
                if($this->photos['error'][$i] === UPLOAD_ERR_NO_FILE){
                    continue;
                }
                if ($this->photos['error'][$i] === UPLOAD_ERR_OK) {
                    if ($this->photos['size'][$i] > 200 * 1024) {
                        $this->error = "chaque photo doit faire moins de 200 Ko.";
                        return false;
                    }
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $this->photos['tmp_name'][$i]);
                    finfo_close($finfo);
                    var_dump($mime);
                    if(!in_array($mime, ['image/jpeg', 'image/jpg'])) {
                        $this->error = "Les photos doivent être au format JPEG/JPG.";
                        return false;
                    }
                    chmod('uploads/annonces/', 0777);
                    $filename = uniqid() . '.jpg';
                    move_uploaded_file($this->photos['tmp_name'][$i], 'uploads/annonces/' . $filename);
                    $this->uploadedPhotos[] = $filename;
                } else{
                    $this->error = "Erreur lors du téléchargement de la photo.";
                    return false;
                }
            }
        }
        
        $this->error = null;
        return true;
    }
    
    /**
     * crée un nouvel objet Annonce à partir des données validées
     * @param string $sellerEmail email de vendeur
     * @return Annonce l'objet annonce crée
     */
    public function createAnnonce($sellerEmail){
        $deliveryModes = array();
        if (isset($this->data[self::DELIVERY_POSTE_REF])) $deliveryModes[] = 'poste';
        if (isset($this->data[self::DELIVERY_REMISE_REF])) $deliveryModes[] = 'remise';
        
        return new Annonce(
            $this->data[self::TITLE_REF],
            $this->data[self::DESCRIPTION_REF],
            floatval($this->data[self::PRICE_REF]) * 100,
            $this->data[self::CATEGORY_REF],
            $sellerEmail,
            $deliveryModes,
            $this->uploadedPhotos,

        );
    }

    
}