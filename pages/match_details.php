<?php
session_start();

require_once "../config/database.php";
require_once "../classes/Acheteur.php";

/* Sécurité */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'acheteur') {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Match non spécifié");
}

$db = Database::connect();

/* Infos acheteur */
$stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("Acheteur introuvable");
}

/* Objet Acheteur */
$acheteur = new Acheteur(
    $_SESSION['user_id'],
    $user['nom'],
    $user['email']
);

/* Match */
$match = $acheteur->getMatch((int) $_GET['id']);
if (!$match) {
    die("Match introuvable ou non disponible");
}

/* Catégories */
$categories = $acheteur->getCategoriesMatch($match['id']);
