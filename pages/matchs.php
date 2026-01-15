<?php

    require_once BASE_PATH . "/config/database.php";
    require_once BASE_PATH . "/classes/User.php";
    require_once BASE_PATH . "/classes/Acheteur.php";

    /*  Auth */
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?page=login");
        exit;
    }

    $db = Database::connect();

    /*  User */
    $stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: index.php?page=logout");
        exit;
    }

    $acheteur = new Acheteur($_SESSION['user_id'], $user['nom'], $user['email'], '', 'acheteur', true);


    /* Match */
    $matchId = $_GET['match_id'] ?? null;
    if (!$matchId || !is_numeric($matchId)) {
        die("Match invalide");
    }

    $match = $acheteur->getMatchById((int)$matchId);
    if (!$match) {
        die("Match introuvable");
    }

    /*  Match pas terminé */
    if ($match['statut'] !== 'termine') {
        die("Vous ne pouvez commenter qu'après la fin du match");
    }

    /*  Vérifier billet */
    if (!$acheteur->aAcheteBillet($matchId)) {
        die("Vous devez avoir acheté un billet pour commenter");
    }


    /*  Déjà commenté */
    if ($acheteur->aDejaCommenter($matchId)) {
        die("Vous avez déjà laissé un avis pour ce match");
    }

    /* Traitement formulaire */
    $error = "";
    $success = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $note = (int) ($_POST['note'] ?? 0);
        $contenu = trim($_POST['contenu'] ?? "");

        if ($note < 1 || $note > 5) {
            $error = "La note doit être entre 1 et 5";
        } elseif (strlen($contenu) < 5) {
            $error = "Le commentaire est trop court";
        } else {
            try {
                $acheteur->ajouterAvis($matchId, $note, $contenu);
                $success = "Merci pour votre avis 🙏";
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Laisser un avis | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 text-gray-100 min-h-screen flex items-center justify-center">

<div class="w-full max-w-lg bg-gray-800 p-8 rounded-xl shadow-lg">

    <h1 class="text-2xl font-bold mb-2 text-center">
        <?= htmlspecialchars($match['equipe1']) ?> vs <?= htmlspecialchars($match['equipe2']) ?>
    </h1>

    <p class="text-gray-400 text-center mb-6">
        Donnez votre avis sur ce match
    </p>

    <?php if ($error): ?>
        <div class="bg-red-500/20 text-red-400 p-3 rounded mb-4">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-500/20 text-green-400 p-3 rounded mb-4">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php else: ?>
    <form method="POST" class="space-y-4">

        <div>
            <label class="block mb-1 font-bold">Note</label>
            <select name="note" required class="w-full p-3 rounded bg-gray-700">
                <option value="">Choisir une note</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?= $i ?>"><?= $i ?> ⭐</option>
                <?php endfor; ?>
            </select>
        </div>

        <div>
            <label class="block mb-1 font-bold">Commentaire</label>
            <textarea name="contenu" rows="4" required
                      class="w-full p-3 rounded bg-gray-700"
                      placeholder="Votre expérience..."></textarea>
        </div>

        <button class="w-full bg-indigo-600 py-3 rounded font-bold hover:bg-indigo-700">
            Publier l'avis
        </button>

    </form>
    <?php endif; ?>

    <a href="index.php?page=match_details&id=<?= $matchId ?>"
       class="block text-center text-indigo-400 mt-4 hover:underline">
        ← Retour au match
    </a>

</div>

</body>
</html>
