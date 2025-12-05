<?php
require_once 'model/CategoryStorage.php';
require_once 'view/AdminView.php';

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

    /**
     * supprimer un utilisateurs
     */
    public function deleteUser($userId){
        if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin'){
            die("accès refusé");
        }
        if(!$userId){
            exit("id manquant");
        }

        $storage = new UserStorage();

        if($storage->delete($userId)){
            echo "utilisateur supprimé.<br>";
            echo '<a href="?action=categoryList">Retour admin</a>';
        }else{
            echo "erreur suppression.";
        }
    }

    /**
     * supprimer une annonce
     */
    public function deleteAnnonce($id){
        if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin'){
            die("accès refusé");
        }
        if(!$id){
            die("id annonce manquant");
        }

        $storage = new AnnonceStorage();

        if($storage->deleteAnnonce($id)){
            echo "annonce supprimée.<br>";
            echo '<a href="?action=categoryList">Retour admin</a>';
        }else{
            echo "erreur suppression annonce.";
        }
    }

    /**
     * formulaire pour renommer une catégorie
     */
    public function renameCategoryForm($id){
        $storage = new CategoryStorage();
        $cat = $storage->getById($id);

        include 'view/admin/renameCategory.php';
    }

    public function renameCategory(){
        $id = $_POST['id'];
        $name = $_POST['name'];

        $storage = new CategoryStorage();

        if ($storage->rename($id, $name)){
            echo "Catégorie renommée.";
        } else {
            echo "Erreur.";
        }
    }



}