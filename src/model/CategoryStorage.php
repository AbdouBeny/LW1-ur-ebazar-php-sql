<?php


interface CategoryStorage{
    public function read($id);
    public function readAll();
    public function create(Category $c);
    public function update($id, Category $c);
    public function delete($id);
    public function countAnnonces($categoryId);
    
}