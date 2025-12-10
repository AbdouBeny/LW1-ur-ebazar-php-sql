<?php
require_once("AchatStorage.php");
require_once("Achat.php");
require_once("config/Database.php");

/**
 * implémentation SQL du stockage des achats
 */
class AchatStorageSql implements AchatStorage{

    private $pdo;

    public function __construct(){
        $this->pdo = Database::getInstance();
    }

    /**
     * lit un achat par son ID
     * @param string $id identifiant de l'achat
     * @return Achat|null 
     */
    public function read($id){
        $stmt = $this->pdo->prepare("SELECT * FROM achats WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->rowToAchat($row) : null;
    }

    /**
     * lit tous les achat
     * @return array Tableau d'achats indexés par ID
     */
    public function readAll(){
        $stmt = $this->pdo->query("SELECT * FROM achats ORDER BY purchase_date DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $achats = [];
        foreach ($rows as $row){
            $achats[$row['id']] = $this->rowToAchat($row);
        }

        return $achats;
    }

    /**
     * crée un nouvel achat 
     * @param Achat $a l'achat à créer
     * @return bool succés
     */
    public function create(Achat $a){
        $stmt = $this->pdo->prepare("
            INSERT INTO achats (
                id, annonce_id, buyer_email, seller_email,
                delivery_mode, purchase_date, received
            ) VALUES (
                :id, :annonce_id, :buyer_email, :seller_email,
                :delivery_mode, :purchase_date, :received
            )
        ");

        return $stmt->execute([
            'id' => $a->getId(),
            'annonce_id' => $a->getAnnonceId(),
            'buyer_email' => $a->getBuyerEmail(),
            'seller_email' => $a->getSellerEmail(),
            'delivery_mode' => $a->getDeliveryMode(),
            'purchase_date' => $a->getPurchaseDate()->format('Y-m-d H:i:s'),
            'received' => $a->isReceived() ? 1 : 0
        ]);
    }

    /**
     * met à jour un achat
     * @param string $id ID de l'achat à mettre à jour
     * @param Achat $a nouvel objet achat
     * @return bool succés
     */
    public function update($id, Achat $a){
        $stmt = $this->pdo->prepare("
            UPDATE achats
            SET annonce_id = :annonce_id,
                buyer_email = :buyer_email,
                seller_email = :seller_email,
                delivery_mode = :delivery_mode,
                purchase_date = :purchase_date,
                received = :received
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'annonce_id' => $a->getAnnonceId(),
            'buyer_email' => $a->getBuyerEmail(),
            'seller_email' => $a->getSellerEmail(),
            'delivery_mode' => $a->getDeliveryMode(),
            'purchase_date' => $a->getPurchaseDate()->format('Y-m-d H:i:s'),
            'received' => $a->isReceived() ? 1 : 0
        ]);
    }

    /**
     * supprime un achat
     * @param string îd ID de l'achat à supprimer
     * @return bool succés
     */
    public function delete($id){
        $stmt = $this->pdo->prepare("DELETE FROM achats WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * recherche les achats d'un acheteur
     * @param string $email email de l'achteur
     * @return array Tableau d'achats 
     */
    public function findByBuyer($email){
        $stmt = $this->pdo->prepare("
            SELECT * FROM achats 
            WHERE buyer_email = :buyer_email 
            ORDER BY purchase_date DESC
        ");
        $stmt->execute(['buyer_email' => $email]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $achats = [];
        foreach ($rows as $row) {
            $achats[$row['id']] = $this->rowToAchat($row);
        }

        return $achats;
    }

    /**
     * recherche les ventes d'un vendeur
     * @param string $email email du vendeur
     * @return array Tableau de ventes du vendeur
     */
    public function findBySeller($email){
        $stmt = $this->pdo->prepare("
            SELECT * FROM achats 
            WHERE seller_email = :seller_email 
            ORDER BY purchase_date DESC
        ");
        $stmt->execute(['seller_email' => $email]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $achats = [];
        foreach ($rows as $row) {
            $achats[$row['id']] = $this->rowToAchat($row);
        }

        return $achats;
    }

    /**
     * recherche un achat par annonce
     * @param string $annonceId ID de l'annonce
     * @return Achat|null 
     */
    public function findByAnnonce($annonceId){
        $stmt = $this->pdo->prepare("
            SELECT * FROM achats 
            WHERE annonce_id = :annonce_id
        ");
        $stmt->execute(['annonce_id' => $annonceId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->rowToAchat($row) : null;
    }

    /**
     * convertit une ligne de base de données en objet Achat
     */
    private function rowToAchat($row){
        $achat = new Achat(
            $row['id'],
            $row['annonce_id'],
            $row['buyer_email'],
            $row['seller_email'],
            $row['delivery_mode']
        );
        $achat->setPurchaseDate(new DateTime($row['purchase_date']));
        $achat->setReceived($row['received']);

        return $achat;
    }
}
