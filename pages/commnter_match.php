<?php
session_start();

require_once BASE_PATH ."/config/database.php";
require_once BASE_PATH ."/classes/User.php";
require_once BASE_PATH ."/classes/Acheteur.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
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

        header("Location: commenter_match.php?match_id=$matchId&success=1");
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
    <style>
        body { background:#050505; color:#e5e7eb; font-family:Inter,sans-serif; }
        .glass-card { background:rgba(255,255,255,.05); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,.1); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

<div class="glass-card w-full max-w-xl p-8 rounded-2xl">
    <h1 class="text-2xl font-bold mb-2">
        ⭐ Avis sur le match
    </h1>

    <p class="text-gray-400 mb-6">
        <?= htmlspecialchars($match['equipe1']) ?> vs <?= htmlspecialchars($match['equipe2']) ?>
    </p>

    <?php if ($success): ?>
        <div class="bg-green-500/20 text-green-400 p-4 rounded mb-4">
            <?= $success ?>
        </div>
        <a href="match_details.php?id=<?= $matchId ?>"
           class="block text-center bg-indigo-600 py-3 rounded font-bold hover:bg-indigo-700">
            Retour au match
        </a>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="bg-red-500/20 text-red-400 p-4 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block mb-2 font-bold">Note</label>
                <select name="note" required
                        class="w-full bg-black/40 p-3 rounded">
                    <option value="">Choisir une note</option>
                    <?php for ($i=5; $i>=1; $i--): ?>
                        <option value="<?= $i ?>"><?= $i ?> ⭐</option>
                    <?php endfor; ?>
                </select>
            </div>

            <div>
                <label class="block mb-2 font-bold">Commentaire</label>
                <textarea name="contenu" rows="4" required
                          class="w-full bg-black/40 p-3 rounded resize-none"
                          placeholder="Votre expérience..."></textarea>
            </div>

            <button type="submit"
                    class="w-full bg-yellow-500 py-3 rounded font-bold hover:bg-yellow-600">
                Publier l’avis
            </button>
        </form>

    <?php endif; ?>
</div>

</body>
</html>
