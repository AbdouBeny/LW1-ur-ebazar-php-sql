<?php
require_once '../model/UserStorage.php';
require_once '../model/User.php';
require_once '../view/UserView.php';


class UserController{
    private $storage;
    private $view;

    public function __construct(){
        $this->storage = new UserStorage();
        $this->view = new UserView();
    }

    /**
     * afficher le formulaire d'inscription
     */
    public function registerForm(){
        $this->view->showRegisterForm();
    }

    /**
     * traiter les données de l'inscription
     */
    public function register(){
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $errors = [];
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errors[] = "email invalide.";
        }
        if (strlen($password) < 6) {
            $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
        }
        if ($password !== $passwordConfirm) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
        if($this->storage->emailExists($email)){
            $errors[] = "cet email est déja utilisé.";
        }

        if(!empty($errors)){
            $this->view->showRegisterForm($errors, $email);
            return;
        }

        // si tout est ok, on passe à la creation de l'utilisateur
        $user = new User($email, $password);
        $this->view->addUser($user);
        $this->view->showMessage("inscription réussie ! vous pouvez maintenant vous connecter.");
    }

    /**
     * traiter les données de la connexion
     */
    public function login(){
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->storage->checkLogin($email, $password);
        if($user){
            // on lance une sesssion
            session_start();
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_email'] = $user->getEmail();
            $_SESSION['user_role'] = $user->getRole();
            $this->view->showMessage("connexion réussie ! bienvenue ." . $user->getEmail());
        } else{
            $this->view->showLoginForm(["identifiants incorrects"], $email);
        }
    }

    /**
     * déconnexion
     */
    public function logout(){
        session_start();
        session_destroy();
        $this->view->showMessage("vous etes déconnecté.");
    }
}