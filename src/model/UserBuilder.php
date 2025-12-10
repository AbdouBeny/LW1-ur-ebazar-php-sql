<?php

/**
 * builder pour creer et valider les objets User
 * gère la validation des données de formulaire pour l'inscription
 */
class UserBuilder{
    const EMAIL_REF = 'email';
    const PASSWORD_REF = 'password';
    const PASSWORD_CONFIRM_REF = 'password_confirm';
    
    protected $data;
    protected $error;
    
    public function __construct($data = array()){
        $this->data = $data;
        $this->error = null;
    }
    // getters
    public function getData() { return $this->data; }
    public function getError() { return $this->error; }
    
    /**
     * valide les données du formulaire d'inscription
     * @return bool vrai si sont validés
     */
    public function isValid(){
        $email = isset($this->data[self::EMAIL_REF]) ? trim($this->data[self::EMAIL_REF]) : '';
        $password = isset($this->data[self::PASSWORD_REF]) ? $this->data[self::PASSWORD_REF] : '';
        $passwordConfirm = isset($this->data[self::PASSWORD_CONFIRM_REF]) ? $this->data[self::PASSWORD_CONFIRM_REF] : '';
        
        if(empty($email)){
            $this->error = "L'email est requis.";
            return false;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $this->error = "l'email n'est pas valide.";
            return false;
        }
        if(empty($password)){
            $this->error = "le mot de passe est requis.";
            return false;
        }
        if(strlen($password) < 6) {
            $this->error = "le mot de passe doit contenir au moins 6 caractères.";
            return false;
        }
        if($password !== $passwordConfirm){
            $this->error = "Les mots de passe ne correspondent pas.";
            return false;
        }
        
        $this->error = null;
        return true;
    }
    
    /**
     * cree un nouvel objet User à partir des données validées
     * @return User l'user créé
     */
    public function createUser(){
        $passwordHash = password_hash($this->data[self::PASSWORD_REF], PASSWORD_DEFAULT);
        return new User($this->data[self::EMAIL_REF], $passwordHash);
    }
    
    /**
     * met à jour un utilsateur 
     * @param User $user 
     * @return User utilisateur mis à jour
     */
    public function updateUser($user){
        if(isset($this->data[self::PASSWORD_REF]) && !empty($this->data[self::PASSWORD_REF])) {
            $passwordHash = password_hash($this->data[self::PASSWORD_REF], PASSWORD_DEFAULT);
            $user->setPasswordHash($passwordHash);
        }
        return $user;
    }
    
}