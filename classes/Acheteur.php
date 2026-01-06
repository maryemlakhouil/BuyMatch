<?php

require_once "User.php";

class Acheteur extends User{

    protected $db;

    public function __construct(int $id, string $nom, string $email) {
        parent::__construct($id, $nom, $email, 'acheteur');
        $this->db = Database::connect();
    }

    /* ------------------   MATCHS  ------------------ */

    public function listerMatchsDisponibles(){

        $stmt = $this->db->query("
            SELECT *
            FROM matches
            WHERE statut = 'valide'
              AND date_heure > NOW()
            ORDER BY date_heure ASC
        ");
        return $stmt->fetchAll();
    }

    /* ------------------ BILLETS ------------------ */

    public function billetsAchetes(){

        $stmt = $this->db->prepare("
            SELECT b.*, m.equipe1, m.equipe2,m.lieu, m.date_heure,c.nom AS categorie
            FROM billets b
            JOIN matches m ON b.match_id = m.id
            JOIN categories c ON b.categorie_id = c.id
            WHERE b.acheteur_id = ?
            ORDER BY b.date_achat DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
    
    /* ------------------ AVIS ------------------ */

    public function ajouterAvis($matchId, $note, $contenu){

        $stmt = $this->db->prepare("
            INSERT INTO commentaires (user_id, match_id, note, contenu)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$this->id,$matchId,$note,$contenu]);
    }
    
    /*------------ Recuperer Match Par ID ---------------- */

    public function getMatchById(int $matchId){

        $stmt = $this->db->prepare("
            SELECT id, equipe1, equipe2, lieu, date_heure
            FROM matches
            WHERE id = ? AND statut = 'valide'
        ");
        $stmt->execute([$matchId]);
        return $stmt->fetch();
    }

    /*-------------- Recuperer categorie d'un match -----------*/

    public function getCategoriesMatch(int $matchId): array{

        $stmt = $this->db->prepare("
            SELECT id, nom, prix, places_disponibles    
            FROM categories
            WHERE match_id = ?
            ORDER BY prix ASC 
        ");
        $stmt->execute([$matchId]);
        return $stmt->fetchAll();
    }

    /*-----------Le nombre de billets ---------------*/

    public function nombreBilletsAchetes(int $matchId): int {

        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM billets
            WHERE acheteur_id = ? AND match_id = ?
        ");
        $stmt->execute([$this->id, $matchId]);
        return (int) $stmt->fetchColumn();
    }

    /*------------ Acheter Billets --------------- */

    public function acheterBillet(int $matchId,int $categorieId,int $numeroPlace): array {

        /* Limite billets */
        if ($this->nombreBilletsAchetes($matchId) >= 4) {
            throw new Exception("Limite de 4 billets atteinte");
        }

        /* Vérifier catégorie */

        $stmt = $this->db->prepare("
            SELECT prix, places_disponibles
            FROM categories 
            WHERE id = ? AND match_id = ?
        ");
        $stmt->execute([$categorieId, $matchId]);
        $categorie = $stmt->fetch();

        if (!$categorie || $categorie['places_disponibles'] <= 0) {
            throw new Exception("Catégorie indisponible");
        }

        /* Vérifier place */

        $stmt = $this->db->prepare("

            SELECT COUNT(*) FROM billets
            WHERE match_id = ? AND numero_place = ?
        ");
        $stmt->execute([$matchId, $numeroPlace]);

        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Place déjà réservée");
        }

        /* QR / Identifiant */
        $qrToken = bin2hex(random_bytes(16));

        /* Transaction */
        $this->db->beginTransaction();

        try {
            /* Insertion billet */
            $stmt = $this->db->prepare("
                INSERT INTO billets 
                (acheteur_id, match_id, categorie_id, numero_place, prix, qr_token)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$this->id,$matchId,$categorieId,$numeroPlace,$categorie['prix'],$qrToken]);

            /* Décrément places */
            $stmt = $this->db->prepare("
                UPDATE categories
                SET places_disponibles = places_disponibles - 1
                WHERE id = ?
            ");
            $stmt->execute([$categorieId]);

            $this->db->commit();

            return ['match_id' => $matchId,
                    'categorie_id' => $categorieId,
                    'numero_place' => $numeroPlace,
                    'prix' => $categorie['prix'],
                    'qr_token' => $qrToken
                ];

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }




}
