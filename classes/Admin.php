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

    public function refuserMatch(int $matchId , ?string $raison =null): bool {

        $stmt = $this->db->prepare("
            update matches
            set statut ='refuse' ,raison_refus =?
            where id=? and statut ='en_attente'
        ");
        $stmt->execute([$raison,$matchId]);
        return $stmt->rowCount()>0;
    }

    // 4- Lister tous les utilisateurs

    public function listerUtilisateurs(): array {

        $stmt = $this->db->prepare("
            select id,nom,email,role,is_active,date_creation
            from users
            order by date_creation desc
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 5 - desactiver/activer Utilisateur 

    public function desactiverUtilisateur(int $userId): bool{

        $stmt = $this->db->prepare("
            UPDATE users
            SET is_active = 0
            WHERE id = ?
        ");

        return $stmt->execute([$userId]);
    }

    public function activerUtilisateur(int $userId): bool{

        $stmt = $this->db->prepare("
            UPDATE users
            SET is_active = 1
            WHERE id = ?
        ");

        return $stmt->execute([$userId]);
    }

    // 6 - changer role d'un utilisateur 

    public function changerRole(int $userId, string $nouveauRole): bool{

        $rolesAutorises = ['acheteur', 'organisateur'];

        if (!in_array($nouveauRole, $rolesAutorises)) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE users
            SET role = ?
            WHERE id = ?
        ");

        return $stmt->execute([$nouveauRole, $userId]);
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
