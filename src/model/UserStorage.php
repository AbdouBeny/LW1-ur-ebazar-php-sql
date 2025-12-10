<?php

/**
 * interface pour le stockage des utilisateurs 
 * définit les méthodes CRUD pour la gestion des utilisateurs
 */
interface UserStorage{
    public function read($id);
    public function readAll();
    public function create(User $u);
    public function update($id, User $u);
    public function delete($id);
    public function exists($email);
    public function checkAuth($email, $password);
}