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
$admin = new Admin($_SESSION['user_id'],$user['nom'],$user['email'],'');

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

if (isset($_GET['role'], $_GET['id'])) {
    $admin->changerRoleUtilisateur(
        (int) $_GET['id'],
        $_GET['role']
    );
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin | Gestion Utilisateurs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #e5e7eb; }
        .font-sport { font-family: 'Orbitron', sans-serif; }
        .glass-card { 
            background: rgba(17, 24, 39, 0.7); 
            backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
        }
        .cyber-border { border-left: 4px solid #4f46e5; }
    </style>
</head>
<body class="p-4 md:p-8 min-h-screen bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-900/20 via-black to-black">

    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-4">
            <div>
                <a href="dashboard.php" class="text-indigo-400 hover:text-indigo-300 flex items-center gap-2 mb-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Retour au Dashboard
                </a>
                <h1 class="text-3xl md:text-4xl font-sport text-white tracking-wider uppercase">
                    Gestion des <span class="text-indigo-500">Utilisateurs</span>
                </h1>
            </div>
            <div class="glass-card px-4 py-2 rounded-lg flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                <span class="text-sm font-medium text-gray-400">Panel Administrateur Sécurisé</span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="glass-card rounded-2xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-white/5">
                    <thead class="bg-white/5">
                        <tr class="text-left text-xs font-sport text-indigo-400 uppercase tracking-widest">
                            <th class="p-5">Identité</th>
                            <th class="p-5">Contact</th>
                            <th class="p-5 text-center">Rôle</th>
                            <th class="p-5 text-center">Statut</th>
                            <th class="p-5 text-right">Actions de Contrôle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <?php foreach ($utilisateurs as $u): ?>
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="p-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-600 to-purple-700 flex items-center justify-center text-white font-bold border border-white/10 shadow-lg">
                                        <?= strtoupper(substr($u['nom'], 0, 1)) ?>
                                    </div>
                                    <span class="font-semibold text-gray-200"><?= htmlspecialchars($u['nom']) ?></span>
                                </div>
                            </td>
                            <td class="p-5 text-gray-400 italic text-sm">
                                <?= htmlspecialchars($u['email']) ?>
                            </td>
                            <td class="p-5 text-center">
                                <span class="px-3 py-1 rounded-full text-[10px] font-sport uppercase tracking-tighter <?= $u['role'] === 'admin' ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' : ($u['role'] === 'organisateur' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : 'bg-gray-500/20 text-gray-400 border border-gray-500/30') ?>">
                                    <?= htmlspecialchars($u['role']) ?>
                                </span>
                            </td>
                            <td class="p-5 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="w-2 h-2 rounded-full <?= $u['is_active'] ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.5)]' ?>"></span>
                                    <span class="text-xs font-medium <?= $u['is_active'] ? 'text-emerald-400' : 'text-rose-400' ?>">
                                        <?= $u['is_active'] ? 'ACTIF' : 'SUSPENDU' ?>
                                    </span>
                                </div>
                            </td>
                            <td class="p-5 text-right space-x-2">
                                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                    <!-- Toggle Status -->
                                    <a href="?toggle=<?= $u['id'] ?>&statut=<?= $u['is_active'] ? 0 : 1 ?>"
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all <?= $u['is_active'] ? 'bg-amber-500/10 text-amber-500 hover:bg-amber-500 hover:text-black' : 'bg-emerald-500/10 text-emerald-500 hover:bg-emerald-500 hover:text-black border border-emerald-500/20' ?>">
                                        <?= $u['is_active'] ? 'Désactiver' : 'Activer' ?>
                                    </a>

                                    <!-- Delete -->
                                    <a href="?delete=<?= $u['id'] ?>"
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-rose-500/10 text-rose-500 hover:bg-rose-600 hover:text-white text-xs font-semibold transition-all border border-rose-500/20"
                                       onclick="return confirm('Confirmer la suppression irréversible ?')">
                                        Supprimer
                                    </a>

                                    <!-- Role Change -->
                                    <?php if ($u['role'] !== 'admin'): ?>
                                        <a href="?role=<?= $u['role'] === 'acheteur' ? 'organisateur' : 'acheteur' ?>&id=<?= $u['id'] ?>"
                                           class="inline-flex items-center px-3 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-500 text-xs font-bold transition-all shadow-lg shadow-indigo-600/20 uppercase tracking-tighter">
                                            ➜ <?= $u['role'] === 'acheteur' ? 'Organisateur' : 'Acheteur' ?>
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-gray-600 italic text-xs">Administrateur Actuel</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>