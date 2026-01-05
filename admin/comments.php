<?php
session_start();

require_once "../config/database.php";
require_once "../classes/Admin.php";

/* Sécurité */
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header("Location: ../auth/login.php");
//     exit;
// }

$db = Database::connect();

/* Infos admin */
$stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$admin = new Admin($_SESSION['user_id'], $user['nom'], $user['email'], '');

/* Actions */
if (isset($_GET['delete'])) {
    $admin->supprimerCommentaire((int) $_GET['delete']);
}

/* Liste commentaires */
$commentaires = $admin->listerCommentaires();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin | Commentaires</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<h1 class="text-3xl font-bold text-indigo-700 mb-8">
     Modération des commentaires
</h1>

<div class="bg-white rounded-xl shadow overflow-x-auto">
<table class="min-w-full">
    <thead class="bg-indigo-600 text-white">
        <tr>
            <th class="p-3">Utilisateur</th>
            <th class="p-3">Match</th>
            <th class="p-3">Note</th>
            <th class="p-3">Commentaire</th>
            <th class="p-3">Date</th>
            <th class="p-3">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($commentaires as $c): ?>
        <tr class="border-b hover:bg-gray-50">
            <td class="p-3"><?= htmlspecialchars($c['utilisateur']) ?></td>
            <td class="p-3"><?= htmlspecialchars($c['equipe1'] . ' vs ' . $c['equipe2']) ?></td>
            <td class="p-3"><?= $c['note'] ?>/5</td>
            <td class="p-3"><?= nl2br(htmlspecialchars($c['contenu'])) ?></td>
            <td class="p-3"><?= date('d/m/Y H:i', strtotime($c['date_commentaire'])) ?></td>
            <td class="p-3">
                <a href="?delete=<?= $c['id'] ?>"
                   onclick="return confirm('Supprimer ce commentaire ?')"
                   class="px-3 py-1 rounded bg-red-500 text-white">
                   Supprimer
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

</body>
</html>
