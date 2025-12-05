<?php
require_once 'Database.php';
require_once 'User.php';


class UserStorage{
    private $pdo;

    public function __construct(){
        $this->pdo = Database::getInstance();
    }

    /**
     * ajouter un nouvel user
     * @param User $user
     * @return bool succés ou échec
     */
    public function addUser(User $user){
        $sql = "INSERT INTO users (email, password, role) values (:email, :password, :role)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':password', hash('sha256', $user->getPassword()));
        $stmt->bindValue(':role', $user->getRole());
        return $stmt->execute();
    }

    /**
     * verifier les identifiants pour la connexion
     * @param string $email
     * @param string $password
     * @return User ou null
     */
    public function checkLogin($email, $password){
        $sql = "SELECT * FROM users where email = :email AND password = :password";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', hash('sha256', $password));
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if($data){
            $user = new User($data['email'], $data['password'], $data['role']);
            $user->setId($data['id']);
            return $user;
        }
        return null;
    }

    /**
     * vérifier si un email existe déja
     * @param string $email
     * @return bool
     */
    public function emailExists($email){
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * supprimer un utilisateur
     * @param int $id
     */
    public function delete($id){
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

}