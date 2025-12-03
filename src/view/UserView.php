<?php

class UserView{

    public function showRegisterForm($errors=[], $email=''){
        echo "<h2>Inscription</h2>";
        if(!empty($errors)){
            echo "<ul style='color:red;'>";
            foreach ($errors as $e){
                echo "<li>". $e . "</li>";
            }
            echo "</ul>";
        }
        echo '<form method="POST" action="?action=register">
                Email : <input type="email" name="email" value="'.htmlspecialchars($email).'"><br>
                Mot de passe : <input type="password" name="password"><br>
                Confirmez mot de passe : <input type="password" name="password_confirm"><br>
                <input type="submit" value="inscrire">
              </form>';
        echo '<a href="?action=loginForm">Déjà inscrit ? Connectez-vous</a>';
    }

    public function showLoginForm($errors=[], $email = ''){
        echo "<h2>Connexion</h2>";
        if(!empty($errors)){
            echo "<ul style='color:red;'>";
            foreach ($errors as $e){
                echo "<li>". $e . "</li>";
            }
            echo "</ul>";
        }
        echo '<form method="POST" action="?action=login">
                Email : <input type="email" name="email" value="'.htmlspecialchars($email).'"><br>
                Mot de passe : <input type="password" name="password"><br>
                <input type="submit" value="Se connecter">
              </form>';
        echo '<a href="?action=registerForm">Pas encore inscrit ? Inscrivez-vous</a>';
    }

    public function showMessage($message){
        echo "<p style='color:green;'>".$message."</p>";
        echo '<a href="?action=loginForm">Retour à la connexion</a>';
    }
}