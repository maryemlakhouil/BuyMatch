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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Modération</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #e5e7eb; }
        .font-sport { font-family: 'Orbitron', sans-serif; text-transform: uppercase; letter-spacing: 0.05em; }
        .glass-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body class="min-h-screen p-4 md:p-8">

<div class="max-w-7xl mx-auto">
    <!-- Header avec retour -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
        <div>
            <a href="dashbord.php" class="inline-flex items-center text-sm text-blue-400 hover:text-blue-300 transition-colors mb-2 group">
                <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Retour au Dashboard
            </a>
            <h1 class="text-3xl font-sport bg-gradient-to-r from-blue-500 to-indigo-500 bg-clip-text text-transparent">
                Modération des Commentaires
            </h1>
        </div>
    </div>

    <!-- Table de modération -->
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/5">
                <thead class="bg-white/5">
                    <tr class="text-left text-xs font-sport text-gray-400 uppercase tracking-wider">
                        <th class="p-4">Utilisateur</th>
                        <th class="p-4">Match</th>
                        <th class="p-4">Note</th>
                        <th class="p-4">Commentaire</th>
                        <th class="p-4">Date</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php if (empty($commentaires)): ?>
                    <tr>
                        <td colspan="6" class="p-12 text-center text-gray-500">Aucun commentaire à modérer.</td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php foreach ($commentaires as $c): ?>
                    <tr class="hover:bg-white/5 transition-colors group">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 text-xs font-bold ring-1 ring-indigo-500/30">
                                    <?= strtoupper(substr($c['utilisateur'], 0, 1)) ?>
                                </div>
                                <span class="font-medium text-gray-200"><?= htmlspecialchars($c['utilisateur']) ?></span>
                            </div>
                        </td>
                        <td class="p-4 text-sm text-gray-300">
                            <span class="block font-semibold text-blue-400"><?= htmlspecialchars($c['equipe1']) ?></span>
                            <span class="text-xs text-gray-500">vs</span>
                            <span class="block font-semibold text-blue-400"><?= htmlspecialchars($c['equipe2']) ?></span>
                        </td>
                        <td class="p-4 text-sm">
                            <div class="flex items-center text-yellow-500">
                                <span class="font-bold text-lg"><?= $c['note'] ?></span>
                                <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            </div>
                        </td>
                        <td class="p-4">
                            <div class="max-w-xs text-sm text-gray-400 leading-relaxed italic">
                                "<?= nl2br(htmlspecialchars($c['contenu'])) ?>"
                            </div>
                        </td>
                        <td class="p-4 text-xs text-gray-500">
                            <?= date('d/m/Y', strtotime($c['date_commentaire'])) ?>
                            <span class="block opacity-50"><?= date('H:i', strtotime($c['date_commentaire'])) ?></span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="?delete=<?= $c['id'] ?>"
                               onclick="return confirm('Confirmer la suppression définitive de ce commentaire ?')"
                               class="inline-flex items-center px-4 py-2 rounded-lg bg-red-500/10 text-red-500 text-xs font-bold hover:bg-red-500 hover:text-white transition-all ring-1 ring-red-500/20">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Supprimer
                            </a>
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
