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

    // 2 - lister tous les matches 

    public function listerTousLesMatchs(): array{

        $stmt = $this->db->prepare("
            SELECT 
                m.id,m.equipe1,m.equipe2,m.date_heure,m.statut,u.nom AS organisateur
            FROM matches m
            JOIN users u ON m.organisateur_id = u.id
            ORDER BY m.date_heure DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 2- Valider / refuser un match

    public function validerMatch(int $matchId): bool{
        $stmt = $this->db->prepare("
            UPDATE matches
            SET statut = 'valide'
            WHERE id = ?
        ");
        return $stmt->execute([$matchId]);
    }

    public function refuserMatch(int $matchId): bool{
        $stmt = $this->db->prepare("
            UPDATE matches
            SET statut = 'refuse'
            WHERE id = ?
        ");
        return $stmt->execute([$matchId]);
    }

    public function changerStatutMatch(int $matchId, string $statut): bool {

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

        if ($userId === $this->id) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE users
            SET is_active = ?
            WHERE id = ?
        ");

        return $stmt->execute([$userId,$statut ? 1 : 0]);
    }

    // 6 - supprimer Un utilisateur 

    public function supprimerUtilisateur(int $userId): bool {

        if ($userId === $this->id) {
            return false;
        }

        $stmt = $this->db->prepare("
            DELETE FROM users
            WHERE id = ?
        ");

        return $stmt->execute([$userId]);
    }

    // 7 - changer role d'un utilisateur 

    public function changerRoleUtilisateur(int $userId, string $nouveauRole): bool{

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

    // 8 -  Supprimer un commentaire

    public function supprimerCommentaire(int $commentaireId): bool {
        $stmt = $this->db->prepare("
        delete from commantairses where id = ?");
        return $stmt->execute([$commentaireId]);
    }

    // 9 - Statistiques globales
  
    public function statistiquesGlobales(): array {

        $stmt = $this->db->query("
            SELECT 
                (SELECT COUNT(*) FROM matches) AS total_matchs,
                (SELECT COUNT(*) FROM billets) AS total_billets,
                (SELECT IFNULL(SUM(prix),0) FROM billets) AS chiffre_affaires,
                (SELECT COUNT(*) FROM users) AS total_utilisateurs
            ");
        return $stmt->fetch();
    }

    // 10 - lister les commantaires 

    public function listerCommentaires(): array {

        $stmt = $this->db->prepare("
            SELECT c.id, c.contenu, c.note, c.date_commentaire,
                u.nom AS utilisateur, m.equipe1, m.equipe2
            FROM commentaires c
            JOIN users u ON c.user_id = u.id
            JOIN matches m ON c.match_id = m.id
            ORDER BY c.date_commentaire DESC
        ");

        $stmt->execute();
        return $stmt->fetchAll();
    }
    
}
