<?php
require_once '../model/CategoryStorage.php';
require_once '../view/AdminView.php';

class AdminController{
    private $categoryStorage;
    private $view;

    public function __construct(){
        $this->categoryStorage = new CategoryStorage();
        $this->view = new AdminView();
    }

    /**
     * page de gestion des catégories
     */
    public function categoryList(){
        $categories = $this->categoryStorage->getAll();
        $this->view->showCategoryList($categories);
    }

    /**
     * forumulaire d'ajout d'une catégorie
     */
    public function addCategoryForm(){
        $this->view->showAddCategoryForm();
    }

    /**
     * traitement de l'ajout d'une catégorie
     */
    public function addCategory(){
        $name = trim($_POST['name'] ?? '');
        if($name === ''){
            $this->view->showAddCategoryForm(["nom est vide"]);
            return;
        }
        if($this->categoryStorage->exists($name)){
            $this->view->showAddCategoryForm(["la catégorie existe déjo"]);
            return;
        }
        $cat = new Category($name);
        $this->categoryStorage->add($cat);
        $this->view->showMessage("catégorie ajoutée.");
    }
}