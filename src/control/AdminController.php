<?php
require_once("Controller.php");


class AdminController extends Controller{
    
    /**
     * affiche la page d'administration
     */
    public function showAdmin(){
        if (!$this->currentUser || !$this->currentUser->isAdmin()){
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "accès réservé aux administrateurs");
            return;
        }
        
        $annonces = $this->annonceStorage->readAll();
        $users = $this->userStorage->readAll();
        $categories = $this->categoryStorage->readAll();
        
        $this->view->prepareAdminPage($annonces, $users, $categories);
    }
    
    /**
     * supprime un utilisateur et toutes ses annonces
     */
    public function deleteUser($email){
        if (!$this->currentUser || !$this->currentUser->isAdmin()){
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Action non autorisée");
            return;
        }
        
        if($email === $this->currentUser->getEmail()){
            $this->view->router->POSTredirect($this->view->router->getAdminURL(), "vous ne pouvez pas supprimer votre propre compte");
            return;
        }
        
        // supprimer les annonces de l'utilisateur
        $userAnnonces = $this->annonceStorage->readBySeller($email);
        foreach ($userAnnonces as $id => $annonce){
            $this->deletePhotos($annonce->getPhotos());
            $this->annonceStorage->delete($id);
        }
        
        // supprimer l'utilisateur
        $this->userStorage->delete($email);
        
        $this->view->router->POSTredirect($this->view->router->getAdminURL(), "utilisateur supprimé avec succès");
    }
    
    /**
     * crée une nouvelle catégorie
     */
    public function createCategory($post){
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Action non autorisée");
            return;
        }
        
        $builder = new CategoryBuilder($post);
        
        if($builder->isValid()){
            $category = $builder->createCategory();
            $this->categoryStorage->create($category);
            
            $this->view->router->POSTredirect($this->view->router->getAdminURL(), "catégorie créée avec succès");
        }else{
            $annonces = $this->annonceStorage->readAll();
            $users = $this->userStorage->readAll();
            $categories = $this->categoryStorage->readAll();
            $this->view->prepareAdminPage($annonces, $users, $categories, $builder->getError());
        }
    }
    
    /**
     * met à jour le nom d'une catégorie
     */
    public function updateCategory($id, $post){
        if (!$this->currentUser || !$this->currentUser->isAdmin()){
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Action non autorisée");
            return;
        }
        
        $category = $this->categoryStorage->read($id);
        if(!$category){
            $this->view->router->POSTredirect($this->view->router->getAdminURL(), "Catégorie non trouvée");
            return;
        }
        
        $name = isset($post['name']) ? trim($post['name']) : '';
        if(empty($name)){
            $this->view->router->POSTredirect($this->view->router->getAdminURL(), "Le nom de la catégorie est requis");
            return;
        }
        
        $category->setName($name);
        $this->categoryStorage->update($id, $category);
        
        $this->view->router->POSTredirect($this->view->router->getAdminURL(), "Catégorie mise à jour avec succès");
    }
    
    /**
     * supprime une catégorie
     */
    public function deleteCategory($id){
        if (!$this->currentUser || !$this->currentUser->isAdmin()){
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Action non autorisée");
            return;
        }
        
        // vérifier s il y a des annonces dans cette categorie
        $annonces = $this->annonceStorage->readByCategory($id);
        if (!empty($annonces)){
            $this->view->router->POSTredirect($this->view->router->getAdminURL(), "impossible de supprimer une catégorie contenant des annonces");
            return;
        }
        
        $this->categoryStorage->delete($id);
        $this->view->router->POSTredirect($this->view->router->getAdminURL(), "Catégorie supprimée avec succès");
    }
    
}