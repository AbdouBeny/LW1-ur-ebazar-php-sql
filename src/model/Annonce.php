<?php

class Annonce{
    private $id;
    private $userId;
    private $categoryId;
    private $title;
    private $description;
    private $price;
    private $delivery;
    private $status;

    public function __construct($userId, $categoryId, $title, $description, $price, $delivery){
        $this->userId = $userId;
        $this->categoryId = $categoryId;
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->delivery = $delivery;
        $this->status = "available";
    }

    public function getId(){
        return $this->id;
    }
    public function getUserId(){
        return $this->userId;
    }
    public function getCategoryId(){
        return $this->categoryId;
    }
    public function getTitle(){
        return $this->title;
    }
    public function getDescription(){
        return $this->description;
    }
    public function getPrice(){
        return $this->price;
    }
    public function getDelivery(){
        return $this->delivery;
    }
    public function getStatus(){
        return $this->status;
    }

    public function setId($id){
        $this->id = $id;
    }
}