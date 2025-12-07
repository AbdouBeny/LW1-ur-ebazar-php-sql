<?php
require_once("AchatStorage.php");
require_once("Achat.php");


class AchatStorageSession implements AchatStorage{
    protected $key = 'achats';
    
    public function __construct(){
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if(!isset($_SESSION[$this->key]) || !is_array($_SESSION[$this->key])){
            $_SESSION[$this->key] = array();
        }
    }
    
    protected function arrayToAchat($arr){
        $achat = new Achat(
            $arr['id'],
            $arr['annonceId'],
            $arr['buyerEmail'],
            $arr['sellerEmail'],
            $arr['deliveryMode']
        );
        
        if(isset($arr['purchaseDate'])){
            $achat->setPurchaseDate($arr['purchaseDate']);
        }
        
        if(isset($arr['received'])){
            $achat->setReceived($arr['received']);
        }
        
        return $achat;
    }
    
    public function read($id){
        if(isset($_SESSION[$this->key][$id])){
            return $this->arrayToAchat($_SESSION[$this->key][$id]);
        }
        return null;
    }
    
    public function readAll(){
        $out = array();
        foreach ($_SESSION[$this->key] as $id => $arr) {
            $out[$id] = $this->arrayToAchat($arr);
        }
        return $out;
    }
    
    public function create(Achat $a){
        $id = $a->getId();
        if(isset($_SESSION[$this->key][$id])){
            return false;
        }
        
        $_SESSION[$this->key][$id] = array(
            'id' => $a->getId(),
            'annonceId' => $a->getAnnonceId(),
            'buyerEmail' => $a->getBuyerEmail(),
            'sellerEmail' => $a->getSellerEmail(),
            'deliveryMode' => $a->getDeliveryMode(),
            'purchaseDate' => $a->getPurchaseDate(),
            'received' => $a->isReceived()
        );
        return true;
    }
    
    public function update($id, Achat $a){
        if(!isset($_SESSION[$this->key][$id])){
            return false;
        }
        
        $_SESSION[$this->key][$id] = array(
            'id' => $a->getId(),
            'annonceId' => $a->getAnnonceId(),
            'buyerEmail' => $a->getBuyerEmail(),
            'sellerEmail' => $a->getSellerEmail(),
            'deliveryMode' => $a->getDeliveryMode(),
            'purchaseDate' => $a->getPurchaseDate(),
            'received' => $a->isReceived()
        );
        return true;
    }
    
    public function delete($id){
        if(!isset($_SESSION[$this->key][$id])){
            return false;
        }
        unset($_SESSION[$this->key][$id]);
        return true;
    }
    
    public function findByBuyer($email){
        $out = array();
        foreach ($_SESSION[$this->key] as $id => $arr){
            if($arr['buyerEmail'] === $email){
                $out[$id] = $this->arrayToAchat($arr);
            }
        }
        return $out;
    }
    
    public function findBySeller($email){
        $out = array();
        foreach($_SESSION[$this->key] as $id => $arr){
            if($arr['sellerEmail'] === $email){
                $out[$id] = $this->arrayToAchat($arr);
            }
        }
        return $out;
    }
    
    public function findByAnnonce($annonceId){
        foreach ($_SESSION[$this->key] as $id => $arr){
            if ($arr['annonceId'] === $annonceId){
                return $this->arrayToAchat($arr);
            }
        }
        return null;
    }
}