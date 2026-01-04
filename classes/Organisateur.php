<?php
require_once "User.php";
require_once __DIR__ . "/../config/database.php";

class Organisateur extends User {

    private PDO $db;

    public function __construct(int $id,string $nom,string $email,?string $password = null,bool $estActif = true) {
        parent::__construct($id, $nom, $email, $password ?? '', 'organisateur', $estActif);
        $this->db = Database::connect();
    }

    /* Les methodes d'Organisateur  */

    // 1 - Creer Un evenement 

    public function creerMatch(string $equipe1,string $equipe2,?string $logo1,?string $logo2,string $dateHeure,string $lieu,int $duree,int $nbPlaces,array $categories): int|false {

        if ($nbPlaces <= 0 || $nbPlaces > 2000) 
            return false;
        if (count($categories) === 0 || count($categories) > 3) 
            return false;

        $stmt = $this->db->prepare("
            INSERT INTO matches
            (organisateur_id, equipe1, equipe2, logo_equipe1, logo_equipe2,date_heure, lieu, nb_places_total)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([$this->id,$equipe1,$equipe2,$logo1,$logo2,$dateHeure,$lieu,$nbPlaces]);

        $matchId = $this->db->lastInsertId();

        /* Insertion catégories */
        $stmtCat = $this->db->prepare("
            INSERT INTO categories (match_id, nom, prix, nb_places)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($categories as $cat) {
            $stmtCat->execute([$matchId,$cat['nom'],$cat['prix'],$cat['places']]);
        }
        return $matchId;
    }

    // 2 - Statistiques des matchs 

    public function consulterStatistiques(): array {

        $stmt = $this->db->prepare("
            SELECT 
                m.id AS match_id,
                m.equipe1,
                m.equipe2,
                COUNT(b.id) AS billets_vendus,
                IFNULL(SUM(b.prix), 0) AS chiffre_affaires
            FROM matches m
            LEFT JOIN billets b ON m.id = b.match_id
            WHERE m.organisateur_id = ?
            GROUP BY m.id
            ORDER BY m.date_heure DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    // 3 - Avis et commentaires pour un match spécifique

    public function consulterAvis(int $matchId): array {

        $stmt = $this->db->prepare("
            SELECT u.nom, c.contenu, c.note, c.date_commentaire
            FROM commentaires c
            JOIN users u ON c.user_id = u.id
            WHERE c.match_id = ?
            ORDER BY c.date_commentaire DESC
        ");
        $stmt->execute([$matchId]);
        return $stmt->fetchAll();
    }
    
}
