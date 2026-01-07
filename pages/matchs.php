<?php
session_start();

require_once "../config/database.php";
require_once "../classes/Acheteur.php";

/* Sécurité */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'acheteur') {
    header("Location: ../auth/login.php");
    exit;
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
$acheteur = new Acheteur($_SESSION['user_id'], $user['nom'], $user['email']);

/* Matchs disponibles */
$matchs = $acheteur->listerMatchsDisponibles();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Matchs disponibles | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-950 text-gray-100 min-h-screen">

<!-- HEADER -->
<header class="bg-gray-900 border-b border-gray-800">
    <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-indigo-500">BuyMatch</h1>
        <span class="text-gray-400"> <?= htmlspecialchars($user['nom']) ?></span>
    </div>
</header>

<!-- CONTENU -->
<main class="max-w-6xl mx-auto px-6 py-10">

    <h2 class="text-3xl font-bold mb-8">⚽ Matchs disponibles</h2>

    <?php if (empty($matchs)): ?>
        <div class="bg-gray-800 p-6 rounded-xl text-center text-gray-400">
            Aucun match disponible pour le moment.
        </div>
    <?php else: ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <?php foreach ($matchs as $match): ?>
                <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 hover:border-indigo-500 transition">

                    <h3 class="text-xl font-bold mb-2">
                        <?= htmlspecialchars($match['equipe1']) ?>
                        <span class="text-gray-400">vs</span>
                        <?= htmlspecialchars($match['equipe2']) ?>
                    </h3>

                    <p class="text-gray-400 text-sm mb-4">
                        📍 <?= htmlspecialchars($match['lieu']) ?><br>
                        🕒 <?= date('d/m/Y H:i', strtotime($match['date_heure'])) ?>
                    </p>

                    <a href="match_details.php?id=<?= $match['id'] ?>"
                       class="block text-center bg-indigo-600 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        Voir détails
                    </a>
                </div>
            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</main>

</body>
</html>
