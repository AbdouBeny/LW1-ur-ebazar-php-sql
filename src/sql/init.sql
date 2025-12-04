-- la table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- table des catégories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- table des annonces
CREATE TABLE IF NOT EXISTS annonces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(30) NOT NULL,
    description VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) DEFAULT 0,
    delivery ENUM('postal','hand') NOT NULL,
    status ENUM('available','sold') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- latable des photos
CREATE TABLE IF NOT EXISTS photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    annonce_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (annonce_id) REFERENCES annonces(id) ON DELETE CASCADE
);

-- Creation d'un admin par défaut
INSERT INTO users (email, password, role) VALUES (
    'admin@ebazar.com',
    SHA2('admin123', 256),
    'admin'
);

-- on ajoute des colonnes pour stocker l'acheteur et la date d'achat 
ALTER TABLE annonces
  ADD buyer_id INT NULL AFTER status,
  ADD sold_at TIMESTAMP NULL AFTER buyer_id,
  ADD CONSTRAINT fk_annonces_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE SET NULL;

