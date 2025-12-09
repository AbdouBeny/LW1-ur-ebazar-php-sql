DROP DATABASE IF EXISTS projet;
CREATE DATABASE projet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projet;


-- la table des utilisateurs
CREATE TABLE users (
    email VARCHAR(255) PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- table des catégories
CREATE TABLE categories (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- table des annonces
CREATE TABLE annonces (
    id VARCHAR(100) PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price INT NOT NULL,
    category_id VARCHAR(50) NOT NULL,
    seller_email VARCHAR(255) NOT NULL,
    photos JSON,
    delivery_modes JSON NOT NULL,
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sold BOOLEAN DEFAULT FALSE,

    CONSTRAINT fk_annonces_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    CONSTRAINT fk_annonces_seller   FOREIGN KEY (seller_email)  REFERENCES users(email)    ON DELETE CASCADE
);

-- table des achats
CREATE TABLE achats (
    id VARCHAR(100) PRIMARY KEY,
    annonce_id VARCHAR(100) NOT NULL,
    buyer_email VARCHAR(255) NOT NULL,
    seller_email VARCHAR(255) NOT NULL,
    delivery_mode ENUM('poste','remise') NOT NULL,
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    received BOOLEAN DEFAULT FALSE,

    CONSTRAINT fk_achats_annonce FOREIGN KEY (annonce_id)  REFERENCES annonces(id) ON DELETE CASCADE,
    CONSTRAINT fk_achats_buyer   FOREIGN KEY (buyer_email)  REFERENCES users(email)   ON DELETE CASCADE,
    CONSTRAINT fk_achats_seller  FOREIGN KEY (seller_email) REFERENCES users(email)   ON DELETE CASCADE
);
