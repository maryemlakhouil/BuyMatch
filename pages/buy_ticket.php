<?php
session_start();

require_once "../config/database.php";
require_once "../classes/Acheteur.php";

/* Sécurité */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'acheteur') {
    http_response_code(403);
    exit("Accès refusé");
}

if (
    empty($_POST['match_id']) ||
    empty($_POST['categorie_id']) ||
    empty($_POST['numero_place'])
) {
    http_response_code(400);
    exit("Données manquantes");
}

$db = Database::connect();

/* Infos acheteur */
$stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$acheteur = new Acheteur(
    $_SESSION['user_id'],
    $user['nom'],
    $user['email']
);

$matchId     = (int) $_POST['match_id'];
$categorieId = (int) $_POST['categorie_id'];
$place       = (int) $_POST['numero_place'];

/* Achat billet */
try {
    $ticket = $acheteur->acheterBillet(
        $matchId,
        $categorieId,
        $place
    );

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'ticket'  => $ticket
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
