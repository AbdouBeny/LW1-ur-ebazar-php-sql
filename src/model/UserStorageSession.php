<?php
require_once("UserStorage.php");
require_once("User.php");

/**
 * Cette classe utilise la session PHP comme système de stockage temporaire pour les annonces.
 * Elle est principalement destinée aux tests
 * elle est actuellement désactivée dans site.php
 */
class UserStorageSession implements UserStorage{
    protected $key = 'users';
    
    public function __construct(){
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }
        
        if (!isset($_SESSION[$this->key]) || !is_array($_SESSION[$this->key])) {
            $_SESSION[$this->key] = array(
                'admin@example.com' => array(
                    'email' => 'admin@example.com',
                    'passwordHash' => password_hash('admin123', PASSWORD_DEFAULT),
                    'role' => 'admin',
                    'registrationDate' => new DateTime()
                ),
                'vendeur@example.com' => array(
                    'email' => 'vendeur@example.com',
                    'passwordHash' => password_hash('vendeur123', PASSWORD_DEFAULT),
                    'role' => 'user',
                    'registrationDate' => new DateTime()
                )
            );
        }
    }
    
    protected function arrayToUser($arr){
        $user = new User($arr['email'], $arr['passwordHash'], $arr['role']);
        if(isset($arr['registrationDate']) && $arr['registrationDate'] instanceof DateTime){
            $user->setRegistrationDate($arr['registrationDate']);
        }
        return $user;
    }
    
    public function read($email){
        if(isset($_SESSION[$this->key][$email])){
            return $this->arrayToUser($_SESSION[$this->key][$email]);
        }
        return null;
    }
    
    public function readAll(){
        $out = array();
        foreach ($_SESSION[$this->key] as $email => $arr){
            $out[$email] = $this->arrayToUser($arr);
        }
        return $out;
    }
    
    public function create(User $u){
        $email = $u->getEmail();
        if (isset($_SESSION[$this->key][$email])) {
            return false;
        }
        
        $_SESSION[$this->key][$email] = array(
            'email' => $u->getEmail(),
            'passwordHash' => $u->getPasswordHash(),
            'role' => $u->getRole(),
            'registrationDate' => $u->getRegistrationDate()
        );
        return true;
    }
    
    public function update($email, User $u){
        if (!isset($_SESSION[$this->key][$email])){
            return false;
        }
        
        $_SESSION[$this->key][$email] = array(
            'email' => $u->getEmail(),
            'passwordHash' => $u->getPasswordHash(),
            'role' => $u->getRole(),
            'registrationDate' => $u->getRegistrationDate()
        );
        return true;
    }
    
    public function delete($email){
        if (!isset($_SESSION[$this->key][$email])) {
            return false;
        }
        unset($_SESSION[$this->key][$email]);
        return true;
    }
    
    public function exists($email){
        return isset($_SESSION[$this->key][$email]);
    }
    
    public function checkAuth($email, $password){
        if (!isset($_SESSION[$this->key][$email])){
            return false;
        }
        
        return password_verify($password, $_SESSION[$this->key][$email]['passwordHash']);
    }
}