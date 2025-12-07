<?php


interface AnnonceStorage{
    public function read($id);
    public function readAll();
    public function create(Annonce $a);
    public function update($id, Annonce $a);
    public function delete($id);
}

