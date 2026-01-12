<?php

    require_once BASE_PATH . "/config/database.php";
    require_once BASE_PATH . "/classes/User.php";
    require_once BASE_PATH . "/classes/Acheteur.php";

    /* Sécurité */
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?page=login");
        exit;
    }

    $db = Database::connect();

    /* Infos utilisateur */
    $stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: index.php?page=logout");
        exit;
    }

    /* Objet Acheteur */
    $acheteur = new Acheteur($_SESSION['user_id'], $user['nom'], $user['email'], '', 'acheteur', true);

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
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #050505 0%, #0f0f1e 100%); color: #e5e7eb; }
        .orbitron { font-family: 'Orbitron', sans-serif; }
        .glass-card { background: rgba(255,255,255,0.05); backdrop-filter: blur(10px); border: 1px solid rgba(99,102,241,0.2); transition: all 0.3s ease; }
        .glass-card:hover { border-color: rgba(99,102,241,0.5); background: rgba(255,255,255,0.08); }
        .gradient-btn { background: linear-gradient(135deg, #06b6d4 0%, #6366f1 100%); transition: all 0.3s ease; }
        .gradient-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(6,182,212,0.3); }
        .navbar { background: rgba(5,5,5,0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(99,102,241,0.1); }
        .nav-link { color: #cbd5e1; transition: all 0.3s ease; position: relative; }
        .nav-link::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 0; height: 2px; background: linear-gradient(90deg, #06b6d4, #6366f1); transition: width 0.3s ease; }
        .nav-link:hover::after { width: 100%; }
        .stat-badge { background: rgba(6,182,212,0.1); border: 1px solid rgba(6,182,212,0.3); }
        .category-item { background: rgba(99,102,241,0.05); border: 1px solid rgba(99,102,241,0.1); transition: all 0.3s ease; }
        .category-item:hover { background: rgba(99,102,241,0.1); border-color: rgba(99,102,241,0.3); }
    </style>
</head>

<body class="min-h-screen">

    <!-- NAVBAR -->
    <nav class="navbar sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="orbitron text-2xl font-black bg-gradient-to-r from-cyan-400 to-indigo-500 bg-clip-text text-transparent">
                BuyMatch
            </div>
            <div class="flex gap-8">
                <a href="index.php?page=home"  class="nav-link">Accueil</a>
                <a href="index.php?page=matchs" class="nav-link">Matchs</a>
                <a href="index.php?page=profile" class="nav-link">Profil</a>
            </div>
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-400 to-indigo-600 flex items-center justify-center text-white font-bold">
                <?= strtoupper(substr($user['nom'], 0, 1)) ?>
            </div>
        </div>
    </nav>

    <!-- CONTENU -->
    <div class="max-w-5xl mx-auto px-6 py-12">

    <!-- RETOUR -->
    <a href="index.php?page=home" class="inline-flex items-center gap-2 text-cyan-400 hover:text-cyan-300 mb-8 transition-colors">
        <span>←</span> Retour aux matchs
    </a>

    <?php if (isset($error)): ?>
        <!-- ERREUR -->
        <div class="glass-card p-8 rounded-2xl text-center">
            <p class="text-red-400 font-bold text-lg"><?= htmlspecialchars($error) ?></p>
        </div>

    <?php else: ?>

        <!-- HEADER MATCH -->
        <div class="glass-card p-8 rounded-2xl mb-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="orbitron text-4xl font-black bg-gradient-to-r from-cyan-400 via-blue-400 to-indigo-500 bg-clip-text text-transparent mb-4">
                        <?= htmlspecialchars($match['equipe1']) ?> vs <?= htmlspecialchars($match['equipe2']) ?>
                    </h1>
                    <div class="space-y-2 text-gray-300">
                        <p class="flex items-center gap-2">
                            <span class="text-cyan-400"></span> <?= htmlspecialchars($match['lieu']) ?>
                        </p>
                        <p class="flex items-center gap-2">
                            <span class="text-cyan-400"></span> <?= date('d M Y à H:i', strtotime($match['date_heure'])) ?>
                        </p>
                    </div>
                </div>
                
                <!-- STATUT BADGE -->
                <div class="stat-badge px-4 py-2 rounded-full text-sm font-bold">
                    <span class="<?= $match['statut'] === 'termine' ? 'text-emerald-400' : 'text-cyan-400' ?>">
                        ● <?= ucfirst($match['statut']) ?>
                    </span>
                </div>
            </div>

            <!-- AVIS STARS -->
            <?php if ($match['statut'] === 'termine' && $statsAvis['total'] > 0): ?>
                <div class="flex items-center gap-4 mt-6 pt-6 border-t border-white/10">
                    <div class="text-3xl font-black text-yellow-400"><?= $statsAvis['moyenne'] ?>/5</div>
                    <div class="flex gap-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="<?= $i <= $statsAvis['moyenne'] ? 'text-yellow-400' : 'text-gray-600' ?> text-lg">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="text-gray-400 text-sm"><?= $statsAvis['total'] ?> avis</span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($match && $match['statut'] !== 'termine'): ?>
            <a href="index.php?page=buy_ticket&match_id=<?= $match['id'] ?>" 
            class="gradient-btn text-white px-8 py-4 rounded-xl font-bold mb-8 inline-block">
                Acheter un billet
            </a>
        <?php endif; ?>

        <!-- CATEGORIES -->
        <div class="glass-card p-8 rounded-2xl mb-8">
            <h2 class="orbitron text-2xl font-bold mb-6 text-cyan-400">Catégories disponibles</h2>

            <?php if (empty($categories)): ?>
                <p class="text-gray-400">Aucune catégorie disponible.</p>
            <?php else: ?>
                <div class="grid gap-4">
                    <?php foreach ($categories as $cat): ?>
                        <div class="category-item p-5 rounded-xl flex justify-between items-center">
                            <div>
                                <p class="font-bold text-white"><?= htmlspecialchars($cat['nom']) ?></p>
                                <p class="text-sm text-gray-400"><?= $cat['nb_places'] ?> places disponibles</p>
                            </div>
                            <p class="text-xl font-black text-cyan-400"><?= number_format($cat['prix'], 2) ?> DH</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- BOUTON AVIS -->
        <?php if ($match && $match['statut'] === 'termine' && !$acheteur->aDejaCommenter($match['id'])): ?>
            <a href="index.php?page=commenter_match&match_id=<?= $match['id'] ?>"
            class="gradient-btn text-white px-8 py-4 rounded-xl font-bold mb-8 inline-block">
                ⭐ Laisser un avis
            </a>
        <?php endif; ?>

        <!-- AVIS DES SPECTATEURS -->
        <?php if ($match && $match['statut'] === 'termine'): ?>
            <div class="glass-card p-8 rounded-2xl">
                <h2 class="orbitron text-2xl font-bold mb-6 text-cyan-400">⭐ Avis des spectateurs</h2>

                <?php if (empty($avis)): ?>
                    <p class="text-gray-400 text-center py-8">Aucun avis pour ce match.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($avis as $a): ?>
                            <div class="category-item p-5 rounded-xl">
                                <div class="flex justify-between items-start mb-3">
                                    <span class="font-bold text-cyan-400"><?= htmlspecialchars($a['nom']) ?></span>
                                    <span class="text-yellow-400 font-bold">
                                        <?php for ($i = 0; $i < (int)$a['note']; $i++): ?>
                                            ★
                                        <?php endfor; ?>
                                    </span>
                                </div>
                                <p class="text-gray-300 mb-3">
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

</div>

</body>
</html>