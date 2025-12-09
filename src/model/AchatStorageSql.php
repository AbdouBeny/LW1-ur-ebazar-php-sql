<?php
require_once("AchatStorage.php");
require_once("Achat.php");
require_once("config/Database.php");


class AchatStorageSql implements AchatStorage{

    private $pdo;

    public function __construct(){
        $this->pdo = Database::getInstance();
    }

    /* ======================== READ ======================== */

    public function read($id){
        $stmt = $this->pdo->prepare("SELECT * FROM achats WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->rowToAchat($row) : null;
    }

    public function readAll(){
        $stmt = $this->pdo->query("SELECT * FROM achats ORDER BY purchase_date DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $achats = [];
        foreach ($rows as $row){
            $achats[$row['id']] = $this->rowToAchat($row);
        }

        return $achats;
    }


    /* ======================== CREATE ======================== */

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


    /* ======================== UPDATE ======================== */

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


    /* ======================== DELETE ======================== */

    public function delete($id){
        $stmt = $this->pdo->prepare("DELETE FROM achats WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }


    /* ======================== FIND METHODS ======================== */

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

    public function findByAnnonce($annonceId){
        $stmt = $this->pdo->prepare("
            SELECT * FROM achats 
            WHERE annonce_id = :annonce_id
        ");
        $stmt->execute(['annonce_id' => $annonceId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->rowToAchat($row) : null;
    }


    /* ======================== HELPERS ======================== */

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
