<?php
require_once("TokenCSRF.php");

class UserView {
    protected $router;
    
    public function __construct($router){
        $this->router = $router;
    }
    
    public function renderHomePage($categories, $lastAnnonces, $countAnnoncesCat){
        $html = "<div class='home-page'>";
        
        // catégories
        $html .= "<section class='categories-section'>";
        $html .= "<h2>Catégories</h2>";
        $html .= "<div class='categories-grid'>";
        foreach ($categories as $category) {
            $count = $countAnnoncesCat[$category->getId()];
            $html .= "<a href='" . $this->router->getCategoryURL($category->getId()) . "' class='category-card'>";
            $html .= "<h3>" . htmlspecialchars($category->getName()) . "</h3>";
            $html .= "<p>" . $count . " annonce(s)</p>";
            $html .= "</a>";
        }
        $html .= "</div>";
        $html .= "</section>";
        
        // dernières annonces
        $html .= "<section class='last-annonces'>";
        $html .= "<h2>Dernières annonces</h2>";
        if (empty($lastAnnonces)) {
            $html .= "<p>Aucune annonce pour le moment.</p>";
        } else {
            $html .= "<div class='annonces-grid'>";
            foreach ($lastAnnonces as $id => $a) {
                $html .= $this->renderAnnonceCard($a, $id);
            }
            $html .= "</div>";
        }
        $html .= "</section>";
        
        $html .= "</div>";
        return $html;
    }
    
    public function renderListPage($annonces){
        if(empty($annonces)){
            return "<p class='no-results'>Aucune annonce disponible.</p>";
        }
        
        $html = "<div class='annonces-list'>";
        foreach ($annonces as $id => $a) {
            $html .= $this->renderAnnonceRow($a, $id);
        }
        $html .= "</div>";
        return $html;
    }
    
    public function renderCategoryPage($category, $annonces, $currentPage = 1, $totalPages = 1){
        $html = "<div class='category-page'>";
        
        if(empty($annonces)){
            $html .= "<p class='no-results'>aucune annonce dans cette catégorie</p>";
        }else{
            $html .= "<div class='annonces-list'>";
            foreach ($annonces as $id => $a) {
                $html .= $this->renderAnnonceRow($a, $id);
            }
            $html .= "</div>";
            
            // pagination
            if($totalPages > 1){
                $html .= "<div class='pagination'>";
                if ($currentPage > 1) {
                    $html .= "<a href='?action=liste&category=" . urlencode($category->getId()) . "&page=" . ($currentPage-1) . "' class='page-link'>Précédent</a> ";
                }
                for ($i = 1; $i <= $totalPages; $i++) {
                    if ($i == $currentPage) {
                        $html .= "<span class='current-page'>$i</span> ";
                    } else {
                        $html .= "<a href='?action=liste&category=" . urlencode($category->getId()) . "&page=$i' class='page-link'>$i</a> ";
                    }
                }
                if ($currentPage < $totalPages) {
                    $html .= "<a href='?action=liste&category=" . urlencode($category->getId()) . "&page=" . ($currentPage+1) . "' class='page-link'>Suivant</a>";
                }
                $html .= "</div>";
            }
        }
        
        $html .= "</div>";
        return $html;
    }
    
    public function renderAnnoncePage($annonce, $id, $category, $seller, $isLoggedIn){
        $html = "<div class='annonce-detail'>";
        
        // photos
        $html .= "<div class='annonce-photos'>";
        $photos = $annonce->getPhotos();
        if (!empty($photos)) {
            $html .= "<div class='main-photo'>";
            $html .= "<img src='uploads/annonces/" . htmlspecialchars($photos[0]) . "' alt='" . htmlspecialchars($annonce->getTitle()) . "' id='mainPhoto'>";
            $html .= "</div>";
            
            if (count($photos) > 1){
                $html .= "<div class='thumbnails'>";
                foreach ($photos as $index => $photo) {
                    $active = $index === 0 ? 'active' : '';
                    $html .= "<img src='uploads/annonces/" . htmlspecialchars($photo) . "' 
                            alt='Photo " . ($index + 1) . "' 
                            class='thumbnail $active'
                            data-index='" . $index . "'
                            onclick='changeMainPhoto(this, \"" . htmlspecialchars($photo) . "\")'>";
                }
                $html .= "</div>";
            }
        } else {
            $html .= "<div class='no-photo'>Pas de photo disponible</div>";
        }
        $html .= "</div>";
        
        // informations
        $html .= "<div class='annonce-info'>";
        $html .= "<h2>" . htmlspecialchars($annonce->getTitle()) . "</h2>";
        $html .= "<div class='price'>" . htmlspecialchars($annonce->getPriceFormatted()) . "</div>";
        $html .= "<div class='meta'>";
        $html .= "<span>Catégorie : " . htmlspecialchars($category ? $category->getName() : 'Inconnue') . "</span>";
        $html .= "<span>Vendeur : " . htmlspecialchars($seller ? $seller->getEmail() : 'Inconnu') . "</span>";
        $html .= "<span>Date : " . $annonce->getCreatedDate()->format('d/m/Y') . "</span>";
        $html .= "</div>";
        
        $html .= "<div class='description'>";
        $html .= "<h3>Description</h3>";
        $html .= "<p>" . nl2br(htmlspecialchars($annonce->getDescription())) . "</p>";
        $html .= "</div>";
        
        $html .= "<div class='delivery-modes'>";
        $html .= "<h3>Modes de livraison</h3>";
        $html .= "<ul>";
        foreach ($annonce->getDeliveryModes() as $mode) {
            $html .= "<li>" . htmlspecialchars($mode === 'poste' ? 'Envoi postal' : 'Remise en main propre') . "</li>";
        }
        $html .= "</ul>";
        $html .= "</div>";
        
        // bouton d'achat
        if($isLoggedIn && !$annonce->isSold()) {
            $html .= "<form action='" . $this->router->getPurchaseURL($id) . "' method='POST' class='purchase-form'>";
            $html .= "<select name='delivery_mode' required>";
            $html .= "<option value=''>Choisir un mode de livraison</option>";
            if (in_array('poste', $annonce->getDeliveryModes())) {
                $html .= "<option value='poste'>Envoi postal</option>";
            }
            if (in_array('remise', $annonce->getDeliveryModes())) {
                $html .= "<option value='remise'>Remise en main propre</option>";
            }
            $html .= "</select>";
            $html .= "<button type='submit' class='btn btn-primary'>Acheter maintenant</button>";
            $html .= "</form>";
        } elseif ($annonce->isSold()) {
            $html .= "<div class='sold-badge'>Vendu</div>";
        } else {
            $html .= "<p><a href='" . $this->router->getLoginURL() . "'>Connectez-vous</a> pour acheter cet article</p>";
        }
        
        $html .= "</div>";
        $html .= "</div>";
        
        $html .= "
        <script>
        function changeMainPhoto(thumbnail, photoSrc){
            const mainPhoto = document.getElementById('mainPhoto');
            if (mainPhoto){
                // on change la source de la photo principale
                mainPhoto.src = 'uploads/annonces/' + photoSrc;
                
                document.querySelectorAll('.thumbnail').forEach(thumb => {
                    thumb.classList.remove('active');
                });
                thumbnail.classList.add('active');
            }
        }
        </script>";
        return $html;
    }
    
    public function renderAnnonceCreationPage($categories, $builder = null){
        $data = $builder ? $builder->getData() : array();
        $error = $builder ? $builder->getError() : null;
        
        $html = "<form action='" . $this->router->getAnnonceSaveURL() . "' method='POST' enctype='multipart/form-data' class='annonce-form'>";
        
        if($error){
            $html .= "<div class='form-error'>" . htmlspecialchars($error) . "</div>";
        }
        $html .= TokenCSRF::field();
        // titre
        $html .= "<div class='form-group'>";
        $html .= "<label for='title'>Titre *</label>";
        $html .= "<input type='text' id='title' name='title' value='" . htmlspecialchars($data['title'] ?? '') . "' required minlength='5' maxlength='30'>";
        $html .= "<small>5 à 30 caractères</small>";
        $html .= "</div>";
        
        // description
        $html .= "<div class='form-group'>";
        $html .= "<label for='description'>Description *</label>";
        $html .= "<textarea id='description' name='description' required minlength='5' maxlength='200'>" . htmlspecialchars($data['description'] ?? '') . "</textarea>";
        $html .= "<small>5 à 200 caractères</small>";
        $html .= "</div>";
        
        // prix
        $html .= "<div class='form-group'>";
        $html .= "<label for='price'>Prix (€) *</label>";
        $html .= "<input type='number' id='price' name='price' value='" . htmlspecialchars($data['price'] ?? '') . "' required min='0' step='0.01'>";
        $html .= "</div>";
        
        // catégorie
        $html .= "<div class='form-group'>";
        $html .= "<label for='category'>Catégorie *</label>";
        $html .= "<select id='category' name='category' required>";
        $html .= "<option value=''>Choisir une catégorie</option>";
        foreach ($categories as $category) {
            $selected = ($data['category'] ?? '') === $category->getId() ? 'selected' : '';
            $html .= "<option value='" . htmlspecialchars($category->getId()) . "' $selected>" . htmlspecialchars($category->getName()) . "</option>";
        }
        $html .= "</select>";
        $html .= "</div>";
        
        // modes de livraison
        $html .= "<div class='form-group'>";
        $html .= "<label>Modes de livraison *</label>";
        $html .= "<div class='checkbox-group'>";
        $html .= "<label><input type='checkbox' name='delivery_poste' value='1' " . (isset($data['delivery_poste']) ? 'checked' : '') . "> Envoi postal</label>";
        $html .= "<label><input type='checkbox' name='delivery_remise' value='1' " . (isset($data['delivery_remise']) ? 'checked' : '') . "> Remise en main propre</label>";
        $html .= "</div>";
        $html .= "</div>";
        
        // photos
        $html .= "<div class='form-group'>";
        $html .= "<label for='photos'>Photos (optionnel)</label>";
        $html .= "<input type='file' id='photos' name='photos[]' accept='image/jpeg,image/jpg' multiple>";
        $html .= "<small>Maximum 5 photos, format JPEG uniquement, 200 Ko max par photo</small>";
        $html .= "</div>";
        
        $html .= "<button type='submit' class='btn btn-primary'>Publier l'annonce</button>";
        $html .= "</form>";
        
        return $html;
    }
    
    public function renderLoginPage($builder = null, $error = null){
        $data = $builder ? $builder->getData() : array();
        
        $html = "<form action='" . $this->router->getLoginSubmitURL() . "' method='POST' class='auth-form'>";
        
        if($error){
            $html .= "<div class='form-error'>" . htmlspecialchars($error) . "</div>";
        }
        $html .= TokenCSRF::field();
        $html .= "<div class='form-group'>";
        $html .= "<label for='email'>Email</label>";
        $html .= "<input type='email' id='email' name='email' value='" . htmlspecialchars($data['email'] ?? '') . "' required>";
        $html .= "</div>";
        
        $html .= "<div class='form-group'>";
        $html .= "<label for='password'>Mot de passe</label>";
        $html .= "<input type='password' id='password' name='password' required>";
        $html .= "</div>";
        
        $html .= "<button type='submit' class='btn btn-primary'>Se connecter</button>";
        $html .= "<p>Pas encore de compte ? <a href='" . $this->router->getRegisterURL() . "'>S'inscrire</a></p>";
        $html .= "</form>";
        
        return $html;
    }
    
    public function renderRegisterPage($builder = null, $error = null){
        $data = $builder ? $builder->getData() : array();
        
        $html = "<form action='" . $this->router->getRegisterSubmitURL() . "' method='POST' class='auth-form'>";
        
        if ($error){
            $html .= "<div class='form-error'>" . htmlspecialchars($error) . "</div>";
        }
        $html .= TokenCSRF::field();
        $html .= "<div class='form-group'>";
        $html .= "<label for='email'>Email</label>";
        $html .= "<input type='email' id='email' name='email' value='" . htmlspecialchars($data['email'] ?? '') . "' required>";
        $html .= "</div>";
        
        $html .= "<div class='form-group'>";
        $html .= "<label for='password'>Mot de passe</label>";
        $html .= "<input type='password' id='password' name='password' required minlength='6'>";
        $html .= "<small>Minimum 6 caractères</small>";
        $html .= "</div>";
        
        $html .= "<div class='form-group'>";
        $html .= "<label for='password_confirm'>Confirmer le mot de passe</label>";
        $html .= "<input type='password' id='password_confirm' name='password_confirm' required>";
        $html .= "</div>";
        
        $html .= "<button type='submit' class='btn btn-primary'>S'inscrire</button>";
        $html .= "<p>Déjà un compte ? <a href='" . $this->router->getLoginURL() . "'>Se connecter</a></p>";
        $html .= "</form>";
        
        return $html;
    }
    
    public function renderProfilePage($user, $myAnnonces, $achats, $ventes, $achatsAnnonces, $ventesAnnonces){
        $html = "<div class='profile-page'>";
        
        $html .= "<div class='user-info'>";
        $html .= "<h2>Mon profil</h2>";
        $html .= "<p><strong>Email :</strong> " . htmlspecialchars($user->getEmail()) . "</p>";
        $html .= "<p><strong>Membre depuis :</strong> " . $user->getRegistrationDate()->format('d/m/Y') . "</p>";
        $html .= "</div>";
        
        if(!$user->isAdmin()){
            // Mes annonces en vente
            $html .= "<section class='profile-section'>";
            $html .= "<h3>Mes annonces en vente</h3>";
            if (empty($myAnnonces)) {
                $html .= "<p>Aucune annonce en vente.</p>";
            } else {
                $html .= "<div class='annonces-grid'>";
                foreach ($myAnnonces as $id => $annonce) {
                    if (!$annonce->isSold()) {
                        $html .= $this->renderAnnonceCard($annonce, $id, true);
                    }
                }
                $html .= "</div>";
            }
            $html .= "</section>";
            
            // Mes ventes
            $html .= "<section class='profile-section'>";
            $html .= "<h3>Mes ventes</h3>";
            if (empty($ventes) || empty($ventesAnnonces)) {
                $html .= "<p>Aucune vente effectuée.</p>";
            } else {
                $html .= "<div class='achats-list'>";
                foreach ($ventes as $achatId => $achat) {
                    $html .= $this->renderAnnonceCard($ventesAnnonces[$achatId], $achat->getAnnonceId(), false);
                    $html .= $this->renderAchatRow($achat, $achatId, false);
                }
                $html .= "</div>";
            }
            $html .= "</section>";
            
            // Mes achats
            $html .= "<section class='profile-section'>";
            $html .= "<h3>Mes achats</h3>";
            if (empty($achats)) {
                $html .= "<p>Aucun achat effectué.</p>";
            } else {
                $html .= "<div class='achats-list'>";
                foreach ($achats as $achatId => $achat) {
                    $html .= $this->renderAnnonceCard($achatsAnnonces[$achatId], $achat->getAnnonceId(), false);
                    $html .= $this->renderAchatRow($achat, $achatId, true);
                }
                $html .= "</div>";
            }
            $html .= "</section>";
            
        }else{
            $html .= "<div class='admin-notice'>";
            $html .= "<p>En tant qu'administrateur, vous ne pouvez pas créer d'annonces ni effectuer d'achats</p>";
            $html .= "<p>utiliser le menu <strong>Administration</strong> pour gérer la plateform";
            $html .= "</div>";
        }
        $html .= "</div>";
        return $html;
    }
    
    protected function renderAnnonceCard($annonce, $id, $showDelete = false){
        if(!$annonce){
            return "<div class='annonce-card error'>Annonce introuvable</div>";
        }

        $html = "<div class='annonce-card'>";
        $html .= "<a href='" . $this->router->getAnnonceURL($id) . "'>";
        if ($annonce->getFirstPhotoUrl()) {
            $html .= "<img src='" . htmlspecialchars($annonce->getFirstPhotoUrl()) . "' alt='' class='annonce-thumb'>";
        } else {
            $html .= "<div class='annonce-thumb no-photo'>Pas de photo</div>";
        }
        $html .= "<h3>" . htmlspecialchars($annonce->getTitle()) . "</h3>";
        $html .= "<div class='annonce-price'>" . htmlspecialchars($annonce->getPriceFormatted()) . "</div>";
        $html .= "</a>";
        
        if ($showDelete && !$annonce->isSold()) {
            $html .= "<form action='" . $this->router->getAnnonceDeleteURL($id) . "' method='POST' class='delete-form'>";
            $html .= TokenCSRF::field();
            $html .= "<button type='submit' class='btn btn-danger btn-small' onclick=\"return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?')\">Supprimer</button>";
            $html .= "</form>";
        }
        
        if ($annonce->isSold()) {
            $html .= "<div class='sold-overlay'>Vendu</div>";
        }
        
        $html .= "</div>";
        return $html;
    }
    
    protected function renderAnnonceRow($annonce, $id) {
        $html = "<div class='annonce-row'>";
        $html .= "<a href='" . $this->router->getAnnonceURL($id) . "' class='annonce-row-link'>";
        if ($annonce->getFirstPhotoUrl()) {
            $html .= "<img src='" . htmlspecialchars($annonce->getFirstPhotoUrl()) . "' alt='' class='annonce-thumb'>";
        } else {
            $html .= "<div class='annonce-thumb no-photo'>Pas de photo</div>";
        }
        $html .= "<div class='annonce-row-info'>";
        $html .= "<h3>" . htmlspecialchars($annonce->getTitle()) . "</h3>";
        $html .= "<p>" . htmlspecialchars($annonce->getShortDescription(100)) . "</p>";
        $html .= "</div>";
        $html .= "<div class='annonce-row-price'>" . htmlspecialchars($annonce->getPriceFormatted()) . "</div>";
        $html .= "</a>";
        $html .= "</div>";
        return $html;
    }
    
    protected function renderAchatRow($achat, $achatId, $isBuyer) {
        $html = "<div class='achat-row'>";
        $html .= "<div class='achat-info'>";
        $html .= "<p><strong>Date d'achat :</strong> " . $achat->getPurchaseDate()->format('d/m/Y H:i') . "</p>";
        $html .= "<p><strong>Mode de livraison :</strong> " . htmlspecialchars($achat->getDeliveryMode() === 'poste' ? 'Envoi postal' : 'Remise en main propre') . "</p>";
        
        if ($isBuyer) {
            $html .= "<p><strong>Statut :</strong> " . ($achat->isReceived() ? 'Reçu' : 'En attente de réception') . "</p>";
            
            if (!$achat->isReceived()) {
                $html .= "<form action='" . $this->router->getConfirmReceptionURL($achatId) . "' method='POST'>";
                $html .= TokenCSRF::field();
                $html .= "<button type='submit' class='btn btn-success btn-small' onclick=\"return confirm('Confirmez-vous la réception de cet article ?')\">Confirmer la réception</button>";
                $html .= "</form>";
            }
        }
        
        $html .= "</div>";
        $html .= "</div>";
        return $html;
    }
    
}