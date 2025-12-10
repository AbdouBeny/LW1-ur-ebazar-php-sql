<?php
require_once("UserStorage.php");
require_once("User.php");
require_once("config/Database.php");

/**
 * implemntation SQL du stockage des utilisateurs
 */
class UserStorageSql implements UserStorage{
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance();
    }
    
    /**
     * lit un utilisateur par son email
     * @param string $email 
     * @return User|null si il le trouve
     */
    public function read($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return $this->rowToUser($row);
        }
        return null;
    }
    
    /**
     * lit tous les utilisateurs 
     * @return array tableau d'users indexés par email
     */
    public function readAll() {
        $stmt = $this->pdo->query("SELECT * FROM users");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $users = [];
        foreach ($rows as $row) {
            $users[$row['email']] = $this->rowToUser($row);
        }
        return $users;
    }
    
    /**
     * crée un nouvel utilisateur
     * @param User $u 
     * @return bool succés de l operation
     */
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
    
    /**
     * met à jour un user
     * @param string $email email de user à mettre à jour 
     * @param User $u nouvel objet utilisateur
     * @return bool succés de l'opertaion
     */
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
    
    /**
     * supprime un utilisateur
     * @param string $email l'email de user à supprimer
     * @return bool succés de l'operation
     */
    public function delete($email){
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE email = :email");
        return $stmt->execute(['email' => $email]);
    }
    
    /**
     * verifie si user existe
     * @param string $email email à vérifier
     * @return bool vrai si il existe
     */
    public function exists($email){
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * vérifie les inforamtions d'authenfication
     * @param string $email 
     * @param string $password
     * @return bool vrai si l'auth reussit
     */
    public function checkAuth($email, $password){
        $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $hash = $stmt->fetchColumn();
        
        return password_verify($password, $hash);
    }
    
    /**
     * convertit une ligne de base de données en objet User
     * @param array $row ligne de resultat de la base de données
     * @return User user créé
     */
    private function rowToUser($row){
        $user = new User($row['email'], $row['password_hash'], $row['role']);
        $user->setRegistrationDate(new DateTime($row['registration_date']));

        return $user;
    }
}