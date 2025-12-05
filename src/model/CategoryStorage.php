<?php
require_once 'Database.php';
require_once 'Category.php';

class CategoryStorage {

    private $pdo;

    public function __construct(){
        $this->pdo = Database::getInstance();
    }

    /**
     * recuperer toutes les catégories
     */
    public function getAll(){
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $this->pdo->query($sql);

        $categories = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $cat = new Category($row['name']);
            $cat->setId($row['id']);
            $categories[] = $cat;
        }
        return $categories;
    }

    /**
     * ajouter une catégorie
     */
    public function add(Category $category){
        $sql = "INSERT INTO categories (name) VALUES (:name)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':name', $category->getName());
        return $stmt->execute();
    }

    /**
     * verfier si une catégorie existe déjà
     */
    public function exists($name){
        $sql ="SELECT COUNT(*) FROM categories where name = :name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * recuperer les categories avec le nombre d'annonces dispo
     */
    public function getAllWithAnnonceCount(){
        $sql = "SELECT c.*, COUNT(a.id) AS count
                FROM categories c
                LEFT JOIN annonces a on a.category_id = c.id
                AND a.status = 'available'
                group by c.id
                order by c.name asc";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * recuperer une categorie avec son id 
     */
    public function getById($id){
        $sql = "SELECT * FROM categories where id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if($data){
            $category = new Category($data["name"]);
            $category->setId($data['id']);
            return $category;
        }
        return null;
    }

    /**
     * renommer une catégorie
     */
    public function rename($id, $name){
        $stmt = $this->pdo->prepare("UPDATE categories SET name = :name WHERE id = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":name", $name);
        return $stmt->execute();
    }

}