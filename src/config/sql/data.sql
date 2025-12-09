USE projet;

-- insertion des catégories par défaut
INSERT INTO categories (id, name) VALUES
('cat_1', 'Informatique'),
('cat_2', 'Livres'),
('cat_3', 'Sport'),
('cat_4', 'Maison'),
('cat_5', 'Vêtements'),
('cat_6', 'Autres')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- insertion de l'administrateur par défaut (mot de passe: "admin123") et aussi un utilisateur par défault (mot de passe: "vendeur123")
INSERT INTO users (email, password_hash, role, registration_date) VALUES
('admin@example.com', '$2y$10$D/PBLyoW86S9xFvzrqGZU.UG2FwihP.Ehx41//bFCwC6kuwFKkxIm', 'admin', NOW()),
('vendeur@example.com', '$2y$10$CKl/MLcmquW72d8w16LEUuKHHY54To3VXc7qDk4Wa4YpachCck/US', 'user', NOW())
ON DUPLICATE KEY UPDATE 
    password_hash = VALUES(password_hash),
    role = VALUES(role);

-- insertion d'annonces de test
INSERT INTO annonces (id, title, description, price, category_id, seller_email, photos, delivery_modes, created_date, sold) VALUES
('velo-001', 'Vélo de montagne', 'Vélo tout suspendu, très bon état, chaines et freins révisés.', 19900, 'cat_3', 'vendeur@example.com', '["velo1.jpg"]', '["remise", "poste"]', '2024-01-15 10:30:00', 0),
('livre-002', 'Lot de romans policiers', 'Ensemble de 10 romans, état correct.', 1500, 'cat_2', 'vendeur@example.com', '[]', '["poste"]', '2024-01-20 14:00:00', 0),
('ordi-003', 'Ordinateur portable 13"', 'Ultrabook, SSD 256 Go, 8 Go RAM. Batterie ok.', 35000, 'cat_1', 'admin@example.com', '["ordi1.jpg", "ordi2.jpg"]', '["remise"]', '2024-01-25 09:15:00', 0)
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    description = VALUES(description),
    price = VALUES(price),
    category_id = VALUES(category_id),
    seller_email = VALUES(seller_email),
    photos = VALUES(photos),
    delivery_modes = VALUES(delivery_modes),
    sold = VALUES(sold);