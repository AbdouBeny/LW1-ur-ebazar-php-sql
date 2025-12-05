<?php
require_once 'model/Annonce.php';
require_once 'model/AnnonceStorage.php';
require_once 'model/CategoryStorage.php';
require_once 'view/AnnonceView.php';

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
        if(!isset($_SESSION['user_id'])){
            header("Location: index.php?action=login");
            exit;
        }

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
            $_SESSION['user_id'],
            $_POST['category'],
            $_POST['title'],
            $_POST['description'],
            $_POST['price'],
            $_POST['delivery']
        );
        $annonceId = $this->storage->add($annonce);

        // upload des photos 
        if(!empty($_FILES["photos"]["name"][0])){
            $count = min(count($_FILES['photos']['name']), 5);
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

        if(!$annonce){
            $this->view->displayError("Annonce introuvable.");
            return;
        }
        $this->view->displayAnnonce($annonce, $photos);
    }

    /**
     * lister par categorie
     */
    public function listByCategory($catId){
        $category = $this->categoryStorage->getById($catId);
        if (!$category){
            $this->view->displayError("Catégorie introuvable.");
            return;
        }
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

        $limit = 10;
        $offset = ($page - 1) * $limit;

        $annonces = $this->storage->listAvailableByCategoryPaged($catId, $limit, $offset);

        $total = $this->storage->countInCategory($catId);
        $totalPages = ceil($total / $limit);
        foreach ($annonces as &$a){
            $a['photo'] = $this->storage->getFirstPhoto($a['id']);
        }

        $this->view->displayAnnoncesByCategoryPaged(
            $category,
            $annonces,
            $page,
            $totalPages,
            $catId
        );
    }


    /**
     * affichier le formularier d'achat pour une annonce
     */
    public function showBuyForm($id){
        if(!$id){
            $this->view->displayError("ID manquant.");
            return;
        }
        $annonce = $this->storage->getAnnonce($id);
        if(!$annonce){
            $this->view->displayError('annonce introuvable.');
            return;
        }
        if($annonce['status'] !== 'available'){
            $this->view->displayError("cette annonce n'est plus disponible.");
            return;
        }
        if(!isset($_SESSION['user_id'])){
            header("Location: index.php?action=loginForm");
            exit;
        }
        if($annonce['user_id'] == $_SESSION['user_id']){
            $this->view->displayError("Vous ne pouvez pas acheter votre propre annonce.");
            return;
        }
        
        $allowed = [];
        $allowed[] = $annonce['delivery'];
        $this->view->showBuyForm($annonce, $allowed);
    }

    /**
     * traitement de l'achat
     */
    public function buyAnnonce(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->view->displayError("requête invalide.");
            return;
        }

        if(!isset($_SESSION['user_id'])){
            header("Location: index.php?action=loginForm");
            exit;
        }
        $annonceId = $_POST['annonce_id'] ?? null;
        $delivery = $_POST['delivery'] ?? null;
        $buyerId = $_SESSION['user_id'];

        if(!$annonceId || !$delivery){
            $this->view->displayError("données manquantes.");
            return;
        }
        $annonce = $this->storage->getAnnonce($annonceId);
        if(!$annonce){
            $this->view->displayError("annonce introuvable.");
            return;
        }
        if($annonce['status'] !== 'available'){
            $this->view->displayError("annonce déjà vendue.");
            return;
        }
        if($annonce['user_id'] == $buyerId){
            $this->view->displayError("vous ne pouvez pas acheter votre propre annonce.");
            return;
        }
        $accepted = [$annonce['delivery']];
        if(!in_array($delivery, $accepted)){
            $this->view->displayError("mode de livraison non autorisé.");
            return;
        }
        $ok = $this->storage->purchaseAnnonce($annonceId, $buyerId, $delivery);

        if(!$ok){
            $this->view->displayError("impossible d'acheter l'annonce (déjà vendue ?).");
            return;
        }
        header("Location: index.php?action=annonce&id={$annonceId}");
        exit;

    }

    /**
     * afficher le compte de l'utilisateur
     */
    public function myAccount(){
        if(!isset($_SESSION['user_id'])){
            header("Location: index.php?action=loginForm");
            exit;
        }

        $userId = $_SESSION['user_id'];

        $myAnnonces = $this->storage->listByUser($userId);
        $mySold = $this->storage->listSoldByUser($userId);
        $myBought = $this->storage->listBoughtByUser($userId);
        foreach ($myAnnonces as &$a){
            $a['photo'] = $this->storage->getFirstPhoto($a['id']);
        }
        foreach ($mySold as &$a){
            $a['photo'] = $this->storage->getFirstPhoto($a['id']);
        }
        foreach ($myBought as &$a){
            $a['photo'] = $this->storage->getFirstPhoto($a['id']);
        }
        $this->view->displayUserAccount($myAnnonces, $mySold, $myBought);
    }
    /**
     * confirmer la reception 
     */
    public function confirmReception(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->view->displayError("requête invalide.");
            return;
        }
        if(!isset($_SESSION['user_id'])){
            header("Location: index.php?action=loginForm");
            exit;
        }
        $annonceId = $_POST['annonce_id'] ?? null;
        $userId = $_SESSION['user_id'];
        if(!$annonceId){
            $this->view->displayError("id manquant.");
            return;
        }
        $annonce = $this->storage->getAnnonce($annonceId);
        if(!$annonce){
            $this->view->displayError("annonce introuvable.");
            return;
        }
        if($annonce['buyer_id'] != $userId){
            $this->view->displayError("vous n'êtes pas l'acheteur de cette annonce.");
            return;
        }
        $ok = $this->storage->confirmReception($annonceId);

        if(!$ok){
            $this->view->displayError("impossible de confirmer la réception.");
            return;
        }

        header("Location: index.php?action=myAccount");
        exit;
    }

    /**
     * supprimer une annonce 
     */
    public function deleteAnnonce($id){
        if (!isset($_SESSION['user_id'])){
            header("Location: index.php?action=loginForm");
            exit;
        }
        if (!$id){
            $this->view->displayError("iD manquant.");
            return;
        }

        $annonce = $this->storage->getAnnonce($id);

        if(!$annonce){
            $this->view->displayError("annonce introuvable.");
            return;
        }
        if ($annonce['user_id'] != $_SESSION['user_id']){
            $this->view->displayError("vous ne pouvez pas supprimer une annonce qui ne vous appartient pas.");
            return;
        }
        if ($annonce['status'] !== 'available'){
            $this->view->displayError("vous ne pouvez pas supprimer une annonce déjà vendue.");
            return;
        }

        $ok = $this->storage->deleteAnnonce($id);

        if(!$ok){
            $this->view->displayError("erreur lors de la suppression.");
            return;
        }

        header("Location: index.php?action=myAccount");
        exit;
    }


}