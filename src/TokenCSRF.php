<?php


class TokenCSRF{
    
    public static function generate(){
        if (session_status() === PHP_SESSION_NONE){
            session_start();
        }
        
        // on générer un token aléatoire
        $token = bin2hex(random_bytes(32));

        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    public static function validate($token, $timeout = 3600){
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        // vérifier la correspondance
        if (!hash_equals($_SESSION['csrf_token'], $token)){
            return false;
        }
        // vérifier l'expiration 
        if(isset($_SESSION['csrf_token_time'])){
            if((time() - $_SESSION['csrf_token_time']) > $timeout){
                self::clear();
                return false;
            }
        }
        
        return true;
    }
    
    public static function get(){
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }
        
        if(!isset($_SESSION['csrf_token'])){
            return self::generate();
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function clear(){
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
    }
    
    public static function field(){
        $token = self::get();
        return "<input type='hidden' name='csrf_token' value='" . htmlspecialchars($token) . "'>";
    }
}