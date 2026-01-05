<?php

require_once "User.php";
require_once __DIR__ . "/../config/database.php";

class Admin extends User {

    protected PDO $db;

    public function __construct(int $id,string $nom,string $email,?string $password = null,bool $estActif = true) {
        parent::__construct($id, $nom, $email, $password ?? '', 'admin', $estActif);
        $this->db = Database::connect();
    }

    /*********************************
     * Les Methodes de Administrateur
    **********************************/

    // 1 -  Lister les matchs en attente

    public function listerMatchsEnAttente(): array {

        $stmt = $this->db->prepare("
            select m.id,m.equipe1,m.equipe2,m.logo_equipe1,m.logo_equipe2,m.date_heure,m.lieu,u.nom as organisateur 
            from matches m
            join users u on m.organisateur_id=u.id
            where m.statut ='en_attente'
            order by m.date_heure asc
        ");
        $stmt->execute();

        return $stmt->fetchAll;
    }

    // 2- Valider un match
    public function validerMatch(int $matchId): bool {

        $stmt = $this->pd->prepare("
            update matches 
            set statut ='valide'
            where id = ? and statut='en_attente'
        ");
        $stmt->execute([$matchId]);
        return $stmt->rowCount()>0;
    }

    // 3- Refuser / supprimer un match
    public function refuserMatch(int $matchId): bool {
        return false;
    }

    // 4- Lister tous les utilisateurs
    public function listerUtilisateurs(): array {
        return [];
    }

    // 5 - Activer / désactiver un utilisateur
    public function changerStatutUtilisateur(int $userId, bool $actif): bool {
        return false;
    }

    // 6 -  Supprimer un commentaire
    public function supprimerCommentaire(int $commentId): bool {
        return false;
    }


    // 7 - Statistiques globales
    public function statistiquesGlobales(): array {
        return [];
    }
}
