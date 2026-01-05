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

if (!$user) {
    die("Admin introuvable");
}

/* Objet Admin */
$admin = new Admin(
    $_SESSION['user_id'],
    $user['nom'],
    $user['email'],
    ''
);

/* Actions */
if (isset($_GET['toggle'])) {
    $admin->changerStatutUtilisateur(
        (int) $_GET['toggle'],
        (bool) $_GET['statut']
    );
}

if (isset($_GET['delete'])) {
    $admin->supprimerUtilisateur((int) $_GET['delete']);
}

/* Liste utilisateurs */
$utilisateurs = $admin->listerUtilisateurs();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin | Utilisateurs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<h1 class="text-3xl font-bold text-indigo-700 mb-8">
     Gestion des utilisateurs
</h1>

<div class="bg-white rounded-xl shadow overflow-x-auto">
<table class="min-w-full">
    <thead class="bg-indigo-600 text-white">
        <tr>
            <th class="p-3">Nom</th>
            <th class="p-3">Email</th>
            <th class="p-3">Rôle</th>
            <th class="p-3">Statut</th>
            <th class="p-3">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($utilisateurs as $u): ?>
        <tr class="border-b hover:bg-gray-50">
            <td class="p-3"><?= htmlspecialchars($u['nom']) ?></td>
            <td class="p-3"><?= htmlspecialchars($u['email']) ?></td>
            <td class="p-3 font-semibold">
                <?= ucfirst($u['role']) ?>
            </td>
            <td class="p-3">
                <?= $u['is_active'] ? '🟢 Actif' : '🔴 Inactif' ?>
            </td>
            <td class="p-3 space-x-2">

                <?php if ($u['id'] !== $_SESSION['user_id']): ?>

                    <a href="?toggle=<?= $u['id'] ?>&statut=<?= $u['is_active'] ? 0 : 1 ?>"
                       class="px-3 py-1 rounded bg-yellow-400 text-white">
                        <?= $u['is_active'] ? 'Désactiver' : 'Activer' ?>
                    </a>

                    <a href="?delete=<?= $u['id'] ?>"
                       onclick="return confirm('Supprimer cet utilisateur ?')"
                       class="px-3 py-1 rounded bg-red-500 text-white">
                        Supprimer
                    </a>

                <?php else: ?>
                    <span class="text-gray-400">—</span>
                <?php endif; ?>

            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

</body>
</html>
