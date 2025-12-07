<?php
require_once("AnnonceStorage.php");
require_once("Annonce.php");


class AnnonceStorageSession implements AnnonceStorage{
    protected $key = 'annonces';
    
    public function __construct(){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[$this->key]) || !is_array($_SESSION[$this->key])){
            $_SESSION[$this->key] = array(
                'velo-001' => array(
                    'title' => 'Vélo de montagne',
                    'description' => "Vélo tout suspendu, très bon état, chaines et freins révisés.",
                    'price' => 19900,
                    'categoryId' => 'cat_3',
                    'sellerEmail' => 'vendeur@example.com',
                    'photos' => array('velo1.jpg'),
                    'deliveryModes' => array('remise','poste'),
                    'createdDate' => new DateTime('2024-01-15'),
                    'sold' => false
                ),
                'livre-002' => array(
                    'title' => 'Lot de romans policiers',
                    'description' => "Ensemble de 10 romans, état correct.",
                    'price' => 1500,
                    'categoryId' => 'cat_2',
                    'sellerEmail' => 'vendeur@example.com',
                    'photos' => array(),
                    'deliveryModes' => array('poste'),
                    'createdDate' => new DateTime('2024-01-20'),
                    'sold' => false
                ),
                'ordi-003' => array(
                    'title' => 'Ordinateur portable 13"',
                    'description' => "Ultrabook, SSD 256 Go, 8 Go RAM. Batterie ok.",
                    'price' => 35000,
                    'categoryId' => 'cat_1',
                    'sellerEmail' => 'admin@example.com',
                    'photos' => array('ordi1.jpg','ordi2.jpg'),
                    'deliveryModes' => array('remise'),
                    'createdDate' => new DateTime('2024-01-25'),
                    'sold' => false
                ),
            );
        }
    }
    
    protected function arrayToAnnonce($arr){
        $annonce = new Annonce(
            $arr['title'],
            $arr['description'],
            $arr['price'],
            $arr['categoryId'],
            $arr['sellerEmail'],
            $arr['deliveryModes'],
            $arr['photos']
        );
        
        if (isset($arr['createdDate'])){
            $annonce->setCreatedDate($arr['createdDate']);
        }
        
        if (isset($arr['sold'])){
            $annonce->setSold($arr['sold']);
        }
        
        return $annonce;
    }
    
    public function read($id){
        if (isset($_SESSION[$this->key][$id])){
            return $this->arrayToAnnonce($_SESSION[$this->key][$id]);
        }
        return null;
    }
    
    public function readAll() {
        $out = array();
        foreach ($_SESSION[$this->key] as $id => $arr){
            $out[$id] = $this->arrayToAnnonce($arr);
        }
        return $out;
    }
    
    public function readAllNotSold(){
        $out = array();
        foreach ($_SESSION[$this->key] as $id => $arr){
            if (!isset($arr['sold']) || !$arr['sold']){
                $out[$id] = $this->arrayToAnnonce($arr);
            }
        }
        return $out;
    }
    
    public function readBySeller($email){
        $out = array();
        foreach ($_SESSION[$this->key] as $id => $arr){
            if($arr['sellerEmail'] === $email){
                $out[$id] = $this->arrayToAnnonce($arr);
            }
        }
        return $out;
    }
    
    public function readByCategory($categoryId){
        $out = array();
        foreach ($_SESSION[$this->key] as $id => $arr) {
            if ($arr['categoryId'] === $categoryId && (!isset($arr['sold']) || !$arr['sold'])){
                $out[$id] = $this->arrayToAnnonce($arr);
            }
        }
        return $out;
    }
    
    public function create(Annonce $a){
        $base = preg_replace('/[^a-z0-9\-]/i', '-', strtolower($a->getTitle()));
        $base = trim($base, '-');
        $id = $base ? $base . '-' . uniqid() : 'annonce-' . uniqid();
        while (isset($_SESSION[$this->key][$id])) {
            $id = $base . '-' . uniqid();
        }
        
        $_SESSION[$this->key][$id] = array(
            'title' => $a->getTitle(),
            'description' => $a->getDescription(),
            'price' => $a->getPrice(),
            'categoryId' => $a->getCategoryId(),
            'sellerEmail' => $a->getSellerEmail(),
            'photos' => $a->getPhotos(),
            'deliveryModes' => $a->getDeliveryModes(),
            'createdDate' => $a->getCreatedDate(),
            'sold' => $a->isSold()
        );
        return $id;
    }
    
    public function update($id, Annonce $a){
        if (!isset($_SESSION[$this->key][$id])) return false;
        
        $_SESSION[$this->key][$id] = array(
            'title' => $a->getTitle(),
            'description' => $a->getDescription(),
            'price' => $a->getPrice(),
            'categoryId' => $a->getCategoryId(),
            'sellerEmail' => $a->getSellerEmail(),
            'photos' => $a->getPhotos(),
            'deliveryModes' => $a->getDeliveryModes(),
            'createdDate' => $a->getCreatedDate(),
            'sold' => $a->isSold()
        );
        return true;
    }
    
    public function delete($id){
        if (!isset($_SESSION[$this->key][$id])) return false;
        unset($_SESSION[$this->key][$id]);
        return true;
    }
    
}