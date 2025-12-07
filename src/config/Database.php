<?php
require_once '/config.php';

class Database{
    private static $instance = null;
    private $pdo;


    private function __construct(){
        try{
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e){
            echo "Connexion à MySQL impossible : ", $e->getMessage();
        }
    }
    public static function getInstance(){
        if(self::$instance === null){
            self::$instance = new Database();
        }
        return self::$instance->pdo;
    }
}