<?php
class Achat{
    protected $id;
    protected $annonceId;
    protected $buyerEmail;
    protected $sellerEmail;
    protected $deliveryMode;
    protected $purchaseDate;
    protected $received = false;
    
    public function __construct($id, $annonceId, $buyerEmail, $sellerEmail, $deliveryMode){
        $this->id = $id;
        $this->annonceId = $annonceId;
        $this->buyerEmail = $buyerEmail;
        $this->sellerEmail = $sellerEmail;
        $this->deliveryMode = $deliveryMode;
        $this->purchaseDate = new DateTime();
    }
    // getters
    public function getId(){ 
        return $this->id;
    }
    public function getAnnonceId(){ 
        return $this->annonceId; 
    }
    public function getBuyerEmail(){ 
        return $this->buyerEmail; 
    }
    public function getSellerEmail(){ 
        return $this->sellerEmail; 
    }
    public function getDeliveryMode(){ 
        return $this->deliveryMode; 
    }
    public function getPurchaseDate(){ 
        return $this->purchaseDate; 
    }
    public function isReceived(){ 
        return $this->received; 
    }
    //setters
    public function setReceived($received){ 
        $this->received = $received; 
    }
    public function setPurchaseDate($date){
        $this->purchaseDate = $date;
    }
}