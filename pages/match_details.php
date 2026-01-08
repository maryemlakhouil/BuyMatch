<?php
session_start();

require_once "../config/database.php";
require_once "../classes/User.php";
require_once "../classes/Acheteur.php";

/* Sécurité */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$db = Database::connect();

/* Infos utilisateur */
$stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("Utilisateur introuvable");
}

/* Objet Acheteur */
$acheteur = new Acheteur($_SESSION['user_id'], $user['nom'], $user['email']);

/* Vérifier match */
$match = null;
$error = null;

$matchId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$matchId) {
    $error = "Match invalide.";
} else {
    $match = $acheteur->getMatchById($matchId);

    if (!$match) {
        $error = "Match introuvable ou non disponible.";
    }
}


/* Catégories */
$categories = $match ? $acheteur->getCategoriesMatch($matchId) : [];


/* Avis & stats */
$avis = isset($match) ? $acheteur->getAvisMatch($match['id']) : [];
$statsAvis = [
    'total' => 0,
    'moyenne' => 0
];

if ($match && $match['statut'] === 'termine') {
    $statsAvis = $acheteur->getStatsAvis($match['id']);
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails du match | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #e5e7eb; }
        .glass-card { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>

<body class="min-h-screen p-8">

<a href="matchs.php" class="text-indigo-400 hover:text-indigo-300 mb-8 inline-block">
    ← Retour aux matchs
</a>

<?php if (isset($error)): ?>
    <div class="glass-card p-6 rounded-xl text-center text-red-400 font-bold">
        <?= htmlspecialchars($error) ?>
    </div>
<?php else: ?>

<!-- MATCH INFO -->
<div class="glass-card p-8 rounded-2xl mb-8">
    <h1 class="text-3xl font-bold mb-4">
        <?= htmlspecialchars($match['equipe1']) ?> vs <?= htmlspecialchars($match['equipe2']) ?>
    </h1>

    <p class="text-gray-400 mb-2">📍 <?= htmlspecialchars($match['lieu']) ?></p>
    <p class="text-gray-400 mb-2">
        🗓 <?= date('d M Y H:i', strtotime($match['date_heure'])) ?>
    </p>

    <!-- Stats avis -->
    <div class="mt-4 text-yellow-400 font-bold">
        <?php if ($statsAvis['total'] > 0): ?>
            <?= $statsAvis['moyenne'] ?> ⭐ (<?= $statsAvis['total'] ?> avis)
        <?php else: ?>
            Aucun avis pour ce match
        <?php endif; ?>
    </div>
</div>

<!-- ACHAT -->
<?php if ($match&& $match['statut'] !== 'termine'): ?>
    <a href="buy_ticket.php?match_id=<?= $match['id'] ?>"
       class="inline-block mb-8 bg-indigo-600 px-6 py-3 rounded font-bold hover:bg-indigo-700">
       🎟 Acheter un billet
    </a>
<?php endif; ?>
<?php if ($match && $match['statut'] === 'termine'): ?>
    <div class="glass-card p-6 rounded-2xl mb-8">
        <h2 class="text-xl font-bold mb-2">⭐ Avis des spectateurs</h2>

        <?php if ($statsAvis['total'] === 0): ?>
            <p class="text-gray-400">
                Aucun avis pour ce match.
            </p>
        <?php else: ?>
            <div class="flex items-center gap-4">
                <div class="text-3xl font-bold text-yellow-400">
                    <?= $statsAvis['moyenne'] ?>/5
                </div>

                <div class="text-gray-400">
                    <?= $statsAvis['total'] ?> avis
                </div>

                <div class="flex">
                    <?php
                    $fullStars = floor($statsAvis['moyenne']);
                    for ($i = 1; $i <= 5; $i++):
                    ?>
                        <span class="<?= $i <= $fullStars ? 'text-yellow-400' : 'text-gray-600' ?>">
                            ⭐
                        </span>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- CATEGORIES -->
<div class="glass-card p-6 rounded-2xl mb-10">

    <h2 class="text-xl font-bold mb-4">Catégories disponibles</h2>

    <?php if (empty($categories)): ?>
        <p class="text-gray-400">Aucune catégorie disponible.</p>
    <?php else: ?>
        <ul class="space-y-3">
            <?php foreach ($categories as $cat): ?>
                <li class="p-4 bg-white/5 rounded-xl flex justify-between">
                    <span>
                        <?= htmlspecialchars($cat['nom']) ?> —
                        <?= number_format($cat['prix'], 2) ?> DH
                    </span>
                    <span class="text-sm text-gray-400">
                        <?= $cat['nb_places'] ?> places
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<!-- BOUTON AVIS -->
<?php if ($match &&
    $match['statut'] === 'termine'
    && !$acheteur->aDejaCommenter($match['id'])
): ?>
    <a href="commenter_match.php?match_id=<?= $match['id'] ?>"
       class="inline-block mb-10 bg-yellow-500 px-6 py-3 rounded font-bold hover:bg-yellow-600">
       ⭐ Laisser un avis
    </a>
<?php endif; ?>

<!-- AVIS -->
<?php if ($match && $match['statut'] === 'termine'): ?>
<div class="glass-card p-6 rounded-2xl">
    <h2 class="text-xl font-bold mb-6">⭐ Avis des spectateurs</h2>

    <?php if (empty($avis)): ?>
        <p class="text-gray-400">Aucun avis pour ce match.</p>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($avis as $a): ?>
                <div class="bg-white/5 p-4 rounded-xl">
                    <div class="flex justify-between mb-2">
                        <span class="font-bold text-indigo-400">
                            <?= htmlspecialchars($a['nom']) ?>
                        </span>
                        <span class="text-yellow-400 font-bold">
                            <?= str_repeat("⭐", (int)$a['note']) ?>
                        </span>
                    </div>

                    <p class="text-gray-300 mb-2">
                        <?= nl2br(htmlspecialchars($a['contenu'])) ?>
                    </p>

                    <p class="text-xs text-gray-500">
                        <?= date('d/m/Y H:i', strtotime($a['created_at'])) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>

</body>
</html>
