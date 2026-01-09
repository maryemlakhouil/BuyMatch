<?php
session_start();

require_once BASE_PATH . "/config/database.php";
require_once BASE_PATH . "/classes/User.php";
require_once BASE_PATH . "/classes/Acheteur.php";

/* Sécurité */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'acheteur') {
    header("Location: ../auth/login.php");
    exit;
}

/* DB */
$db = Database::connect();

/* Infos utilisateur */

$stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("Utilisateur introuvable !!!");
}

/* Objet Acheteur */

$acheteur = new Acheteur($_SESSION['user_id'], $user['nom'], $user['email'], '', 'acheteur', true);

/* Billets */
$billets = $acheteur->billetsAchetes();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes billets | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 text-gray-100 p-8">

<h1 class="text-3xl font-bold text-indigo-500 mb-8">
     Mes billets
</h1>

<?php if (empty($billets)): ?>
    <div class="bg-gray-800 p-6 rounded-xl text-center text-gray-400">
        Aucun billet acheté pour le moment.
    </div>
<?php else: ?>
<div class="overflow-x-auto bg-gray-800 rounded-xl shadow">
<table class="min-w-full">
    <thead class="bg-indigo-600 text-white">
        <tr>
            <th class="p-3 text-left">Match</th>
            <th class="p-3">Date</th>
            <th class="p-3">Catégorie</th>
            <th class="p-3">Place</th>
            <th class="p-3">Prix</th>
            <th class="p-3">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($billets as $b): ?>
        <tr class="border-b border-gray-700 hover:bg-gray-700/50">
            <td class="p-3 font-semibold">
                <?= htmlspecialchars($b['equipe1']) ?> vs <?= htmlspecialchars($b['equipe2']) ?>
            </td>
            <td class="p-3 text-center">
                <?= date('d/m/Y H:i', strtotime($b['date_heure'])) ?>
            </td>
            <td class="p-3 text-center">
                <?= htmlspecialchars($b['categorie']) ?>
            </td>
            <td class="p-3 text-center">
                #<?= $b['numero_place'] ?>
            </td>
            <td class="p-3 text-center font-bold">
                <?= number_format($b['prix'], 2) ?> DH
            </td>
            <td class="p-3 text-center">
                <a href="ticket_print.php?ticket_id=<?= $b['id'] ?>"
                   target="_blank"
                   class="bg-green-600 px-4 py-2 rounded text-sm font-bold hover:bg-green-700">
                    Imprimer
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

</body>
</html>
