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
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Application du thème Cyber Sport Dark */
        body {
            background-color: #050505;
            color: #e5e7eb;
            font-family: 'Inter', sans-serif;
        }
        .sport-title {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .btn-gradient-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transition: all 0.3s ease;
        }
        .btn-gradient-red {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            transition: all 0.3s ease;
        }
        .status-dot {
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
        }
    </style>
</head>

<body class="min-h-screen p-4 md:p-8">

<div class="max-w-6xl mx-auto">
    <!-- Ajout du bouton retour au dashboard -->
    <div class="mb-8 flex items-center justify-between">
        <a href="dashboard.php" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span class="font-medium">Retour au Dashboard</span>
        </a>
    </div>

    <h1 class="text-3xl md:text-4xl font-black mb-8 sport-title bg-clip-text text-transparent bg-gradient-to-r from-emerald-400 to-cyan-500">
         Validation des matchs
    </h1>

    <?php if (empty($matchs)): ?>
        <div class="glass-card p-12 rounded-2xl text-center border-dashed border-2 border-emerald-500/20">
            <div class="inline-flex p-4 rounded-full bg-emerald-500/10 mb-4 status-dot">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2 class="text-xl font-bold mb-2">Tout est à jour !</h2>
            <p class="text-gray-400">Il n'y a aucun match en attente de validation pour le moment.</p>
        </div>
    <?php else: ?>

        <div class="glass-card rounded-2xl border border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-white/5 border-b border-white/5">
                        <tr>
                            <th class="p-5 text-xs font-bold uppercase tracking-wider text-gray-400">Match</th>
                            <th class="p-5 text-xs font-bold uppercase tracking-wider text-gray-400">Date & Heure</th>
                            <th class="p-5 text-xs font-bold uppercase tracking-wider text-gray-400">Lieu</th>
                            <th class="p-5 text-xs font-bold uppercase tracking-wider text-gray-400">Organisateur</th>
                            <th class="p-5 text-xs font-bold uppercase tracking-wider text-center text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                    <?php foreach ($matchs as $m): ?>
                        <tr class="hover:bg-white/5 transition-colors group">
                            <td class="p-5">
                                <div class="flex flex-col">
                                    <span class="text-lg font-bold text-white group-hover:text-emerald-400 transition-colors">
                                        <?= htmlspecialchars($m['equipe1']) ?> vs <?= htmlspecialchars($m['equipe2']) ?>
                                    </span>
                                    <span class="text-xs text-emerald-500/60 font-mono">MATCH_ID: #<?= $m['id'] ?></span>
                                </div>
                            </td>
                            <td class="p-5">
                                <div class="flex items-center gap-2 text-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="font-mono text-sm"><?= date('d/m/Y H:i', strtotime($m['date_heure'])) ?></span>
                                </div>
                            </td>
                            <td class="p-5 text-gray-400">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <?= htmlspecialchars($m['lieu']) ?>
                                </div>
                            </td>
                            <td class="p-5">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-xs font-bold text-white uppercase">
                                        <?= substr(htmlspecialchars($m['organisateur']), 0, 2) ?>
                                    </div>
                                    <span class="text-gray-300 font-medium"><?= htmlspecialchars($m['organisateur']) ?></span>
                                </div>
                            </td>
                            <td class="p-5">
                                <form method="POST" class="flex justify-center gap-3">
                                    <input type="hidden" name="match_id" value="<?= $m['id'] ?>">
                                    <button name="action" value="valide"
                                            class="btn-gradient-green text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/20 hover:scale-105 active:scale-95 uppercase tracking-wider">
                                        Valider
                                    </button>
                                    <button name="action" value="refuse"
                                            class="btn-gradient-red text-white px-5 py-2 rounded-xl text-sm font-bold shadow-lg shadow-red-500/20 hover:scale-105 active:scale-95 uppercase tracking-wider">
                                        Refuser
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
