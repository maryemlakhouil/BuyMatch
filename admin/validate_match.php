<?php
session_start();

require_once "../config/database.php";
require_once "../classes/Admin.php";

$db = Database::connect();

/* Infos admin */

$stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("Admin introuvable");
}

/* Objet Admin */
$admin = new Admin($_SESSION['user_id'],$user['nom'],$user['email'],'');

/* Traitement action */
if (isset($_POST['match_id'], $_POST['action'])) {
    $matchId = (int) $_POST['match_id'];
    $action  = $_POST['action'];

    $admin->changerStatutMatch($matchId, $action);
}

/* Matchs en attente */
$matchs = $admin->listerMatchsEnAttente();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Validation des matchs | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

<div class="max-w-6xl mx-auto py-10">

    <h1 class="text-3xl font-bold text-gray-800 mb-8">
         Validation des matchs
    </h1>

    <?php if (empty($matchs)): ?>
        <div class="bg-green-100 text-green-700 p-4 rounded-lg">
            Aucun match en attente 
        </div>
    <?php else: ?>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-4 text-left">Match</th>
                        <th class="p-4 text-left">Date</th>
                        <th class="p-4 text-left">Lieu</th>
                        <th class="p-4 text-left">Organisateur</th>
                        <th class="p-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($matchs as $m): ?>
                    <tr class="border-t">
                        <td class="p-4 font-semibold">
                            <?= htmlspecialchars($m['equipe1']) ?>
                            vs
                            <?= htmlspecialchars($m['equipe2']) ?>
                        </td>
                        <td class="p-4">
                            <?= date('d/m/Y H:i', strtotime($m['date_heure'])) ?>
                        </td>
                        <td class="p-4"><?= htmlspecialchars($m['lieu']) ?></td>
                        <td class="p-4"><?= htmlspecialchars($m['organisateur']) ?></td>
                        <td class="p-4 text-center">
                            <form method="POST" class="flex justify-center gap-2">
                                <input type="hidden" name="match_id" value="<?= $m['id'] ?>">
                                <button name="action" value="valide"
                                        class="bg-green-600 text-white px-4 py-2 rounded">
                                    Valider
                                </button>
                                <button name="action" value="refuse"
                                        class="bg-red-600 text-white px-4 py-2 rounded">
                                    Refuser
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
