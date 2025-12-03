<?php
require_once '../model/Annonce.php';
require_once '../model/AnnonceStorage.php';
require_once '../model/CategoryStorage.php';
require_once '../view/AnnonceView.php';

class AnnonceController{
    private $storage;
    private $categoryStorage;
    private $view;

    public function __construct(){
        $this->storage = new AnnonceStorage();
        $this->categoryStorage = new CategoryStorage();
        $this->view = new AnnonceView();
    }

    /**
     * afficher le formulaire
     */
    public function showCreateForm(){
        $categories = $this->categoryStorage->getAll();
        $this->view->displayAnnonceForm($categories);
    }

    /**
     * traitement du formulaire
     */
    public function createAnnonce(){
        if(!isset($_SESSION['user'])){
            header("Location: index.php?action=login");
            exit;
        }
        $user = $_SESSION['user'];

        $errors = [];
        if (empty($_POST['title']) || strlen($_POST['title']) < 5 || strlen($_POST['title']) > 30)
            $errors[] = "Le titre doit faire entre 5 et 30 caractères.";
        if (empty($_POST['description']) || strlen($_POST['description']) < 5 || strlen($_POST['description']) > 200)
            $errors[] = "La description doit faire entre 5 et 200 caractères.";
        if (!isset($_POST['price']) || !is_numeric($_POST['price']))
            $errors[] = "Le prix doit être un nombre.";
        if (!isset($_POST['delivery']) || !in_array($_POST['delivery'], ['postal', 'hand']))
            $errors[] = "Mode de livraison invalide.";
        if (!isset($_POST['category']))
            $errors[] = "Catégorie obligatoire.";

        if (count($errors) > 0) {
            $categories = $this->categoryStorage->getAll();
            $this->view->displayAnnonceForm($categories, $errors);
            return;
        }

        $annonce = new Annonce(
            $user['id'],
            $_POST['category'],
            $_POST['title'],
            $_POST['description'],
            $_POST['price'],
            $_POST['delivery']
        );
        $annonceId = $this->storage->add($annonce);

        // upload des photos 
        if(!empty($_FILES["photos"]["name"][0])){
            $count = min(count($_FILES['photos']['count']), 5);
            for($i = 0; $i < $count; $i++){
                if($_FILES['photos']['error'][$i] === UPLOAD_ERR_OK){
                    if($_FILES['photos']['size'][$i] <= 200 * 1024){
                        $filename = uniqid() . ".jpg";
                        move_uploaded_file($_FILES['photos']['tmp_name'][$i], "uploads/" . $filename);
                        $this->storage->addPhoto($annonceId, $filename);
                    }
                }
            }
        }
        header("Location: index.php?action=annonce&id=$annonceId");
    }

    /**
     * afficher une annonce
     */
    public function showAnnonce($id){
        $annonce = $this->storage->getAnnonce($id);
        $photos = $this->storage->getPhotos($id);

        if(!annonce){
            $this->view->displayError("Annonce introuvable.");
            return;
        }
        $this->view->displayAnnonce($annonce, $photos);
    }

    /**
     * lister par categorie
     */
    public function listByCategory($catId){
        $annonces = $this->storage->listAvailableByCategory($catId);
        $category = $this->categoryStorage->getById($catId);
        $this->view->displayAnnoncesByCategory($category, $annonces);
    }
    
}