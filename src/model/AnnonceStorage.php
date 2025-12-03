<?php
require_once 'Database.php';
require_once 'Annonce.php';

class AnnonceStorage {
    private $pdo;

    public function __construct(){
        $this->pdo = Database::getInstance();
    }

    /**
     * ajouter une annonce
     */
    public function add(Annonce $annonce){
        $sql = "INSERT INTO annonces (user_id, category_id, title, description, price, delivery, status)
        values (:user_id, :category_id, :title, :description, :price, :delivery, :status)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $annonce->getUserId());
        $stmt->bindValue(':category_id', $annonce->getCategoryId());
        $stmt->bindValue(':title', $annonce->getTitle());
        $stmt->bindValue(':description', $annonce->getDescription());
        $stmt->bindValue(':price', $annonce->getPrice());
        $stmt->bindValue(':delivery', $annonce->getDelivery());
        $stmt->bindValue(':status', "available");
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }
    
    /**
     * ajouter une photo
     */
    public function addPhoto($annonceId, $filename){
        $sql = "INSERT INTO photos (annonce_id, filename) values (:aid, :filename)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':aid', $annonceId);
        $stmt->bindValue(':filename', $filename);
        return $stmt->execute();
    }

    /**
     * recuperer les photos
     */
    public function getPhotos($annonceId){
        $sql = "SELECT filename FROM photos where annonce_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $annonceId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * lister les annonces diponbiles par categorie
     */
    public function listAvailableByCategory($categoryId){
        $sql = "SELECT * FROM annonces
                where category_id = :cat and status = 'available'
                order by created_at desc";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':cat', $categoryId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}