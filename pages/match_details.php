<?php

    session_start();
    require_once "../config/database.php";
    require_once "../classes/User.php";
    require_once "../classes/Acheteur.php";

    // Vérifier authentification

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit;
    }

    // Connexion DB
    $db = Database::connect();

    // Récupérer infos utilisateur depuis DB

    $stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Utilisateur introuvable !! ");
    }

    // Créer objet 
    $acheteur = new Acheteur($_SESSION['user_id'], $user['nom'], $user['email']);

    // Vérifier paramètre id

    $matchId = $_GET['id'] ?? null;
    if (!$matchId || !is_numeric($matchId)) {
        $error = "Match invalide.";
        $match = null;
    } else {
        // Récupérer match
        $match = $acheteur->getMatchById((int)$matchId);
        if (!$match) {
            $error = "Match introuvable ou non disponible.";
        }
    }

    // Récupérer catégories si match existant
    $categories = $match ? $acheteur->getCategoriesMatch($matchId) : [] ;
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

<a href="buy_ticket.php?match_id=<?= $match['id'] ?>"
   class="mt-6 inline-block bg-indigo-600 px-6 py-3 rounded font-bold hover:bg-indigo-700">
   🎟 Acheter un billet
</a>

<?php if(isset($error)): ?>
    <div class="glass-card p-6 rounded-xl text-center text-red-400 font-bold">
        <?= htmlspecialchars($error) ?>
    </div>
<?php else: ?>
    <div class="glass-card p-8 rounded-2xl mb-8">
        <h1 class="text-3xl font-bold mb-4"><?= htmlspecialchars($match['equipe1']) ?> vs <?= htmlspecialchars($match['equipe2']) ?></h1>
        <p class="text-gray-400 mb-2">Lieu : <?= htmlspecialchars($match['lieu']) ?></p>
        <p class="text-gray-400 mb-2">Date & Heure : <?= date('d M Y H:i', strtotime($match['date_heure'])) ?></p>
    </div>

    <div class="glass-card p-6 rounded-2xl">
        <h2 class="text-xl font-bold mb-4">Catégories disponibles</h2>
        <?php if(empty($categories)): ?>
            <p class="text-gray-400">Aucune catégorie disponible pour ce match.</p>
        <?php else: ?>
            <ul class="space-y-3">
                <?php foreach($categories as $cat): ?>
                    <li class="p-4 bg-white/5 rounded-xl flex justify-between items-center">
                        <span><?= htmlspecialchars($cat['nom']) ?> - <?= number_format($cat['prix'],2) ?> DH</span>
                        <span class="text-sm text-gray-400"><?= $cat['nb_places'] ?> places</span>
                    </li>
                <?php endforeach; ?>
                
            </ul>
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>
</html>
