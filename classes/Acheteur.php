<?php

require_once "User.php";

class Acheteur extends User{

    protected $db;

    public function __construct($id, $nom, $email){
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
            SELECT b.*, m.equipe1, m.equipe2, m.date_heure
            FROM billets b
            JOIN matches m ON b.match_id = m.id
            WHERE b.user_id = ?
            ORDER BY b.date_achat DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }

    /* ------------------ AVIS ------------------ */

    public function ajouterAvis($matchId, $note, $contenu)
    {
        $stmt = $this->db->prepare("
            INSERT INTO commentaires (user_id, match_id, note, contenu)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([
            $this->id,
            $matchId,
            $note,
            $contenu
        ]);
    }
}
