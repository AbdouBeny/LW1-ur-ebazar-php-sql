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

    /**
     * recuerer les n dernieres annonces
     */
    public function getLastAnnonces($limit=4){
        $sql = "SELECT * FROM annonces
                where status = 'available'
                order by created_at desc
                limit :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * recuperer la premiere photo
     */
    public function getFirstPhoto($annonceId){
        $sql = "SELECT filename from photos where annonce_id = :id order by id asc limit 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":id", $annonceId);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * recuperer une annonce
     */
    public function getAnnonce($id){
        $sql = "SELECT * FROM annonces WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * marquer une annonce comme vendue et enregistrer l'acheteur
     */
    public function purchaseAnnonce($annonceId, $buyerId, $delivery){
        try{
            $this->pdo->beginTransaction();

            $sql = "UPDATE annonces
                    SET status = 'sold',
                        buyer_id = :buyer,
                        delivery = :delivery,
                        sold_at = NOW()
                    WHERE id = :id AND status = 'available'";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':buyer', $buyerId, PDO::PARAM_INT);
            $stmt->bindValue(':delivery', $delivery);
            $stmt->bindValue(':id', $annonceId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() !== 1){
                $this->pdo->rollBack();
                return false;
            }

            $this->pdo->commit();
            return true;

        }catch(Exception $e){
            if ($this->pdo->inTransaction()){
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    /**
     * lister les annonces vendues par un utilisateur (vendeur)
     */
    public function listSoldByUser($userId){
        $sql = "SELECT * from annonces where user_id = :uid AND status = 'sold' order by sold_at desc";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * lister les annonces achetées par un acheteur
     */
    public function listBoughtByUser($userId){
        $sql = "SELECT * from annonces where buyer_id = :uid order by sold_at desc";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}