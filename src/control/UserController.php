<?php
require_once("Controller.php");


class UserController extends Controller{

    public function showLogin(){
        $this->view->prepareLoginPage();
    }
    
    public function showRegister(){
        $this->view->prepareRegisterPage();
    }
    
    public function login($post){
        $email = isset($post['email']) ? trim($post['email']) : '';
        $password = isset($post['password']) ? $post['password'] : '';
        
        if(empty($email) || empty($password)){
            $this->view->prepareLoginPage(null, "email et mot de passe requis");
            return;
        }
        
        if($this->userStorage->checkAuth($email, $password)){
            $user = $this->userStorage->read($email);
            $_SESSION['user'] = $user;
            
            // redirection après login
            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : $this->view->router->getHomeURL();
            unset($_SESSION['redirect_after_login']);
            
            $this->view->router->POSTredirect($redirect, "Connexion réussie");
        }else{
            $this->view->prepareLoginPage(null, "Email ou mot de passe incorrect");
        }
    }
    
    public function register($post){
        $builder = new UserBuilder($post);
        
        if($builder->isValid()){
            if($this->userStorage->exists($builder->getData()[UserBuilder::EMAIL_REF])){
                $this->view->prepareRegisterPage($builder, "Cet email est déjà utilisé");
                return;
            }
            
            $user = $builder->createUser();
            $this->userStorage->create($user);
            
            $_SESSION['user'] = $user;
            $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Inscription réussie");
        }else{
            $this->view->prepareRegisterPage($builder, $builder->getError());
        }
    }
    
    public function logout(){
        unset($_SESSION['user']);
        $this->view->router->POSTredirect($this->view->router->getHomeURL(), "Déconnexion réussie");
    }
    
    public function showProfile(){
        if(!$this->currentUser){
            $this->view->router->POSTredirect($this->view->router->getLoginURL(), "Veuillez vous connecter");
            return;
        }
        $achatsAnnonces = array();
        $ventesAnnonces = array();
        $myAnnonces = $this->annonceStorage->readBySeller($this->currentUser->getEmail());
        $achats = $this->achatStorage->findByBuyer($this->currentUser->getEmail());
        foreach($achats as $achatId => $achat){
            $achatsAnnonces[$achatId] = $this->annonceStorage->read($achat->getAnnonceId());
        }
        $ventes = $this->achatStorage->findBySeller($this->currentUser->getEmail());
        foreach($ventes as $venteId => $vente){
            $ventesAnnonces[$venteId] = $this->annonceStorage->read($vente->getAnnonceId());
        }
        $this->view->prepareProfilePage($this->currentUser, $myAnnonces, $achats, $ventes, $achatsAnnonces, $ventesAnnonces);
    }
}