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

        return $stmt->fetchAll();
    }

    // 2- Valider / refuser un match

    public function changerStatutMatch(int $matchId, string $statut): bool {

        // Sécurité : statut autorisé uniquement
        if (!in_array($statut, ['valide', 'refuse'])) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE matches
            SET statut = ?
            WHERE id = ?
        ");

        return $stmt->execute([$statut, $matchId]);
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

    public function changerStatutUtilisateur(int $userId, bool $statut): bool {

        //  l'admin ne peut pas se désactiver
        if ($userId === $this->id) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE users
            SET est_actif = ?
            WHERE id = ?
        ");

        return $stmt->execute([$statut ? 1 : 0, $userId]);
    }
     // supprimer Un utilisateur 

    public function supprimerUtilisateur(int $userId): bool {

        // l'admin ne peut pas se supprimer
        if ($userId === $this->id) {
            return false;
        }

        $stmt = $this->db->prepare("
            DELETE FROM users
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

    public function supprimerCommentaire(int $commentaireId): bool {
        $stmt = $this->db->prepare("
        delete from commantairses where id = ?");
        return $stmt->execute([$commentaireId]);
    }

    // 7 - Statistiques globales

    public function statistiquesGlobales(): array {

        $stats = [];
        // total d'utilisateurs 
        $stmt = $this->db->query(" SELECT COUNT(*) FROM users");
        $stats['users'] = $stmt->fetchColumn();

        // total des matches 
        $stmt = $this->db->query(" SELECT COUNT(*) FROM matches");
        $stats['matches'] = $stmt->fetchColumn();

        // total billets vendus
        $stmt = $this->db->query("SELECT COUNT(*) FROM billets");
        $stats['billets'] =$stmt->fetchColumn();

        // chiffres affaires 
        $stmt = $this->db->query("SELECT IFNULL(SUM(prix),0) FROM billets");
        $stats['chiffre_affaires'] = $stmt->fetchColumn();

        return $stats;
    }


}
