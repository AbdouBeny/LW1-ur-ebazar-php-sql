<?php
require_once("CategoryStorage.php");
require_once("Category.php");

/**
 * Cette classe utilise la session PHP comme système de stockage temporaire pour les annonces.
 * Elle est principalement destinée aux tests
 * elle est actuellement désactivée dans site.php
 */
class CategoryStorageSession implements CategoryStorage{
    protected $key = 'categories';
    
    public function __construct(){
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }
        
        if (!isset($_SESSION[$this->key]) || !is_array($_SESSION[$this->key])) {
            $_SESSION[$this->key] = array(
                'cat_1' => array(
                    'id' => 'cat_1',
                    'name' => 'Informatique'
                ),
                'cat_2' => array(
                    'id' => 'cat_2',
                    'name' => 'Livres'
                ),
                'cat_3' => array(
                    'id' => 'cat_3',
                    'name' => 'Sport'
                ),
                'cat_4' => array(
                    'id' => 'cat_4',
                    'name' => 'Maison'
                ),
                'cat_5' => array(
                    'id' => 'cat_5',
                    'name' => 'Vêtements'
                ),
                'cat_6' => array(
                    'id' => 'cat_6',
                    'name' => 'Autres'
                )
            );
        }
    }
    
    protected function arrayToCategory($arr){
        return new Category($arr['id'], $arr['name']);
    }
    
    public function read($id){
        if (isset($_SESSION[$this->key][$id])){
            return $this->arrayToCategory($_SESSION[$this->key][$id]);
        }
        return null;
    }
    
    public function readAll(){
        $out = array();
        foreach ($_SESSION[$this->key] as $id => $arr){
            $out[$id] = $this->arrayToCategory($arr);
        }
        return $out;
    }
    
    public function create(Category $c){
        $id = $c->getId();
        if (isset($_SESSION[$this->key][$id])){
            return false;
        }
        
        $_SESSION[$this->key][$id] = array(
            'id' => $c->getId(),
            'name' => $c->getName()
        );
        return true;
    }
    
    public function update($id, Category $c){
        if (!isset($_SESSION[$this->key][$id])){
            return false;
        }
        
        $_SESSION[$this->key][$id] = array(
            'id' => $c->getId(),
            'name' => $c->getName()
        );
        return true;
    }
    
    public function delete($id){
        if (!isset($_SESSION[$this->key][$id])) {
            return false;
        }
        unset($_SESSION[$this->key][$id]);
        return true;
    }
    
    public function countAnnonces($categoryId){
        if (!isset($_SESSION['annonces'])) {
            return 0;
        }
        
        $count = 0;
        foreach ($_SESSION['annonces'] as $key => $annonce) {
            if (isset($annonce['categoryId']) && $annonce['categoryId'] === $categoryId && $annonce['sold']=== false) {
                $count++;
            }
        }
        return $count;
    }

    
}