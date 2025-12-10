<?php
require_once("UserView.php");
require_once("AdminView.php");


class View{
    public $title = '';
    public $content = '';
    public $menu = array();
    public $feedback = null;
    public $router;
    protected $userView;
    protected $adminView;
    
    public function __construct($router, $feedback = null){
        $this->router = $router;
        $this->feedback = $feedback;
        $this->userView = new UserView($router);
        $this->adminView = new AdminView($router);
        
        $this->buildMenu();
    }
    
    protected function buildMenu(){
        $this->menu = array(
            array('url' => $this->router->getHomeURL(), 'text' => 'Accueil'),
            array('url' => $this->router->getListURL(), 'text' => 'Toutes les annonces'),
        );
        
        if (isset($_SESSION['user'])) {
            if(!$_SESSION['user']->isAdmin()){
                $this->menu[] = array('url' => $this->router->getAnnonceCreationURL(), 'text' => 'Déposer une annonce');
            }
            
            $this->menu[] = array('url' => $this->router->getUserProfileURL(), 'text' => 'Mon compte');
            
            if ($_SESSION['user']->isAdmin()) {
                $this->menu[] = array('url' => $this->router->getAdminURL(), 'text' => 'Administration');
            }
            
            $this->menu[] = array('url' => $this->router->getLogoutURL(), 'text' => 'Déconnexion');
        } else {
            $this->menu[] = array('url' => $this->router->getLoginURL(), 'text' => 'Connexion');
            $this->menu[] = array('url' => $this->router->getRegisterURL(), 'text' => 'Inscription');
        }
    }
    
    public function render() {
        echo "<!doctype html>\n<html lang='fr'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'>";
        echo "<title>" . htmlspecialchars($this->title) . "</title>";
        echo "<link rel='stylesheet' href='src/css/style.css'>";
        echo "</head><body>";
        echo "<header>";
        echo "<nav class='main-nav'>";
        foreach ($this->menu as $item) {
            echo "<a href='" . htmlspecialchars($item['url']) . "' class='nav-link'>" . htmlspecialchars($item['text']) . "</a>";
        }
        echo "</nav>";
        if ($this->feedback) {
            echo "<div class='feedback " . (strpos($this->feedback, 'succès') !== false ? 'feedback-success' : 'feedback-error') . "'>" . htmlspecialchars($this->feedback) . "</div>";
        }
        echo "</header>";
        echo "<main class='container'>";
        echo "<h1>" . htmlspecialchars($this->title) . "</h1>";
        echo $this->content;
        echo "</main>";
        echo "<footer class='footer'>";
        echo "<p>Plate-forme petites annonces - " . date('Y') . "</p>";
        echo "<p>BENYOUCEF ABDEREZAK 🪶</p>";
        echo "</footer>";
        echo "</body></html>";
    }
    
    public function prepareHomePage($categories, $lastAnnonces, $countAnnoncesCat){
        $this->title = "Accueil - Plateforme de petites annonces";
        $this->content = $this->userView->renderHomePage($categories, $lastAnnonces, $countAnnoncesCat);
    }
    
    public function prepareListPage($annonces) {
        $this->title = "Toutes les annonces";
        $this->content = $this->userView->renderListPage($annonces);
    }
    
    public function prepareCategoryPage($category, $annonces, $currentPage, $totalPages){
        $this->title = "Catégorie : " . $category->getName();
        $this->content = $this->userView->renderCategoryPage($category, $annonces, $currentPage, $totalPages);
    }
    
    public function prepareAnnoncePage($annonce, $id, $category, $seller){
        $this->title = $annonce->getTitle();
        $this->content = $this->userView->renderAnnoncePage($annonce, $id, $category, $seller, isset($_SESSION['user']));
    }
    
    public function prepareAnnonceCreationPage($categories, $builder = null) {
        $this->title = "Déposer une nouvelle annonce";
        $this->content = $this->userView->renderAnnonceCreationPage($categories, $builder);
    }
    
    public function prepareLoginPage($builder = null, $error = null) {
        $this->title = "Connexion";
        $this->content = $this->userView->renderLoginPage($builder, $error);
    }
    
    public function prepareRegisterPage($builder = null, $error = null){
        $this->title = "Inscription";
        $this->content = $this->userView->renderRegisterPage($builder, $error);
    }
    
    public function prepareProfilePage($user, $myAnnonces, $achats, $ventes, $achatsAnnonces, $ventesAnnonces){
        $this->title = "Mon compte";
        $this->content = $this->userView->renderProfilePage($user, $myAnnonces, $achats, $ventes, $achatsAnnonces, $ventesAnnonces);
    }
    
    public function prepareAdminPage($annonces, $users, $categories, $error = null) {
        $this->title = "Administration";
        $this->content = $this->adminView->renderAdminPage($annonces, $users, $categories, $error);
    }
    
    public function prepareNotFoundPage($message){
        $this->title = "Page non trouvée";
        $this->content = "<div class='not-found'><h2>" . htmlspecialchars($message) . "</h2><p><a href='" . $this->router->getHomeURL() . "'>Retour à l'accueil</a></p></div>";
    }
}