<?php
session_start();

// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisateur') {
//     header('Location: ../auth/login.php');
//     exit;
// }

require_once "../config/database.php";
require_once "../classes/Organisateur.php";

/* Connexion DB */
$db = Database::connect();

/* Charger les infos organisateur depuis la BDD */
$stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("Organisateur introuvable");
}

/* Instanciation POO */
$organisateur = new Organisateur(
    $_SESSION['user_id'],
    $user['nom'],
    $user['email']
);

/* Récupérer statistiques */
$stats = $organisateur->consulterStatistiques();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques | Organisateur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-sport { font-family: 'Orbitron', sans-serif; }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="bg-[#050505] text-gray-200 min-h-screen">

<div class="max-w-6xl mx-auto py-12 px-6">
    <!-- Header stylisé avec le thème Cyber Sport -->
    <div class="mb-12 text-center">
        <h1 class="text-4xl md:text-5xl font-sport font-bold tracking-tighter text-white uppercase italic">
            <span class="text-indigo-500">Analytics</span> Performance
        </h1>
        <div class="h-1 w-24 bg-indigo-600 mx-auto mt-4 rounded-full"></div>
    </div>

    <?php if (empty($stats)): ?>
        <!-- Alerte vide stylisée -->
        <div class="glass-card p-8 rounded-2xl border-dashed border-2 border-gray-800 text-center">
            <div class="text-5xl mb-4">📊</div>
            <p class="text-gray-400 text-lg">Aucune donnée de performance disponible pour le moment.</p>
        </div>
    <?php else: ?>

    <!-- Tableau de statistiques modernisé avec effets de verre -->
    <div class="overflow-hidden glass-card rounded-2xl shadow-2xl">
        <table class="min-w-full border-separate border-spacing-0">
            <thead>
                <tr class="bg-indigo-600/10">
                    <th class="p-6 text-left text-xs font-sport uppercase tracking-widest text-indigo-400 border-b border-white/5">Détails du Match</th>
                    <th class="p-6 text-center text-xs font-sport uppercase tracking-widest text-indigo-400 border-b border-white/5">Billets Vendus</th>
                    <th class="p-6 text-right text-xs font-sport uppercase tracking-widest text-indigo-400 border-b border-white/5">Revenus Totaux</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php foreach ($stats as $row): ?>
                <tr class="group hover:bg-white/[0.02] transition-colors">
                    <td class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-indigo-600/20 flex items-center justify-center font-sport text-indigo-400">VS</div>
                            <span class="text-lg font-semibold text-white group-hover:text-indigo-400 transition-colors">
                                <?= htmlspecialchars($row['equipe1']) ?> 
                                <span class="text-gray-500 font-normal px-1">v</span> 
                                <?= htmlspecialchars($row['equipe2']) ?>
                            </span>
                        </div>
                    </td>
                    <td class="p-6 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/5 text-gray-300 font-mono">
                            <?= (int)$row['billets_vendus'] ?>
                        </span>
                    </td>
                    <td class="p-6 text-right">
                        <span class="text-xl font-bold text-emerald-400 font-mono">
                            <?= number_format($row['chiffre_affaires'], 2) ?> <span class="text-[10px] uppercase opacity-50 ml-1">EUR</span>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Ajout d'un récapitulatif global en bas de page -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="glass-card p-6 rounded-2xl">
            <p class="text-xs font-sport text-gray-500 uppercase mb-2">Total Billets</p>
            <p class="text-3xl font-bold text-white"><?= array_sum(array_column($stats, 'billets_vendus')) ?></p>
        </div>
        <div class="glass-card p-6 rounded-2xl border-l-4 border-emerald-500">
            <p class="text-xs font-sport text-gray-500 uppercase mb-2">Chiffre d'Affaires Global</p>
            <p class="text-3xl font-bold text-emerald-400"><?= number_format(array_sum(array_column($stats, 'chiffre_affaires')), 2) ?> €</p>
        </div>
    </div>

    <?php endif; ?>

    <div class="mt-12 text-center">
        <a href="dashboard.php" class="inline-flex items-center text-sm font-sport text-gray-500 hover:text-white transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Retour au Dashboard
        </a>
    </div>
</div>

</body>
</html>
