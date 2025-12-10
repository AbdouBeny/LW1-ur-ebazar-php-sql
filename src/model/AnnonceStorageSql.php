<?php
require_once("AnnonceStorage.php");
require_once("Annonce.php");
require_once("config/Database.php");

class AnnonceStorageSql implements AnnonceStorage{

    private $pdo;

    public function __construct(){
        $this->pdo = Database::getInstance();
    }


    public function read($id){
        $stmt = $this->pdo->prepare("SELECT * FROM annonces WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->rowToAnnonce($row) : null;
    }

    public function readAll(){
        $stmt = $this->pdo->query("SELECT * FROM annonces ORDER BY created_date DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $annonces = [];
        foreach ($rows as $row) {
            $annonces[$row['id']] = $this->rowToAnnonce($row);
        }
        return $annonces;
    }

    public function readAllNotSold(){
        $stmt = $this->pdo->prepare("
            SELECT * FROM annonces 
            WHERE sold = 0 
            ORDER BY created_date DESC
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $annonces = [];
        foreach ($rows as $row){
            $annonces[$row['id']] = $this->rowToAnnonce($row);
        }
        return $annonces;
    }

    public function readBySeller($email){
        $stmt = $this->pdo->prepare("
            SELECT * FROM annonces 
            WHERE seller_email = :seller_email
            ORDER BY created_date DESC
        ");
        $stmt->execute(['seller_email' => $email]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $annonces = [];
        foreach ($rows as $row){
            $annonces[$row['id']] = $this->rowToAnnonce($row);
        }
        return $annonces;
    }

    public function readByCategory($categoryId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM annonces 
            WHERE category_id = :category_id 
              AND sold = 0
            ORDER BY created_date DESC
        ");
        $stmt->execute(['category_id' => $categoryId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $annonces = [];
        foreach ($rows as $row) {
            $annonces[$row['id']] = $this->rowToAnnonce($row);
        }
        return $annonces;
    }

    public function readByCategoryPaginated($categoryId, $page = 1, $perPage = 10){
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM annonces 
            WHERE category_id = :category_id 
            AND sold = 0
            ORDER BY created_date DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':category_id', $categoryId);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $annonces = [];
        foreach ($rows as $row) {
            $annonces[$row['id']] = $this->rowToAnnonce($row);
        }
        return $annonces;
    }

    public function countByCategory($categoryId){
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM annonces 
            WHERE category_id = :category_id 
            AND sold = 0
        ");
        $stmt->execute(['category_id' => $categoryId]);
        return $stmt->fetchColumn();
    }


    public function create(Annonce $a){
        $id = $this->generateId($a);

        $stmt = $this->pdo->prepare("
            INSERT INTO annonces (
                id, title, description, price, category_id, seller_email,
                photos, delivery_modes, created_date, sold
            ) VALUES (
                :id, :title, :description, :price, :category_id, :seller_email,
                :photos, :delivery_modes, :created_date, :sold
            )
        ");

        $photos = json_encode($a->getPhotos());
        $delivery_modes = json_encode($a->getDeliveryModes());

        $success = $stmt->execute([
            'id' => $id,
            'title' => $a->getTitle(),
            'description' => $a->getDescription(),
            'price' => $a->getPrice(),
            'category_id' => $a->getCategoryId(),
            'seller_email' => $a->getSellerEmail(),
            'photos' => $photos,
            'delivery_modes' => $delivery_modes,
            'created_date' => $a->getCreatedDate()->format('Y-m-d H:i:s'),
            'sold' => $a->isSold() ? 1 : 0
        ]);

        return $success ? $id : false;
    }


    public function update($id, Annonce $a){
        $stmt = $this->pdo->prepare("
            UPDATE annonces
            SET title = :title,
                description = :description,
                price = :price,
                category_id = :category_id,
                seller_email = :seller_email,
                photos = :photos,
                delivery_modes = :delivery_modes,
                created_date = :created_date,
                sold = :sold
            WHERE id = :id
        ");

        $photos = json_encode($a->getPhotos());
        $delivery_modes = json_encode($a->getDeliveryModes());

        return $stmt->execute([
            'id' => $id,
            'title' => $a->getTitle(),
            'description' => $a->getDescription(),
            'price' => $a->getPrice(),
            'category_id' => $a->getCategoryId(),
            'seller_email' => $a->getSellerEmail(),
            'photos' => $photos,
            'delivery_modes' => $delivery_modes,
            'created_date' => $a->getCreatedDate()->format('Y-m-d H:i:s'),
            'sold' => $a->isSold() ? 1 : 0
        ]);
    }


    public function delete($id){
        $stmt = $this->pdo->prepare("DELETE FROM annonces WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }


    private function generateId(Annonce $a){
        $base = preg_replace('/[^a-z0-9\-]/i', '-', strtolower($a->getTitle()));
        $base = trim($base, '-');
        $id = $base ? $base . '-' . uniqid() : 'annonce-' . uniqid();
        
        // Vérifier si l'ID existe déjà
        do {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM annonces WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $exists = $stmt->fetchColumn() > 0;
            
            if ($exists) {
                $id = $base . '-' . uniqid();
            }
        } while ($exists);
        
        return $id;
    }

    private function rowToAnnonce($row){
        $photos = json_decode($row['photos'], true) ?: [];
        $delivery_modes = json_decode($row['delivery_modes'], true) ?: [];

        $annonce = new Annonce(
            $row['title'],
            $row['description'],
            $row['price'],
            $row['category_id'],
            $row['seller_email'],
            $delivery_modes,
            $photos
        );
        $annonce->setCreatedDate(new DateTime($row['created_date']));
        $annonce->setSold($row['sold']);

        return $annonce;
    }


}
