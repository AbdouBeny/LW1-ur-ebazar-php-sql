<?php

class CategoryBuilder{
    const NAME_REF = 'name';
    
    protected $data;
    protected $error;
    
    public function __construct($data = array()){
        $this->data = $data;
        $this->error = null;
    }
    
    public function getData(){ return $this->data; }
    public function getError(){ return $this->error; }
    
    public function isValid(){
        $name = isset($this->data[self::NAME_REF]) ? trim($this->data[self::NAME_REF]) : '';
        
        if(empty($name)){
            $this->error = "Le nom de la catégorie est requis.";
            return false;
        }
        
        if(strlen($name) < 2){
            $this->error = "le nom de la catégorie doit contenir au moins 2 caractères.";
            return false;
        }
        
        if(strlen($name) > 50){
            $this->error = "Le nom de la catégorie ne peut pas dépasser 50 caractères.";
            return false;
        }
        
        $this->error = null;
        return true;
    }
    
    public function createCategory(){
        $id = uniqid('cat_');
        return new Category($id, $this->data[self::NAME_REF]);
    }
}