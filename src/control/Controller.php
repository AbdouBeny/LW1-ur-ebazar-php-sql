<?php
require_once("view/View.php");
require_once("model/Annonce.php");
require_once("model/AnnonceBuilder.php");
require_once("model/User.php");
require_once("model/UserBuilder.php");
require_once("model/CategoryBuilder.php");
require_once("model/Achat.php");

class Controller{
    protected $view;
    protected $annonceStorage;
    protected $userStorage;
    protected $categoryStorage;
    protected $achatStorage;
    protected $currentUser;
    
    public function __construct($view, $annonceStorage, $userStorage, $categoryStorage, $achatStorage){
        $this->view = $view;
        $this->annonceStorage = $annonceStorage;
        $this->userStorage = $userStorage;
        $this->categoryStorage = $categoryStorage;
        $this->achatStorage = $achatStorage;
        $this->currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
    }
    
    public function showHome(){
        $categories = $this->categoryStorage->readAll();
        $annonces = $this->annonceStorage->readAllNotSold();
        
        // récupérer les 4 dernieres annonces
        $last = array_slice($annonces, -4, 4, true);
        foreach($categories as $category){
            $countAnnoncesCat[$category->getId()] = $this->categoryStorage->countAnnonces($category->getId());
        }
        $this->view->prepareHomePage($categories, $last, $countAnnoncesCat);
    }
    
    public function showList($categoryId = null){
        if($categoryId){
            $category = $this->categoryStorage->read($categoryId);
            if (!$category){
                $this->view->prepareNotFoundPage("Catégorie non trouvée");
                return;
            }
            $annonces = $this->annonceStorage->readByCategory($categoryId);
            $this->view->prepareCategoryPage($category, $annonces);
        }else{
            $annonces = $this->annonceStorage->readAllNotSold();
            $this->view->prepareListPage($annonces);
        }
    }
    
    public function showAnnonce($id){
        if (empty($id)){
            $this->view->prepareNotFoundPage("Annonce non trouvée");
            return;
        }
        
        $annonce = $this->annonceStorage->read($id);
        if($annonce !== null){
            $category = $this->categoryStorage->read($annonce->getCategoryId());
            $seller = $this->userStorage->read($annonce->getSellerEmail());
            $this->view->prepareAnnoncePage($annonce, $id, $category, $seller);
        }else{
            $this->view->prepareNotFoundPage("Annonce non trouvée");
        }
    }
    
    public function createNewAnnonce(){
        if (!$this->currentUser){
            $_SESSION['redirect_after_login'] = $this->view->router->getAnnonceCreationURL();
            $this->view->router->POSTredirect($this->view->router->getLoginURL(), "Veuillez vous connecter pour déposer une annonce");
            return;
        }
        
        $categories = $this->categoryStorage->readAll();
        $this->view->prepareAnnonceCreationPage($categories);
    }
    
    public function saveNewAnnonce($post, $files){
        if (!$this->currentUser){
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Action non autorisée");
            return;
        }
        
        $builder = new AnnonceBuilder($post, isset($files['photos']) ? $files['photos'] : array());
        
        if($builder->isValid()){
            $annonce = $builder->createAnnonce($this->currentUser->getEmail());
            $id = $this->annonceStorage->create($annonce);
              
            $this->view->router->POSTredirect($this->view->router->getAnnonceURL($id), "Annonce créée avec succès");
        }else{
            $categories = $this->categoryStorage->readAll();
            $this->view->prepareAnnonceCreationPage($categories, $builder);
        }
    }

    public function deletePhotos($photos){
        $uploadDir = 'uploads/annonces/';
        foreach ($photos as $photo) {
            $filePath = $uploadDir . $photo;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
    
    public function deleteAnnonce($id){
        if(!$this->currentUser){
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Action non autorisée");
            return;
        }
        
        $annonce = $this->annonceStorage->read($id);
        if(!$annonce){
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Annonce non trouvée");
            return;
        }
        
        // vérifier les permissions
        if($annonce->getSellerEmail() !== $this->currentUser->getEmail() && !$this->currentUser->isAdmin()) {
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Action non autorisée");
            return;
        }
        
        // vérifier si l'annonce est vendue
        if($annonce->isSold()){
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Impossible de supprimer une annonce vendue");
            return;
        }
        
        // supprimer les photos
        $this->deletePhotos($annonce->getPhotos());
        $uploadDir = 'uploads/annonces/';
        
        // Supprimer l annonce
        $this->annonceStorage->delete($id);
        if($this->currentUser->isAdmin()){
            $this->view->router->POSTredirect($this->view->router->getAdminURL(), "Annonce supprimée avec succès");
        }else{
            $this->view->router->POSTredirect($this->view->router->getUserProfileURL(), "Annonce supprimée avec succès");
        }
    }
    
    public function purchaseAnnonce($id, $post){
        if(!$this->currentUser){
            $_SESSION['redirect_after_login'] = $this->view->router->getAnnonceURL($id);
            $this->view->router->POSTredirect($this->view->router->getLoginURL(), "Veuillez vous connecter pour acheter");
            return;
        }
        
        $annonce = $this->annonceStorage->read($id);
        if(!$annonce){
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Annonce non trouvée");
            return;
        }
        
        if($annonce->isSold()){
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Cette annonce a déjà été vendue");
            return;
        }
        
        if($annonce->getSellerEmail() === $this->currentUser->getEmail()){
            $this->view->router->POSTredirect($this->view->router->getAnnonceURL($id), "vous ne pouvez pas acheter votre propre annonce");
            return;
        }
        
        $deliveryMode = isset($post['delivery_mode']) ? $post['delivery_mode'] : '';
        if(!$annonce->acceptsDeliveryMode($deliveryMode)){
            $this->view->router->POSTredirect($this->view->router->getAnnonceURL($id), "Mode de livraison non valide");
            return;
        }
        
        // marquer l annonce comme vendue
        $annonce->setSold(true);
        $this->annonceStorage->update($id, $annonce);
        
        // créer l'achat
        $achatId = uniqid('achat_');
        $achat = new Achat($achatId, $id, $this->currentUser->getEmail(), $annonce->getSellerEmail(), $deliveryMode);
        $this->achatStorage->create($achat);
        
        $this->view->router->POSTredirect($this->view->router->getUserProfileURL(), "Achat confirmé avec succès");
    }
    
    public function confirmReception($achatId){
        if (!$this->currentUser){
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Action non autorisée");
            return;
        }
        
        $achat = $this->achatStorage->read($achatId);
        if (!$achat){
            $this->view->router->POSTredirect($this->view->router->getUserProfileURL(), "Achat non trouvé");
            return;
        }
        
        if($achat->getBuyerEmail() !== $this->currentUser->getEmail()){
            $this->view->router->POSTredirect($this->view->router->getUserProfileURL(), "Action non autorisée");
            return;
        }
        
        $achat->setReceived(true);
        $this->achatStorage->update($achatId, $achat);
        
        $this->view->router->POSTredirect($this->view->router->getUserProfileURL(), "Réception confirmée");
    }

}