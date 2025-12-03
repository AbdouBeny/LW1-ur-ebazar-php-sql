<?php

class User{
    private $id;
    private $email;
    private $password;
    private $role;

    public function __construct($email, $password, $role = 'user'){
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    public function getId(){
        return $this->id;
    }
    public function getEmail(){
        return $this->email;
    }
    public function getPassword(){
        return $this->password;
    }
    public function getRole(){
        return $this->role;
    }

    public function setId($id){
        $this->id = $id;
    }
}