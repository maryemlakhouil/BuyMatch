<?php
require_once "../config/database.php";
require_once "../classes/Acheteur.php";

$db = Database::connect();
// $acheteur = new Acheteur(0, ,"Visiteur", "");

// Filtre exemple
$lieuFilter = $_GET['lieu'] ?? '';
$equipeFilter = $_GET['equipe'] ?? '';

// Récupérer matchs publiés
$matchs = Acheteur::listerMatchsDisponibles();

// Filtrage simple
if ($lieuFilter) {
    $matchs = array_filter($matchs, fn($m) => stripos($m['lieu'], $lieuFilter) !== false);
}
if ($equipeFilter) {
    $matchs = array_filter($matchs, fn($m) => stripos($m['equipe1'], $equipeFilter) !== false || stripos($m['equipe2'], $equipeFilter) !== false);
}

// Récupérer catégories pour chaque match
$matchCategories = [];
foreach ($matchs as $m) {
    $matchCategories[$m['id']] = Acheteur::getCategoriesMatch($m['id']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BuyMatch - Accueil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #e5e7eb; }
        .glass-card { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body class="min-h-screen p-6">

<!-- HEADER -->
<header class="mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
    <h1 class="text-4xl font-bold text-center md:text-left">BuyMatch</h1>
    <div class="space-x-4">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="auth/login.php" class="bg-indigo-600 px-4 py-2 rounded hover:bg-indigo-700 font-bold">Se connecter</a>
            <a href="auth/register.php" class="bg-green-600 px-4 py-2 rounded hover:bg-green-700 font-bold">S'inscrire</a>
        <?php else: ?>
            <span>Bonjour, <?= htmlspecialchars($_SESSION['user_nom'] ?? 'Utilisateur') ?></span>
        <?php endif; ?>
    </div>
</header>

<!-- FILTRES -->
<div class="mb-8 flex flex-col md:flex-row gap-4 items-center">
    <form method="GET" class="flex gap-4 flex-wrap">
        <input type="text" name="lieu" placeholder="Filtrer par lieu" value="<?= htmlspecialchars($lieuFilter) ?>"
               class="p-2 rounded bg-gray-700">
        <input type="text" name="equipe" placeholder="Filtrer par équipe" value="<?= htmlspecialchars($equipeFilter) ?>"
               class="p-2 rounded bg-gray-700">
        <button type="submit" class="bg-indigo-600 px-4 py-2 rounded hover:bg-indigo-700 font-bold">Filtrer</button>
    </form>
</div>

<!-- MATCHS -->
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($matchs)): ?>
        <p class="text-center col-span-full text-gray-400">Aucun match disponible.</p>
    <?php else: ?>
        <?php foreach ($matchs as $match): ?>
            <div class="glass-card p-6 rounded-2xl flex flex-col justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2"><?= htmlspecialchars($match['equipe1']) ?> vs <?= htmlspecialchars($match['equipe2']) ?></h2>
                    <p class="text-gray-400 mb-1">📍 <?= htmlspecialchars($match['lieu']) ?></p>
                    <p class="text-gray-400 mb-2">🗓 <?= date('d M Y H:i', strtotime($match['date_heure'])) ?></p>

                    <h3 class="text-lg font-semibold mt-4 mb-2">Catégories & Prix</h3>
                    <?php if (empty($matchCategories[$match['id']])): ?>
                        <p class="text-gray-400">Pas de catégorie disponible.</p>
                    <?php else: ?>
                        <ul class="space-y-1">
                            <?php foreach ($matchCategories[$match['id']] as $cat): ?>
                                <li class="flex justify-between text-gray-300">
                                    <span><?= htmlspecialchars($cat['nom']) ?></span>
                                    <span><?= number_format($cat['prix'],2) ?> DH</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <a href="match_details.php?id=<?= $match['id'] ?>"
                   class="mt-4 inline-block text-center bg-indigo-600 py-2 rounded font-bold hover:bg-indigo-700">
                    Voir les détails
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
