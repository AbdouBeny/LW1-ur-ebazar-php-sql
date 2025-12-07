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
    
    public function getEmail(){ return $this->email; }
    public function getPasswordHash(){ return $this->passwordHash; }
    public function getRole(){ return $this->role; }
    public function getRegistrationDate(){ return $this->registrationDate; }
    
    public function isAdmin(){ return $this->role === 'admin'; }
    
    public function setPasswordHash($hash){ 
        $this->passwordHash = $hash; 
    }
    public function setRegistrationDate($date){
        $this->registrationDate = $date;
    }
    
}