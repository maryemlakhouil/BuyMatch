<?php

    session_start();

    require_once BASE_PATH ."/config/database.php";
    require_once BASE_PATH ."/classes/Organisateur.php";

    $db = Database::connect();
    
    // Vérification sécurité : accès organisateur uniquement
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisateur') {
        header("Location: ../auth/login.php");
        exit;
    }

    /* Infos organisateur */

    $stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Organisateur introuvable !!");
    }

    /* Objet POO */

    $organisateur = new Organisateur($_SESSION['user_id'],$user['nom'],$user['email'],'');

    /* Récupérer les matchs */
    $stmt = $db->prepare("
        SELECT id, equipe1, equipe2
        FROM matches
        WHERE organisateur_id = ?
        ORDER BY date_heure DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $matches = $stmt->fetchAll();

    /* Avis */
    $avis = [];
    $matchSelectionne = null;

    if (isset($_GET['match_id'])) {
        $matchSelectionne = (int) $_GET['match_id'];
        $avis = $organisateur->consulterAvis($matchSelectionne);
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Organisateur | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-sport { font-family: 'Orbitron', sans-serif; }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar-gradient {
            background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
        }
    </style>
</head>

<body class="bg-[#050505] text-gray-100">
<div class="flex min-h-screen">

<!-- SIDEBAR -->
<aside class="w-72 sidebar-gradient border-r border-white/10 flex flex-col hidden md:flex">
    <div class="p-8">
        <div class="text-3xl font-sport font-bold tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-500">
            BUYMATCH
        </div>
        <p class="text-[10px] text-gray-500 font-sport mt-1 tracking-[0.2em] uppercase">Pro Organizer Suite</p>
    </div>

    <nav class="flex-1 px-4 space-y-2">
        <a href="dashbord.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 font-semibold transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
            Dashboard
        </a>
        <a href="create_match.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Créer un match
        </a>
        <a href="stats.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            Statistiques
        </a>
        <a href="../pages/profile.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/5 text-gray-400 hover:text-white transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            Votre Profil
        </a>
    </nav>

    <div class="p-6">
        <a href="../auth/logout.php" class="flex items-center justify-center gap-2 w-full bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white py-3 rounded-xl border border-red-500/20 transition-all font-semibold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            Déconnexion
        </a>
    </div>
</aside>

<!-- MAIN -->
<main class="flex-1 p-6 md:p-12 max-w-6xl">
    <header class="mb-12">
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-2">
            Bonjour, <span class="italic text-indigo-400"><?= htmlspecialchars($user['nom']); ?></span>
        </h1>
        <p class="text-gray-500 font-medium">Gérez vos matchs et consultez les retours de la communauté.</p>
    </header>
 
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <h2 class="text-xl font-sport font-bold uppercase tracking-widest text-indigo-500 flex items-center gap-3">
            <span class="w-8 h-[2px] bg-indigo-500"></span>
            Avis & Commentaires
        </h2>

        <form method="GET" id="matchFilter">
            <div class="relative group">
                <select name="match_id" 
                        class="appearance-none bg-[#111] border border-white/10 text-white px-6 py-3 pr-12 rounded-xl focus:outline-none focus:border-indigo-500 transition-all cursor-pointer" 
                        onchange="this.form.submit()">
                    <option value="">Sélectionner un match</option>
                    <?php foreach ($matches as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= ($matchSelectionne == $m['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['equipe1']) ?> vs <?= htmlspecialchars($m['equipe2']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500 group-hover:text-indigo-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>
        </form>
    </div>

    <?php if ($matchSelectionne): ?>
        <?php if (empty($avis)): ?>
            <div class="glass-card p-8 rounded-2xl border-yellow-500/20 flex items-center gap-4 text-yellow-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="font-medium">Aucun avis n'a été publié pour ce match pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="grid gap-6">
                <?php foreach ($avis as $a): ?>
                    <div class="glass-card p-8 rounded-2xl hover:bg-white/[0.05] transition-all group border border-white/5 hover:border-indigo-500/30">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-lg uppercase shadow-lg shadow-indigo-500/20">
                                    <?= substr(htmlspecialchars($a['nom']), 0, 1) ?>
                                </div>
                                <div>
                                    <div class="font-bold text-white text-lg"><?= htmlspecialchars($a['nom']) ?></div>
                                    <div class="text-xs text-gray-500 uppercase tracking-widest font-semibold">
                                        Posté le <?= date('d M Y à H:i', strtotime($a['date_commentaire'])) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-1 bg-black/40 px-4 py-2 rounded-full border border-white/5">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <svg class="w-5 h-5 <?= $i <= (int)$a['note'] ? 'text-yellow-400 fill-yellow-400' : 'text-gray-700' ?>" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="relative italic text-gray-300 leading-relaxed text-lg pl-6 border-l-2 border-indigo-500/30">
                            "<?= nl2br(htmlspecialchars($a['contenu'])) ?>"
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="flex flex-col items-center justify-center py-24 glass-card rounded-3xl border-dashed border-2 border-white/10 opacity-60">
            <div class="w-20 h-20 bg-indigo-500/10 rounded-full flex items-center justify-center text-indigo-500 mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" /></svg>
            </div>
            <p class="text-xl font-medium text-gray-400">Veuillez sélectionner un match pour voir les avis</p>
        </div>
    <?php endif; ?>

</main>
</div>
</body>
</html>
