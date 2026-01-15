<?php


require_once BASE_PATH ."/config/database.php";
require_once BASE_PATH ."/classes/User.php";
require_once BASE_PATH ."/classes/Acheteur.php";

if (!isset($_SESSION['user_id'])) {
     header("Location: index.php?page=login");
    exit;
}

$db = Database::connect();
$success = isset($_GET['success']);

// Infos utilisateur
$stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("Utilisateur introuvable");
}


$acheteur = new Acheteur($_SESSION['user_id'], $user['nom'], $user['email'], '', 'acheteur', true);


// Vérifier match_id
$matchId = $_GET['match_id'] ?? null;
if (!$matchId || !is_numeric($matchId)) {
    die("Match invalide");
}

// Récupérer match
$match = $acheteur->getMatchById((int)$matchId);
if (!$match || $match['statut'] !== 'termine') {
    die("Vous ne pouvez pas commenter ce match");
}

// Vérifier achat billet
if (!$acheteur->aAcheteBillet($matchId)) {
    die("Vous devez avoir acheté un billet pour commenter");
}



// Vérifier déjà commenté
if ($acheteur->aDejaCommenter($matchId)) {
    die("Vous avez déjà laissé un avis");
}

// Traitement formulaire
$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = filter_input(INPUT_POST, 'note', FILTER_VALIDATE_INT);
    $contenu = trim(filter_input(INPUT_POST, 'contenu', FILTER_SANITIZE_SPECIAL_CHARS));


    if (!$note || $note < 1 || $note > 5) {
        $error = "Note invalide";
    } elseif (strlen($contenu) < 5) {
        $error = "Le commentaire est trop court";
    } else {
        $stmt = $db->prepare("
            INSERT INTO avis (user_id, match_id, note, contenu)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $matchId,
            $note,
            $contenu
        ]);

        header("Location: index.php?page=commenter_match&match_id=$matchId&success=1");

        exit;

    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Laisser un avis | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background: #050505 url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><rect fill="%23050505"/><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,.02)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            color: #e5e7eb;
            font-family: 'Inter', sans-serif;
        }
        .orbitron { font-family: 'Orbitron', sans-serif; }
        .glass-card { 
            background: rgba(255,255,255,.05); 
            backdrop-filter: blur(10px); 
            border: 1px solid rgba(255,255,255,.1);
        }
        .navbar {
            background: rgba(5,5,5,.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #06b6d4 0%, #6366f1 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(6,182,212,.3);
        }
        .star-rating {
            display: flex;
            gap: 0.5rem;
            font-size: 2rem;
        }
        .star {
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .star:hover {
            transform: scale(1.2);
        }
        input:focus, select:focus, textarea:focus {
            background-color: rgba(6,182,212,.1) !important;
            border-color: rgba(6,182,212,.5) !important;
            outline: none;
            box-shadow: 0 0 0 3px rgba(6,182,212,.1);
        }
    </style>
</head>
<body class="min-h-screen">

<!-- Ajout navbar professionnelle -->
<nav class="navbar sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="index.php?page=home "class="text-2xl font-bold orbitron bg-gradient-to-r from-cyan-400 to-indigo-500 bg-clip-text text-transparent">
            BuyMatch
        </a>
        <div class="flex items-center gap-6">
            <a href="index.php?page=home" class="text-gray-300 hover:text-cyan-400 transition">Accueil</a>
            <a href="index.php?page=match_details&id=<?= $matchId ?>" class="text-gray-300 hover:text-cyan-400 transition">Retour au match</href=>
        </div>
    </div>
</nav>

<!-- Conteneur principal avec padding et centrage -->
<div class="min-h-screen flex items-center justify-center p-6 py-12">
    <div class="glass-card w-full max-w-2xl p-8 md:p-12 rounded-2xl">
        
        <!-- En-tête redessiné avec typographie Orbitron -->
        <div class="mb-8">
            <h1 class="text-4xl md:text-5xl font-black orbitron bg-gradient-to-r from-yellow-400 via-yellow-300 to-amber-400 bg-clip-text text-transparent mb-2">
                Votre Avis
            </h1>
            <p class="text-gray-400 text-lg">Partagez votre expérience du match</p>
        </div>

        <!-- Infos du match en bloc amélioré -->
        <div class="glass-card p-6 mb-8 rounded-xl border-l-4 border-cyan-400">
            <p class="text-gray-300 text-sm uppercase tracking-wider mb-2">Match terminé</p>
            <p class="text-2xl font-bold orbitron">
                <span class="text-cyan-400"><?= htmlspecialchars($match['equipe1']) ?></span>
                <span class="text-gray-500 mx-3">vs</span>
                <span class="text-indigo-400"><?= htmlspecialchars($match['equipe2']) ?></span>
            </p>
        </div>

        <!-- Messages de succès/erreur -->
        <?php if ($success): ?>
            <div class="glass-card bg-green-500/10 border-l-4 border-green-400 p-6 rounded-xl mb-6">
                <p class="text-green-400 font-semibold flex items-center gap-2">
                    <span>✓</span> Votre avis a été publié avec succès !
                </p>
            </div>
            <a href="index.php?page=match_details&id=<?= $matchId ?>"
               class="btn-primary w-full py-3 rounded-xl font-bold text-white text-center block">
                ← Retour au match
            </a>
        <?php else: ?>

            <?php if ($error): ?>
                <div class="glass-card bg-red-500/10 border-l-4 border-red-400 p-4 rounded-xl mb-6">
                    <p class="text-red-400 font-semibold"><?= $error ?></p>
                </div>
            <?php endif; ?>

            <!-- Formulaire redessiné avec glassmorphism -->
            <form method="POST" class="space-y-6">
                
                <!-- Sélection de note avec styles modernes -->
                <div>
                    <label class="block mb-3 font-bold text-lg orbitron text-white">
                        Votre note
                    </label>
                    <select name="note" required
                            class="w-full glass-card p-4 rounded-xl text-white border border-gray-700 hover:border-cyan-400 transition">
                        <option value="" class="bg-gray-900">Choisir une note...</option>
                        <?php for ($i=5; $i>=1; $i--): ?>
                            <option value="<?= $i ?>" class="bg-gray-900"><?= $i ?> ⭐</option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Textarea redessiné -->
                <div>
                    <label class="block mb-3 font-bold text-lg orbitron text-white">
                        Votre commentaire
                    </label>
                    <textarea name="contenu" rows="5" required
                              class="w-full glass-card p-4 rounded-xl text-white border border-gray-700 hover:border-cyan-400 transition resize-none placeholder-gray-500"
                              placeholder="Décrivez votre expérience lors du match..."></textarea>
                    <p class="text-gray-400 text-sm mt-2">Minimum 5 caractères</p>
                </div>

                <!-- Bouton avec dégradé animé -->
                <button type="submit"
                        class="btn-primary w-full py-4 rounded-xl font-bold text-white text-lg orbitron uppercase tracking-wider">
                    Publier l'avis ⭐
                </button>

            </form>

        <?php endif; ?>
    </div>
</div>

</body>
</html>
