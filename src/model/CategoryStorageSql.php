<?php
require_once("CategoryStorage.php");
require_once("Category.php");
require_once("config/Database.php");

class CategoryStorageSql implements CategoryStorage{
    private $pdo;
    
    public function __construct(){
        $this->pdo = Database::getInstance();
    }
    
    public function read($id){
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return $this->rowToCategory($row);
        }
        return null;
    }
    
    public function readAll(){
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $categories = [];
        foreach ($rows as $row) {
            $categories[$row['id']] = $this->rowToCategory($row);
        }
        return $categories;
    }
    
    public function create(Category $c) {
        $stmt = $this->pdo->prepare("
            INSERT INTO categories (id, name) 
            VALUES (:id, :name)
        ");
        
        return $stmt->execute([
            'id' => $c->getId(),
            'name' => $c->getName()
        ]);
    }
    
    public function update($id, Category $c){
        $stmt = $this->pdo->prepare("
            UPDATE categories 
            SET name = :name 
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'name' => $c->getName(),
            'id' => $id
        ]);
    }
    
    public function delete($id){
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    public function countAnnonces($categoryId){
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM annonces 
            WHERE category_id = :category_id AND sold = 0
        ");
        $stmt->execute(['category_id' => $categoryId]);
        return $stmt->fetchColumn();
    }
    
    private function rowToCategory($row){
        return new Category($row['id'], $row['name']);
    }
}