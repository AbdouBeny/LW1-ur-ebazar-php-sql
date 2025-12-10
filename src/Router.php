<?php
require_once("control/Controller.php");
require_once("control/UserController.php");
require_once("control/AdminController.php");
require_once("view/View.php");
require_once("TokenCSRF.php");


class Router{
    public function main($annonceStorage = null, $userStorage =null, $categoryStorage = null, $achatStorage = null){
        if (session_status() === PHP_SESSION_NONE){
            session_start();
        }

        // generer un token csrf si inexistant
        if(!isset($_SESSION['csrf_token'])){
            TokenCSRF::generate();
        }
        // validation csrf pour les req POST
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->validateCSRF();
        }

        // Feedback 
        $feedback = null;
        if (isset($_SESSION['feedback'])){
            $feedback = $_SESSION['feedback'];
            unset($_SESSION['feedback']);
        }

        $view = new View($this, $feedback);
        $controller = new Controller($view, $annonceStorage, $userStorage, $categoryStorage, $achatStorage);
        $userController = new UserController($view, $annonceStorage, $userStorage, $categoryStorage, $achatStorage);
        $adminController = new AdminController($view, $annonceStorage, $userStorage, $categoryStorage, $achatStorage);

        $action = isset($_GET['action']) ? $_GET['action'] : null;
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $categoryId = isset($_GET['category']) ? $_GET['category'] : null;

        // les routes principales
        if($action === 'liste'){
            $controller->showList($categoryId);
        }elseif($action === 'nouveau'){
            $controller->createNewAnnonce();
        }elseif($action === 'sauverNouveau'){
            $controller->saveNewAnnonce($_POST, $_FILES);
        }elseif($action === 'supprimer' && $id){
            $controller->deleteAnnonce($id);
        }elseif($action === 'acheter' && $id){
            $controller->purchaseAnnonce($id, $_POST);
        }elseif($action === 'confirmer' && $id){
            $controller->confirmReception($id);
            
        // les routes utilisateur
        }elseif($action === 'connexion'){
            $userController->showLogin();
        }elseif($action === 'connexionPost'){
            $userController->login($_POST);
        }elseif($action === 'inscription'){
            $userController->showRegister();
        }elseif($action === 'inscriptionPost'){
            $userController->register($_POST);
        }elseif($action === 'deconnexion'){
            $userController->logout();
        }elseif($action === 'profil'){
            $userController->showProfile();
            
        // les routes admin
        }elseif($action === 'admin'){
            $adminController->showAdmin();
        }elseif($action === 'supprimerUtilisateur' && isset($_GET['email'])){
            $adminController->deleteUser($_GET['email']);
        }elseif($action === 'creerCategorie'){
            $adminController->createCategory($_POST);
        }elseif($action === 'modifierCategorie' && $id){
            $adminController->updateCategory($id, $_POST);
        }elseif($action === 'supprimerCategorie' && $id){
            $adminController->deleteCategory($id);
            
        // les routes par défaut
        }elseif ($id){
            $controller->showAnnonce($id);
        }else{
            $controller->showHome();
        }

        $view->render();
    }

    protected function scriptName(){
        return htmlspecialchars(basename($_SERVER['PHP_SELF']));
    }

    // URLs principales
    public function getHomeURL(){
        return $this->scriptName();
    }

    public function getListURL($categoryId = null){
        $url = $this->scriptName() . "?action=liste";
        if ($categoryId) {
            $url .= "&category=" . urlencode($categoryId);
        }
        return $url;
    }

    public function getCategoryURL($categoryId){
        return $this->getListURL($categoryId);
    }

    public function getAnnonceURL($id){
        return $this->scriptName() . "?id=" . urlencode($id);
    }

    public function getAnnonceCreationURL(){
        return $this->scriptName() . "?action=nouveau";
    }

    public function getAnnonceSaveURL(){
        return $this->scriptName() . "?action=sauverNouveau";
    }

    public function getAnnonceDeleteURL($id){
        return $this->scriptName() . "?action=supprimer&id=" . urlencode($id);
    }

    public function getPurchaseURL($id){
        return $this->scriptName() . "?action=acheter&id=" . urlencode($id);
    }

    // URLs utilisateur
    public function getLoginURL(){
        return $this->scriptName() . "?action=connexion";
    }

    public function getLoginSubmitURL(){
        return $this->scriptName() . "?action=connexionPost";
    }

    public function getRegisterURL(){
        return $this->scriptName() . "?action=inscription";
    }

    public function getRegisterSubmitURL(){
        return $this->scriptName() . "?action=inscriptionPost";
    }

    public function getLogoutURL(){
        return $this->scriptName() . "?action=deconnexion";
    }

    public function getUserProfileURL(){
        return $this->scriptName() . "?action=profil";
    }

    public function getConfirmReceptionURL($achatId){
        return $this->scriptName() . "?action=confirmer&id=" . urlencode($achatId);
    }

    // URLs admin
    public function getAdminURL(){
        return $this->scriptName() . "?action=admin";
    }

    public function getUserDeleteURL($email){
        return $this->scriptName() . "?action=supprimerUtilisateur&email=" . urlencode($email);
    }

    public function getCategoryCreateURL(){
        return $this->scriptName() . "?action=creerCategorie";
    }

    public function getCategoryUpdateURL($categoryId){
        return $this->scriptName() . "?action=modifierCategorie&id=" . urlencode($categoryId);
    }

    public function getCategoryDeleteURL($categoryId){
        return $this->scriptName() . "?action=supprimerCategorie&id=" . urlencode($categoryId);
    }

    public function POSTredirect($url, $feedback = null){
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if($feedback !== null){
            $_SESSION['feedback'] = $feedback;
        }
        header("HTTP/1.1 303 See Other");
        header("Location: " . $url);
        exit;
    }

    private function validateCSRF(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
            
            if(!TokenCSRF::validate($token)){
                $this->POSTredirect($this->getHomeURL(), "Erreur de sécurité. Veuillez réessayer.");
                exit;
            }
        }
    }

    
}