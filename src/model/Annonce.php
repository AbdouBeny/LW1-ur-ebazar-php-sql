<?php


class Annonce{
    protected $title;
    protected $description;
    protected $price;
    protected $categoryId;
    protected $sellerEmail;
    protected $photos; 
    protected $deliveryModes;
    protected $createdDate;
    protected $sold = false;
    
    public function __construct($title, $description, $priceCentimes, $categoryId, $sellerEmail, $deliveryModes = array(), $photos = array()) {
        $this->title = $title;
        $this->description = $description;
        $this->price = (int)$priceCentimes;
        $this->categoryId = $categoryId;
        $this->sellerEmail = $sellerEmail;
        $this->deliveryModes = is_array($deliveryModes) ? $deliveryModes : array();
        $this->photos = is_array($photos) ? $photos : array();
        $this->createdDate = new DateTime();
    }
    // getters
    public function getTitle(){ 
        return $this->title; 
    }
    public function getDescription(){ 
        return $this->description; 
    }
    public function getPrice(){ 
        return $this->price; 
    }
    public function getPriceFormatted(){
        return number_format($this->price / 100, 2, ',', ' ') . " €";
    }
    public function getCategoryId(){ 
        return $this->categoryId; 
    }
    public function getSellerEmail(){ 
        return $this->sellerEmail; 
    }
    public function getPhotos(){ 
        return $this->photos; 
    }
    public function getFirstPhotoUrl(){ 
        return count($this->photos) ? 'uploads/annonces/' . $this->photos[0] : null; 
    }
    public function getDeliveryModes(){ 
        return $this->deliveryModes; 
    }
    public function getCreatedDate(){ 
        return $this->createdDate; 
    }
    // setters
    public function setCreatedDate($date){
        $this->createdDate = $date;
    }
    public function setPhotos($photos){
        $this->photos = $photos;
    }
    public function setSold($sold){ 
        $this->sold = $sold; 
    }

    public function isSold(){ 
        return $this->sold; 
    }
    
    /**
     * retourne une description tronquée
     * @param int $max longueur max
     * @return string 
     */
    public function getShortDescription($max = 100){
        $desc = strip_tags($this->description);
        if (strlen($desc) <= $max) return $desc;
        return substr($desc, 0, $max - 3) . '...';
    }
    
    /**
     * verifie si un mode de livraison est accepté
     * @param string $mode 
     * @return bool 
     */
    public function acceptsDeliveryMode($mode){
        return in_array($mode, $this->deliveryModes);
    }

}