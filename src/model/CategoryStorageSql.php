<?php
require_once("CategoryStorage.php");
require_once("Category.php");
require_once("config/Database.php");

/**
 * implementation SQL du stockage des catégories
 */
class CategoryStorageSql implements CategoryStorage{
    private $pdo;
    
    public function __construct(){
        $this->pdo = Database::getInstance();
    }
    
    /**
     * lit une catégorie par son ID
     * @param string $id identifiant de la catégorie
     * @return Category|null 
     */
    public function read($id){
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return $this->rowToCategory($row);
        }
        return null;
    }
    
    /**
     * lit toutes les catégories
     * @return array tableau de catégories indexées par ID
     */
    public function readAll(){
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $categories = [];
        foreach ($rows as $row) {
            $categories[$row['id']] = $this->rowToCategory($row);
        }
        return $categories;
    }
    
    /**
     * crée une nouvelle catégorie
     * @param Category $c la catégorie à créer
     * @return bool succés de l'operation
     */
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
    
    /**
     * met à jour une catégorie
     * @param string $id ID de la catégorie à mettre à jour 
     * @return bool 
     */
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
    
    /**
     * supprime une catégorie
     * @param string îd ID de la catégorie à supprimer
     * @return bool
     */
    public function delete($id){
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * compte le nombre d'annonces dans une catégorie
     * @param string $categoryId
     * @return int nombre d'annonces
     */
    public function countAnnonces($categoryId){
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM annonces 
            WHERE category_id = :category_id AND sold = 0
        ");
        $stmt->execute(['category_id' => $categoryId]);
        return $stmt->fetchColumn();
    }
    
    /**
     * convertit une ligne de base de don en objet Category
     * @param array $row Ligne de reultat de la bd
     * @return Category l'objet category crée
     */
    private function rowToCategory($row){
        return new Category($row['id'], $row['name']);
    }
}