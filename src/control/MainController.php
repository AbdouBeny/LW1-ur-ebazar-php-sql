<?php

class MainController {
    private $view;
    private $categoryStorage;
    private $annonceStorage;

    public function __construct($view, $categoryStorage, $annonceStorage){
        $this->view = $view;
        $this->categoryStorage = $categoryStorage;
        $this->annonceStorage = $annonceStorage;
    }

    public function home(){
        $categories = $this->categoryStorage->getAllWithAnnonceCount();
        $lastAnnonces = $this->annonceStorage->getLastAnnonces(4);

        // ajouter une miniature à chaque annonce
        foreach($lastAnnonces as &$annonce){
            $annonce['photo'] = $this->annonceStorage->getFirstPhoto($annonce['id']) ?? "default.jpg";
        }

        $this->view->renderHome($categories, $lastAnnonces);
    }
}