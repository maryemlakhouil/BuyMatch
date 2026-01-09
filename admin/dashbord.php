<?php
    session_start();
    
    require_once  BASE_PATH . "/config/database.php";
    require_once  BASE_PATH . "/classes/Admin.php";

    // Sécurité
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../auth/login.php");
        exit;
    }

    $db = Database::connect();

    /* Infos admin */
    $stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ? ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Administrateur introuvable !! ");
    }

    // Objet Admin 
    $admin = new Admin($_SESSION['user_id'],$user['nom'],$user['email'],'');

    /* Statistiques globales */
    $stats = $admin->statistiquesGlobales();
    
   /* 5 derniers inscrits */

    $recentUsers = $db->query("
        SELECT nom, email, date_creation FROM users 
        ORDER BY date_creation DESC 
        LIMIT 5
    ")->fetchAll();

    /* Matchs en attente */

    $pendingMatches = $db->query("
        SELECT m.id,m.equipe1,m.equipe2,m.date_heure,u.nom AS organisateur
        FROM matches m
        JOIN users u ON m.organisateur_id = u.id
        WHERE m.statut = 'en_attente'
        ORDER BY m.date_heure DESC
        LIMIT 5
    ")->fetchAll();

    /* Derniers commentaires */

    $recentComments = $db->query("
        SELECT  c.contenu,c.note,c.date_commentaire,u.nom
        FROM commentaires c
        JOIN users u ON c.user_id = u.id
        ORDER BY c.date_commentaire DESC
        LIMIT 5
    ")->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-dark: #050505;
            --card-dark: #0f0f0f;
            --primary: #4f46e5;
            --accent: #818cf8;
        }
        body { font-family: 'Inter', sans-serif; }
        .font-sport { font-family: 'Orbitron', sans-serif; }
        .glass-card {
            background: rgba(15, 15, 15, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .sidebar-gradient {
            background: linear-gradient(180deg, #0f0f0f 0%, #050505 100%);
        }
    </style>
</head>

<body class="bg-[#050505] text-gray-200 min-h-screen">

    <div class="flex min-h-screen">

        <aside class="w-72 sidebar-gradient border-r border-white/5 flex flex-col hidden lg:flex">
            <div class="p-8">
                <div class="text-2xl font-sport font-bold tracking-tighter text-white">
                    BUY<span class="text-indigo-500">MATCH</span>
                    <div class="text-[10px] text-indigo-400 tracking-[0.3em] font-sans uppercase opacity-70">Admin Panel</div>
                </div>
            </div>

            <nav class="flex-1 px-6 space-y-2">
                <a href="../admin/dashbord.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-600/10 text-indigo-400 border border-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Dashboard
                </a>
                <a href="../admin/users.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 01-12 0v1z"/></svg>
                    Utilisateurs
                </a>
                <a href="../admin/validate_match.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Matchs
                </a>
                <a href="../admin/comments.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-400 hover:bg-white/5 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    Commentaires
                </a>
            </nav>

            <div class="p-6">
                <a href="../auth/logout.php" class="flex items-center justify-center gap-2 w-full bg-red-500/10 text-red-500 py-3 rounded-xl border border-red-500/20 hover:bg-red-500 hover:text-white transition-all font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Déconnexion
                </a>
            </div>
        </aside>

        <!-- MAIN -->
        <main class="flex-1 p-6 md:p-12">

            <!-- HEADER -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-12">
                <div>
                    <h1 class="text-4xl font-sport font-bold text-white mb-2 uppercase italic tracking-tight">
                        Welcome, <span class="text-indigo-500"><?= htmlspecialchars($user['nom']) ?></span>
                    </h1>
                    <p class="text-gray-500 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                        Access Level: System Administrator
                    </p>
                </div>
                
                <div class="flex items-center gap-4 bg-white/5 p-2 rounded-2xl border border-white/10">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-white">
                        <?= strtoupper(substr($user['nom'], 0, 1)) ?>
                    </div>
                    <div class="pr-4">
                        <div class="text-sm font-bold text-white leading-none mb-1"><?= htmlspecialchars($user['nom']) ?></div>
                        <div class="text-[10px] text-gray-500 uppercase tracking-widest leading-none"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                </div>
            </div>

            <!-- STATS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">

                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform">
                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path></svg>
                    </div>
                    <h3 class="text-gray-400 text-sm font-medium uppercase tracking-widest mb-4">Total Users</h3>
                    <p class="text-4xl font-sport font-bold text-white"><?= number_format($stats['total_utilisateurs']) ?></p>
                    <div class="mt-4 text-xs text-indigo-400 font-bold uppercase">Community Growth</div>
                </div>

                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform text-indigo-500">
                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1a1 1 0 112 0v1a1 1 0 11-2 0zM13 16v-1a1 1 0 112 0v1a1 1 0 11-2 0zM14.536 14.536a1 1 0 01-1.414 0l-.707-.707a1 1 0 011.414-1.414l.707.707a1 1 0 010 1.414zM6.464 14.536a1 1 0 010-1.414l.707-.707a1 1 0 011.414 1.414l-.707.707a1 1 0 01-1.414 0z"></path></svg>
                    </div>
                    <h3 class="text-gray-400 text-sm font-medium uppercase tracking-widest mb-4">Live Matches</h3>
                    <p class="text-4xl font-sport font-bold text-white"><?= number_format($stats['total_matchs']) ?></p>
                    <div class="mt-4 text-xs text-indigo-400 font-bold uppercase">Event Inventory</div>
                </div>

                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform text-emerald-500">
                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path></svg>
                    </div>
                    <h3 class="text-gray-400 text-sm font-medium uppercase tracking-widest mb-4">Tickets Sold</h3>
                    <p class="text-4xl font-sport font-bold text-white"><?= number_format($stats['total_billets']) ?></p>
                    <div class="mt-4 text-xs text-indigo-400 font-bold uppercase">Booking Volume</div>
                </div>

                <div class="glass-card p-8 rounded-3xl relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:scale-110 transition-transform text-amber-500">
                        <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg>
                    </div>
                    <h3 class="text-gray-400 text-sm font-medium uppercase tracking-widest mb-4">Net Revenue</h3>
                    <p class="text-4xl font-sport font-bold text-white"><?= number_format($stats['chiffre_affaires'], 2) ?> <span class="text-lg font-sans text-indigo-400">DH</span></p>
                    <div class="mt-4 text-xs text-indigo-400 font-bold uppercase">Total turnover</div>
                </div>

            </div>
            
            <!-- NEW SECTIONS: L'essence d'un vrai dashboard -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- SECTION: MATCHS EN ATTENTE -->
                <div class="glass-card rounded-3xl overflow-hidden">
                    <div class="p-6 border-b border-white/5 flex justify-between items-center bg-white/5">
                        <h2 class="font-sport font-bold text-lg text-white uppercase tracking-wider italic">Action Required: Match Validation</h2>
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-500 text-[10px] font-bold rounded-full border border-amber-500/20 uppercase">Pending</span>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <?php foreach($pendingMatches as $match): ?>
                            <div class="flex items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/5 hover:border-indigo-500/30 transition-all group">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z"/></svg>
                                    </div>
                                    <div class="font-bold text-white">
                                        <?= htmlspecialchars($match['equipe1']) ?> vs <?= htmlspecialchars($match['equipe2']) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        By <?= htmlspecialchars($match['organisateur']) ?> • <?= date('d M', strtotime($match['date_heure'])) ?>
                                    </div>

                                </div>
                                <a href="validate_match.php" class="p-2 bg-indigo-500 rounded-lg text-white opacity-0 group-hover:opacity-100 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </a>
                            </div>
                        <?php endforeach; if(empty($pendingMatches)) echo "<p class='text-center text-gray-500 py-4 italic'>No pending matches</p>"; ?>
                    </div>
                </div>
            </div>

            <!-- SECTION: DERNIERS INSCRITS -->
            <div class="glass-card rounded-3xl overflow-hidden">
                <div class="p-6 border-b border-white/5 flex justify-between items-center bg-white/5">
                    <h2 class="font-sport font-bold text-lg text-white uppercase tracking-wider italic">New Recruits</h2>
                    <a href="users.php" class="text-xs text-indigo-400 hover:text-white transition-colors uppercase font-bold tracking-widest">View All</a>
                </div>
                <div class="p-6">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-[10px] text-gray-500 uppercase tracking-widest">
                                <th class="pb-4 font-medium">User</th>
                                <th class="pb-4 font-medium">Joined</th>
                                <th class="pb-4 font-medium text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <?php foreach($recentUsers as $u): ?>
                            <tr class="group">
                                <td class="py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-600/20 flex items-center justify-center text-[10px] font-bold text-indigo-400">
                                            <?= strtoupper(substr($u['nom'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-white"><?= htmlspecialchars($u['nom']) ?></div>
                                            <div class="text-[10px] text-gray-500"><?= htmlspecialchars($u['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                           <td class="py-4 text-xs text-gray-400"><?= date('d/m/Y', strtotime($u['date_creation'])) ?></td>

                                <td class="py-4 text-right">
                                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION: DERNIERS COMMENTAIRES (Modération rapide) -->
            <div class="glass-card rounded-3xl overflow-hidden lg:col-span-2">
                <div class="p-6 border-b border-white/5 flex justify-between items-center bg-white/5">
                    <h2 class="font-sport font-bold text-lg text-white uppercase tracking-wider italic">Feed Activity</h2>
                    <div class="flex gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span class="w-2 h-2 rounded-full bg-white/10"></span>
                        <span class="w-2 h-2 rounded-full bg-white/10"></span>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach($recentComments as $com): ?>
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/5 flex flex-col justify-between">
                        <p class="text-sm text-gray-300 italic mb-4">"<?= htmlspecialchars(substr($com['contenu'], 0, 80)) ?>..."</p>
                        <div class="flex items-center justify-between mt-auto">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-indigo-500 flex items-center justify-center text-[8px] font-bold"><?= strtoupper(substr($com['nom'], 0, 1)) ?></div>
                                <span class="text-xs font-bold text-white"><?= htmlspecialchars($com['nom']) ?></span>
                            </div>
                            <div class="flex text-amber-400">
                                <?php for($i=0; $i<5; $i++) echo $i < $com['note'] ? "★" : "☆"; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

    </main>
</div>

</body>
</html>
