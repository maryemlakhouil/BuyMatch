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

$acheteur = new Acheteur($_SESSION['user_id'], $user['nom'], $user['email'], '', 'acheteur', true);

/* Vérifier match */
$matchId = $_GET['match_id'] ?? null;

if (!$matchId || !is_numeric($matchId)) {
    die("Match invalide");
}

$match = $acheteur->getMatchById((int)$matchId);
if (!$match) {
    die("Match introuvable ou non disponible");
}

/* Catégories */
$categories = $acheteur->getCategoriesMatch($matchId);

/* Nombre de billets déjà achetés */
$nbBillets = $acheteur->nombreBilletsAchetes($matchId);

/* Messages */
$error = "";
$success = "";

/* Achat billet */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $categorieId  = (int) $_POST['categorie_id'];
        $numeroPlace  = (int) $_POST['numero_place'];

        $ticket = $acheteur->acheterBillet($matchId, $categorieId, $numeroPlace);
        // ENVOI EMAIL
        $acheteur->envoyerBilletParEmail($ticket, $match);

        $success = "Billet acheté avec succès ! Un email vous a été envoyé ";

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Achat Billet | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 p-8">

<a href="match_details.php?id=<?= $matchId ?>" class="text-indigo-400">← Retour</a>

<div class="max-w-xl mx-auto bg-gray-800 p-8 rounded-xl mt-6">

    <h1 class="text-2xl font-bold mb-4">
        <?= htmlspecialchars($match['equipe1']) ?> vs <?= htmlspecialchars($match['equipe2']) ?>
    </h1>

    <p class="text-gray-400 mb-6">
        <?= date('d/m/Y H:i', strtotime($match['date_heure'])) ?>
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
    <?php endif; ?>

    <?php if ($nbBillets >= 4): ?>
        <p class="text-yellow-400 font-bold">
         Vous avez atteint la limite de 4 billets pour ce match.
        </p>
    <?php else: ?>
    <form method="POST" class="space-y-4">

        <div>
            <label class="block mb-1">Catégorie</label>
            <select name="categorie_id" required class="w-full p-3 rounded bg-gray-700">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>">
                        <?= htmlspecialchars($cat['nom']) ?> — <?= number_format($cat['prix'],2) ?> DH
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block mb-1">Numéro de place</label>
            <input type="number" name="numero_place" min="1" required
                   class="w-full p-3 rounded bg-gray-700">
        </div>

        <button class="w-full bg-indigo-600 py-3 rounded font-bold hover:bg-indigo-700">
            Acheter le billet
        </button>

    </form>
    <?php endif; ?>
    <?php if ($success): ?>
    <a href="ticket_print.php?ticket_id=<?= $ticket['id'] ?>"target="_blank"
       class="block mt-4 bg-green-600 text-center py-3 rounded font-bold hover:bg-green-700">
        Télécharger / Imprimer le billet
    </a>
<?php endif; ?>


</div>

</body>
</html>
