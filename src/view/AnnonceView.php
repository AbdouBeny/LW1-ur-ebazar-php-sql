<?php

class AnnonceView{

    public function displayAnnonceForm($categories, $errors = []){
        echo "<h2>Déposer une annonce</h2>";
        if(!empty($errors)){
            echo "<div style='color:red;'>";
            foreach($errors as $e){
                echo "<p>".$e."</p>";
            }
            echo "</div>";
        }
        echo "
        <form method='POST' enctype='multipart/form-data' action='index.php?action=saveAnnonce'>
            <label>Catégorie :</label>
            <select name='category'>";
                foreach($categories as $c){
                    echo "<option value='{$c['id']}'>{$c['name']}</option>";
                }
                echo "
            </select><br><br>

            <label>Titre :</label>
            <input type='text' name='title' required><br><br>

            <label>Description :</label>
            <textarea name='description' required></textarea><br><br>

            <label>Prix :</label>
            <input type='number' step='0.01' name='price'><br><br>

            <label>Mode de livraison :</label>
            <select name='delivery'>
                <option value='postal'>Envoi postal</option>
                <option value='hand'>Remise en main propre</option>
            </select><br><br>

            <label>Photos (max 5, .jpg, 200k):</label>
            <input type='file' name='photos[]' multiple accept='image/jpeg'><br><br>

            <button type='submit'>Créer l'annonce</button>
        </form>
        ";
    }

    public function displayAnnonce($annonce, $photos){
        echo "<h2>{$annonce['title']}</h2>";
        echo "<p>{$annonce['description']}</p>";
        echo "<p>Prix : {$annonce['price']} €</p>";
        echo "<p>Livraison : {$annonce['delivery']}</p>";

        echo "<h3>Photos :</h3>";
        foreach($photos as $p){
            echo "<img src='uploads/$p' width='200'><br>";
        }
    }

    public function displayAnnoncesByCategory($category, $annonces){
        echo "<h2>Catégorie: {$category['name']}</h2>";
        foreach($annonces as $a){
            echo "<div style='border:1px solid #ccc; padding:10px; margin:5px'>";
            echo "<h3>{$a['title']}</h3>";
            echo "<p>{$a['price']}</p>";
            echo "<a href='index.php?action=annonce&id={$a['id']}'>Voir</a>";
            echo "</div>";
        }
    }

    public function displayError($msg){
        echo "<p style='color:red;'>$msg</p>";
    }
}