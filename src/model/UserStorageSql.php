<?php
require_once("UserStorage.php");
require_once("User.php");
require_once("config/Database.php");


class UserStorageSql implements UserStorage{
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance();
    }
    
    public function read($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return $this->rowToUser($row);
        }
        return null;
    }
    
    public function readAll() {
        $stmt = $this->pdo->query("SELECT * FROM users");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $users = [];
        foreach ($rows as $row) {
            $users[$row['email']] = $this->rowToUser($row);
        }
        return $users;
    }
    
    public function create(User $u) {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (email, password_hash, role, registration_date) 
            VALUES (:email, :password_hash, :role, :registration_date)
        ");
        
        return $stmt->execute([
            'email' => $u->getEmail(),
            'password_hash' => $u->getPasswordHash(),
            'role' => $u->getRole(),
            'registration_date' => $u->getRegistrationDate()->format('Y-m-d H:i:s')
        ]);
    }
    
    public function update($email, User $u) {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET email = :new_email, password_hash = :password_hash, role = :role
            WHERE email = :old_email
        ");
        
        return $stmt->execute([
            'new_email' => $u->getEmail(),
            'password_hash' => $u->getPasswordHash(),
            'role' => $u->getRole(),
            'old_email' => $email
        ]);
    }
    
    public function delete($email){
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE email = :email");
        return $stmt->execute(['email' => $email]);
    }
    
    public function exists($email){
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetchColumn() > 0;
    }
    
    public function checkAuth($email, $password){
        $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $hash = $stmt->fetchColumn();
        
        return password_verify($password, $hash);
    }
    
    private function rowToUser($row){
        $user = new User($row['email'], $row['password_hash'], $row['role']);
        $user->setRegistrationDate(new DateTime($row['registration_date']));

        return $user;
    }
}