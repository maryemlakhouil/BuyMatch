<?php
session_start();
require_once BASE_PATH . "/config/database.php";

if (!isset($_SESSION['user_id'])) {
    die("Accès refusé");
}

$ticketId = $_GET['ticket_id'] ?? null;
if (!$ticketId || !is_numeric($ticketId)) {
    die("Billet invalide");
}

$db = Database::connect();

/* Charger billet */
$stmt = $db->prepare("
    SELECT b.*, m.equipe1, m.equipe2, m.date_heure, c.nom AS categorie
    FROM billets b
    JOIN matches m ON b.match_id = m.id
    JOIN categories c ON b.categorie_id = c.id
    WHERE b.id = ? AND b.user_id = ?
");

$stmt->execute([$ticketId, $_SESSION['user_id']]);
$ticket = $stmt->fetch();

if (!$ticket) {
    die("Billet introuvable");
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Billet | BuyMatch</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f3f4f6;
}
.ticket {
    width: 360px;
    margin: 40px auto;
    padding: 20px;
    background: #fff;
    border: 2px dashed #000;
}
h1 {
    text-align: center;
}
.info {
    margin: 10px 0;
}
@media print {
    body {
        background: #fff;
    }
}
</style>
</head>

<body onload="window.print()">

<div class="ticket">
    <h1> BuyMatch</h1>

    <div class="info"><strong>Match :</strong>
        <?= htmlspecialchars($ticket['equipe1']) ?> vs <?= htmlspecialchars($ticket['equipe2']) ?>
    </div>

    <div class="info"><strong>Date :</strong>
        <?= date('d/m/Y H:i', strtotime($ticket['date_heure'])) ?>
    </div>

    <div class="info"><strong>Catégorie :</strong>
        <?= htmlspecialchars($ticket['categorie']) ?>
    </div>

    <div class="info"><strong>Place :</strong>
        #<?= $ticket['numero_place'] ?>
    </div>

    <div class="info">
        <strong>Identifiant :</strong><br>
        BM-<?= $ticketId ?>-<?= $_SESSION['user_id'] ?>
    </div>
</div>

</body>
</html>
