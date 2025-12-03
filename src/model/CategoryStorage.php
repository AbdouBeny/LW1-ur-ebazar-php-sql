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
        while($row = $stmt->fetch(PDO::FETCH_ADDOC)){
            $cat = new Category($row['name']);
            $cat->setId($row['id']);
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
     * renommer une categorie
     */
    public function update($id, $newName){
        $sql = "UPDATE categories SET name = :name where id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':name', $newName);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }
}