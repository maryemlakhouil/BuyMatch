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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du match</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="max-w-4xl mx-auto p-6">

    <!-- Match -->
    <div class="bg-white rounded shadow p-6 mb-6">
        <h1 class="text-3xl font-bold mb-2">
            <?= htmlspecialchars($match['equipe1']) ?> 
            <span class="text-gray-400">vs</span> 
            <?= htmlspecialchars($match['equipe2']) ?>
        </h1>

        <p class="text-gray-600">
             <?= htmlspecialchars($match['lieu']) ?><br>
             <?= date('d/m/Y H:i', strtotime($match['date_heure'])) ?>
        </p>
    </div>

    <!-- Catégories -->
    <div class="bg-white rounded shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Catégories disponibles</h2>

        <?php if (empty($categories)): ?>
            <p class="text-gray-500 italic">Aucune catégorie disponible.</p>
        <?php endif; ?>

        <?php foreach ($categories as $cat): ?>
            <div class="border rounded p-4 mb-4 flex justify-between items-center">
                <div>
                    <p class="font-bold"><?= htmlspecialchars($cat['nom']) ?></p>
                    <p class="text-sm text-gray-600">
                        Prix : <?= number_format($cat['prix'], 2) ?> DH<br>
                        Places restantes : <?= $cat['nb_places'] ?>
                    </p>
                </div>

                <div>
                    <?php if ($cat['nb_places'] > 0): ?>
                        <a href="buy_ticket.php?category=<?= $cat['id'] ?>&match=<?= $match['id'] ?>"
                           class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                            Acheter
                        </a>
                    <?php else: ?>
                        <span class="text-red-500 font-semibold">Complet</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

</body>
</html>
