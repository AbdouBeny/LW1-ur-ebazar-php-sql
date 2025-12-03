<?php

class AdminView{

    public function showCategoryList($categories){
        echo "<h2>Gestion des catégories</h2>";
        echo "<ul>";
        foreach ($categories as $c){
            echo "<li>" . htmlspecialchars($c->getName()) . "</li>";
        }
        echo "</ul>";
        echo '<a href="?action=addCategoryForm">Ajouter une catégorie</a>';
    }

    public function showAddCategoryForm($errors = []){
        echo "<h2>Ajouter une catégorie</h2>";
        if(!empty($errors)){
            echo "<ul style='color:red;'>";
            foreach($errors as $e){
                echo "<li>".$e."</li>";
            }
            echo "</ul>";
        }
        echo '<form method="POST" action="?action=addCategory">
                Nom : <input type="text" name="name">
                <input type="submit" value="Ajouter">
              </form>';
    }

    public function showMessage($msg){
        echo "<p style='color:green;'>$msg</p>";
        echo '<a href="?action=categoryList">Retour</a>';
    }
}