<?php


class User{
    protected $email;
    protected $passwordHash;
    protected $role; 
    protected $registrationDate;
    
    public function __construct($email, $passwordHash, $role = 'user'){
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->registrationDate = new DateTime();
    }
    
    // getters
    public function getEmail(){ return $this->email; }
    public function getPasswordHash(){ return $this->passwordHash; }
    public function getRole(){ return $this->role; }
    public function getRegistrationDate(){ return $this->registrationDate; }
    
    // setters
    public function setPasswordHash($hash){ 
        $this->passwordHash = $hash; 
    }
    public function setRegistrationDate($date){
        $this->registrationDate = $date;
    }

    /**
     * verifie si user est administrateur
     * @return bool vrai si est admin
     */
    public function isAdmin(){ return $this->role === 'admin'; }
    
}