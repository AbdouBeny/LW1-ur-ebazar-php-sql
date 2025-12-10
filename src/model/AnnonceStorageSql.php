<?php
require_once("AnnonceStorage.php");
require_once("Annonce.php");
require_once("config/Database.php");

/**
 * implementation sql du stockage des annonces
 */
class AnnonceStorageSql implements AnnonceStorage{

    private $pdo;

    public function __construct(){
        $this->pdo = Database::getInstance();
    }

    /**
     * lit une annonce par son ID
     * @param string $id identifiant de l'annonce
     * @return Annonce|null 
     */
    public function read($id){
        $stmt = $this->pdo->prepare("SELECT * FROM annonces WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->rowToAnnonce($row) : null;
    }

    /**
     * lit toutes les annonces
     * @return array Tableau d'annonces indexées par ID
     */
    public function readAll(){
        $stmt = $this->pdo->query("SELECT * FROM annonces ORDER BY created_date DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $annonces = [];
        foreach ($rows as $row) {
            $annonces[$row['id']] = $this->rowToAnnonce($row);
        }
        return $annonces;
    }

    /**
     * lit toutes les annonces non vendues
     * @return array Tableau d'annonces non vendues
     */
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

    /**
     * lit les annonces d'un vedeur
     * @param string $email email du vendeur
     * @return array Tableau d'annonces du vendeur
     */
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

    /**
     * lit les annonces d'une catégorie
     * @param string $categoryId ID de la catégorie
     * @return array Tableau d'annonces de la catégorie
     */
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

    /**
     * lit les annonces par catégorie avec pagination
     * @param string $categoryId ID de la categorie
     * @param int $page numero de page 
     * @param int $perPage nombre d'annonces par page
     * @return array Tableau d'annonces
     */
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

    /**
     * compte le nombre d'annonces dans une catégorie
     * @param string $categoryId ID de la cat
     * @return int nombre d'annonces
     */
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

    /**
     * crée une nouvelle annonce
     * @param Annonce $a annonce à créer
     * @return string|false ID de l'annonce ou false en cas d'erreur
     */
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

    /**
     * met à jour une annonce
     * @param string $id ID de l annonce à mettre à jour 
     * @param Annonce $a nouvel objet annonce
     * @return bool succées de l'operation
     */
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

    /**
     * supprime une annonce
     * @param string $id ID de l'annonce à suppri
     * @return bool succés de l'op
     */
    public function delete($id){
        $stmt = $this->pdo->prepare("DELETE FROM annonces WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * génere un ID unique pour une annonce
     * @param Annonce $a l'annonce
     * @return string ID generer
     */
    private function generateId(Annonce $a){
        $base = preg_replace('/[^a-z0-9\-]/i', '-', strtolower($a->getTitle()));
        $base = trim($base, '-');
        $id = $base ? $base . '-' . uniqid() : 'annonce-' . uniqid();
        
        // vérifier si l'ID existe déjà
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

    /**
     * convertit une ligne de base de données en objet Annonce
     * @param array $row ligne de résultat de la base de données
     * @return Annonce l'objet annonce crée
     */
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
