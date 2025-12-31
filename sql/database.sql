/**********************************
    Creation de la base
***********************************/

CREATE DATABASE BuyMatch;
USE BuyMatch;

/*********************************
    Creation Du Tableau
**********************************/

/* 1 - users */

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('acheteur','organisateur','admin') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

/* 2 - Match */

CREATE TABLE matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    organisateur_id INT NOT NULL,
    equipe1 VARCHAR(100) NOT NULL,
    equipe2 VARCHAR(100) NOT NULL,
    logo_equipe1 VARCHAR(255),
    logo_equipe2 VARCHAR(255),
    date_heure DATETIME NOT NULL,
    lieu VARCHAR(150) NOT NULL,
    duree INT DEFAULT 90,
    nb_places_total INT CHECK (nb_places_total <= 2000),
    statut ENUM('en_attente','valide','refuse') DEFAULT 'en_attente',
    note_moyenne DECIMAL(3,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    constraint fk_user FOREIGN KEY (organisateur_id) REFERENCES users(id)
);

/* 3 - Categorie */

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prix DECIMAL(8,2) NOT NULL,
    nb_places INT NOT NULL,
    constraint fk_Match FOREIGN KEY (match_id) REFERENCES matches(id)
);

/* 4 - billets */  

CREATE TABLE billets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    match_id INT NOT NULL,
    categorie_id INT NOT NULL,
    numero_place INT NOT NULL,
    prix DECIMAL(8,2) NOT NULL,
    qr_code VARCHAR(255),
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    constraint fk_users FOREIGN KEY (user_id) REFERENCES users(id),
    constraint fk_Match1 FOREIGN KEY (match_id) REFERENCES matches(id),
    constraint fk_categorie FOREIGN KEY (categorie_id) REFERENCES categories(id)
);

/* 5 - Commantaires */

CREATE TABLE commentaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    match_id INT NOT NULL,
    contenu TEXT NOT NULL,
    note INT CHECK (note BETWEEN 1 AND 5),
    date_commentaire DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (match_id) REFERENCES matches(id)
);

/* 6 - vue sql : Statistiques par match */

CREATE VIEW vue_stats_match AS
SELECT
    m.id AS match_id,
    m.equipe1,
    m.equipe2,
    COUNT(b.id) AS billets_vendus,
    IFNULL(SUM(b.prix), 0) AS chiffre_affaires
FROM matches m
LEFT JOIN billets b ON m.id = b.match_id
GROUP BY m.id;

/* 7 - Calcul chiffre d’affaires d’un match */

DELIMITER //

CREATE PROCEDURE calculer_chiffre_affaires(IN p_match_id INT)
BEGIN
    SELECT 
        IFNULL(SUM(prix), 0) AS total
    FROM billets
    WHERE match_id = p_match_id;
END //

DELIMITER ;

/*******************************
Insertion De Donnée 
*******************************/

/* 1 - */

INSERT INTO users (nom, email, password, role) VALUES ('Maryem', 'maryem@gmail.com', '0000', 'acheteur'),
                                                      ('Amine', 'amin@gmail.com', '0000', 'organisateur'),
                                                      ('Admin', 'admin@gmail.com', '0000', 'admin');

/* 2 - */

INSERT INTO matches (organisateur_id, equipe1, equipe2, date_heure, lieu, nb_places_total) VALUES (2, 'Raja', 'Wydad', '2026-01-15 20:00:00', 'Stade Mohammed V', 1800);

/* 3 - */

INSERT INTO categories (match_id, nom, prix, nb_places) VALUES (1, 'VIP', 150.00, 200),
                                                                (1, 'Standard', 80.00, 600),
                                                                (1, 'Economy', 40.00, 1000);

/* 4 - */

INSERT INTO billets (user_id, match_id, categorie_id, numero_place, prix, qr_code) VALUES (1, 1, 2, 45, 80.00, 'QR_ABC_123');

/* 5 - */ 

INSERT INTO commentaires (user_id, match_id, contenu, note) VALUES (1, 1, 'Match incroyable ', 5);


