<?php

require_once "User.php";

class Acheteur extends User{

    protected $db;

    public function __construct($id, $nom, $email,$password,$role,$estActif){
        parent::__construct($id, $nom, $email,$password,'acheteur');
        $this->db = Database::connect();
    }

    /* ------------------   MATCHS  ------------------ */

    public static function listerMatchsDisponibles(){

        $stmt =  Database::connect()->query("
            SELECT * 
            FROM matches
            WHERE statut = 'valide'
              AND date_heure > NOW()
            ORDER BY date_heure ASC
        ");
        return $stmt->fetchAll();
        return ;
    }

    /* ------------------ BILLETS ------------------ */

    public function billetsAchetes(){

        $stmt = $this->db->prepare("
            SELECT b.*, m.equipe1, m.equipe2,m.lieu, m.date_heure,c.nom AS categorie
            FROM billets b
            JOIN matches m ON b.match_id = m.id
            JOIN categories c ON b.categorie_id = c.id
            WHERE b.user_id = ?
            ORDER BY b.date_achat DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
    
    
    /*------------ Recuperer Match Par ID ---------------- */

    public function getMatchById(int $matchId){

        $stmt = $this->db->prepare("
            SELECT id, equipe1, equipe2, lieu, date_heure,statut
            FROM matches
            WHERE id = ? AND statut = 'valide'
        ");
        $stmt->execute([$matchId]);
        return $stmt->fetch();
    }

    /*-------------- Recuperer categorie d'un match -----------*/

    public static function getCategoriesMatch(int $matchId): array{

        $stmt = Database::connect()->prepare("
            SELECT id, nom, prix,nb_places    
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
            WHERE user_id = ? AND match_id = ?
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
            SELECT prix, nb_places
            FROM categories 
            WHERE id = ? AND match_id = ?
        ");
        $stmt->execute([$categorieId, $matchId]);
        $categorie = $stmt->fetch();

        if (!$categorie || $categorie['nb_places'] <= 0) {
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
                (user_id, match_id, categorie_id, numero_place, prix, qr_code)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$this->id,$matchId,$categorieId,$numeroPlace,$categorie['prix'],$qrToken]);
            $ticketId = $this->db->lastInsertId();
            /* Décrément places */
            $stmt = $this->db->prepare("
                UPDATE categories
                SET nb_places = nb_places - 1
                WHERE id = ?
            ");
            $stmt->execute([$categorieId]);

            $this->db->commit();

            return [
                    'id' => (int)$ticketId,
                    'match_id' => $matchId,
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

    /*----------- Match Est Terminee --------- */

    public function matchEstTermine(int $matchId): bool {

        $stmt = $this->db->prepare("
            SELECT statut FROM matches WHERE id = ?
        ");
        $stmt->execute([$matchId]);
        $match = $stmt->fetch();

        return $match && $match['status'] === 'termine';
    }

    public function aDejaCommenter(int $matchId): bool {
    $stmt = $this->db->prepare("
        SELECT id FROM avis_matchs
        WHERE user_id = ? AND match_id = ?
    ");
    $stmt->execute([$this->id, $matchId]);
    return (bool) $stmt->fetch();
}


public function ajouterAvis(int $matchId, int $note, string $commentaire): void {

    if (!$this->matchEstTermine($matchId)) {
        throw new Exception("Le match n'est pas encore terminé.");
    }

    if ($this->aDejaCommenter($matchId)) {
        throw new Exception("Vous avez déjà laissé un avis pour ce match.");
    }

    $stmt = $this->db->prepare("
        INSERT INTO avis_matchs (user_id, match_id, note, commentaire)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$this->id, $matchId, $note, $commentaire]);
}
/* -------- Avis & Commentaires -------- */

public function getAvisMatch(int $matchId): array
{
    $stmt = $this->db->prepare("
        SELECT c.note, c.contenu, c.date_commentaire , u.nom
        FROM commentaires c
        JOIN users u ON c.user_id = u.id
        WHERE c.match_id = ?
        ORDER BY c.date_commentaire  DESC
    ");
    $stmt->execute([$matchId]);
    return $stmt->fetchAll();
}

public function getStatsAvis(int $matchId): array
{
    $db = Database::connect();

    $stmt = $db->prepare("
        SELECT 
            COUNT(*) AS total,
            ROUND(AVG(note), 1) AS moyenne
        FROM avis
        WHERE match_id = ?
    ");
    $stmt->execute([$matchId]);

    $stats = $stmt->fetch();

    return [
        'total'   => (int) $stats['total'],
        'moyenne' => $stats['moyenne'] ?? 0
    ];
}

public function aAcheteBillet(int $matchId): bool
{
    $stmt = $this->db->prepare("
        SELECT COUNT(*) 
        FROM billets 
        WHERE user_id = ? AND match_id = ?
    ");
    $stmt->execute([$this->id, $matchId]);

    return $stmt->fetchColumn() > 0;
}


public function envoyerBilletParEmail(array $ticket, array $match): void
{
    require_once __DIR__ . '/../services/Mailer.php';

    $mailer = new Mailer();

    ob_start();
   require_once __DIR__ . '/../templates/emails/ticket.php';
    $html = ob_get_clean();

    $mailer->send(
        $this->email,
        "🎟 Votre billet - {$match['equipe1']} vs {$match['equipe2']}",
        $html
    );
}










}
