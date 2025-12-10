<?php

/**
 * interface pour le stockage des achats
 * définit les methodes CRUD pour la gestion des ahcats
 */
interface AchatStorage{
    public function read($id);
    public function readAll();
    public function create(Achat $a);
    public function update($id, Achat $a);
    public function delete($id);
    public function findByBuyer($email);
    public function findBySeller($email);
    public function findByAnnonce($annonceId);

    
}