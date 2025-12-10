<?php

/**
 * interface pour le stockage des annonces
 * définit les methodes CRUD pour la gestion des annonces
 */
interface AnnonceStorage{
    public function read($id);
    public function readAll();
    public function create(Annonce $a);
    public function update($id, Annonce $a);
    public function delete($id);
    public function readByCategoryPaginated($categoryId, $page, $perPage);
    public function countByCategory($categoryId);
    public function readAllNotSold();
    public function readBySeller($email);
    public function readByCategory($categoryId);
}

