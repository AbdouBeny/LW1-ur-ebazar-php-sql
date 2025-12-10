<?php
require_once("TokenCSRF.php");

class AdminView{
    protected $router;
    
    public function __construct($router){
        $this->router = $router;
    }
    
    /**
     * génére le html de la page d'administration
     */
    public function renderAdminPage($annonces, $users, $categories, $error = null){
        $html = "<div class='admin-page'>";
        
        if ($error){
            $html .= "<div class='form-error'>" . htmlspecialchars($error) . "</div>";
        }
        
        // gestion des catégories
        $html .= "<section class='admin-section'>";
        $html .= "<h2>Gestion des catégories</h2>";
        
        // formulaire d'ajout
        $html .= "<div class='admin-form'>";
        $html .= "<h3>Ajouter une catégorie</h3>";
        $html .= "<form action='" . $this->router->getCategoryCreateURL() . "' method='POST'>";
        $html .= TokenCSRF::field();
        $html .= "<input type='text' name='name' placeholder='Nom de la catégorie' required minlength='2' maxlength='50'>";
        $html .= "<button type='submit' class='btn btn-primary'>Ajouter</button>";
        $html .= "</form>";
        $html .= "</div>";
        
        // lliste des catégories
        $html .= "<div class='categories-list'>";
        $html .= "<h3>Catégories existantes</h3>";
        if (empty($categories)) {
            $html .= "<p>Aucune catégorie.</p>";
        } else {
            $html .= "<table class='admin-table'>";
            $html .= "<thead><tr><th>Nom</th><th>Actions</th></tr></thead>";
            $html .= "<tbody>";
            foreach ($categories as $category) {
                $html .= "<tr>";
                $html .= "<td>" . htmlspecialchars($category->getName()) . "</td>";
                $html .= "<td class='actions'>";
                $html .= "<form action='" . $this->router->getCategoryUpdateURL($category->getId()) . "' method='POST' class='inline-form'>";
                $html .= TokenCSRF::field();
                $html .= "<input type='text' name='name' value='" . htmlspecialchars($category->getName()) . "' required>";
                $html .= "<button type='submit' class='btn btn-small'>Renommer</button>";
                $html .= "</form>";
                $html .= "<form action='" . $this->router->getCategoryDeleteURL($category->getId()) . "' method='POST' class='inline-form'>";
                $html .= "<button type='submit' class='btn btn-danger btn-small' onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')\">Supprimer</button>";
                $html .= "</form>";
                $html .= "</td>";
                $html .= "</tr>";
            }
            $html .= "</tbody>";
            $html .= "</table>";
        }
        $html .= "</div>";
        $html .= "</section>";
        
        // gestion des annonces
        $html .= "<section class='admin-section'>";
        $html .= "<h2>Gestion des annonces</h2>";
        
        if(empty($annonces)){
            $html .= "<p>Aucune annonce.</p>";
        } else {
            $html .= "<table class='admin-table'>";
            $html .= "<thead><tr><th>Titre</th><th>Vendeur</th><th>Prix</th><th>Statut</th><th>Actions</th></tr></thead>";
            $html .= "<tbody>";
            foreach ($annonces as $id => $annonce) {
                $html .= "<tr>";
                $html .= "<td>" . htmlspecialchars($annonce->getTitle()) . "</td>";
                $html .= "<td>" . htmlspecialchars($annonce->getSellerEmail()) . "</td>";
                $html .= "<td>" . htmlspecialchars($annonce->getPriceFormatted()) . "</td>";
                $html .= "<td>" . ($annonce->isSold() ? 'Vendu' : 'En vente') . "</td>";
                $html .= "<td class='actions'>";
                $html .= "<a href='" . $this->router->getAnnonceURL($id) . "' class='btn btn-small'>Voir</a>";
                $html .= "<form action='" . $this->router->getAnnonceDeleteURL($id) . "' method='POST' class='inline-form'>";
                $html .= "<button type='submit' class='btn btn-danger btn-small' onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?')\">Supprimer</button>";
                $html .= "</form>";
                $html .= "</td>";
                $html .= "</tr>";
            }
            $html .= "</tbody>";
            $html .= "</table>";
        }
        $html .= "</section>";
        
        // gestion des utilisateurs
        $html .= "<section class='admin-section'>";
        $html .= "<h2>Gestion des utilisateurs</h2>";
        
        if(empty($users)){
            $html .= "<p>Aucun utilisateur.</p>";
        }else{
            $html .= "<table class='admin-table'>";
            $html .= "<thead><tr><th>Email</th><th>Rôle</th><th>Date d'inscription</th><th>Actions</th></tr></thead>";
            $html .= "<tbody>";
            foreach ($users as $email => $user) {
                $html .= "<tr>";
                $html .= "<td>" . htmlspecialchars($email) . "</td>";
                $html .= "<td>" . htmlspecialchars($user->getRole()) . "</td>";
                $html .= "<td>" . $user->getRegistrationDate()->format('d/m/Y') . "</td>";
                $html .= "<td class='actions'>";
                if ($user->getRole() !== 'admin') {
                    $html .= "<form action='" . $this->router->getUserDeleteURL($email) . "' method='POST' class='inline-form'>";
                    $html .= "<button type='submit' class='btn btn-danger btn-small' onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur et toutes ses annonces ?')\">Supprimer</button>";
                    $html .= "</form>";
                } else {
                    $html .= "<span class='text-muted'>Admin</span>";
                }
                $html .= "</td>";
                $html .= "</tr>";
            }
            $html .= "</tbody>";
            $html .= "</table>";
        }
        $html .= "</section>";
        
        $html .= "</div>";
        return $html;
    }
}