<?php

    require_once BASE_PATH . "/config/database.php";
    require_once BASE_PATH . "/classes/User.php";
    require_once BASE_PATH . "/classes/Acheteur.php";

    /* Sécurité */
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'acheteur') {
        header("Location: index.php?page=login");
        exit;
    }

    /* DB */
    $db = Database::connect();

    /* Infos utilisateur */

    $stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
       header("Location: index.php?page=logout");
       exit;
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
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-orbitron { font-family: 'Orbitron', sans-serif; }
        
        body {
            background: linear-gradient(135deg, #050505 0%, #0a0e27 100%);
            color: #e4e4e7;
        }

        .navbar {
            background: rgba(5, 5, 5, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 255, 255, 0.1);
        }

        .navbar a {
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #00ffff, #6366f1);
            transition: width 0.3s ease;
        }

        .navbar a:hover::after {
            width: 100%;
        }

        .glass-card {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            background: rgba(15, 23, 42, 0.9);
            border-color: rgba(0, 255, 255, 0.3);
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.1);
        }

        .gradient-text {
            background: linear-gradient(135deg, #00ffff, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00ffff, #6366f1);
            color: #050505;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.3);
        }

        table tbody tr {
            border-bottom: 1px solid rgba(0, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        table tbody tr:hover {
            background: rgba(0, 255, 255, 0.05);
            box-shadow: inset 0 0 15px rgba(0, 255, 255, 0.05);
        }

        .badge {
            background: rgba(99, 102, 241, 0.2);
            border: 1px solid rgba(99, 102, 241, 0.5);
            color: #a5b4fc;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .empty-state {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(10px);
            border: 2px dashed rgba(0, 255, 255, 0.2);
        }
    </style>
</head>

<body>

    <!-- Navbar professionnelle -->
    <nav class="navbar fixed top-0 left-0 right-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="font-orbitron text-2xl font-bold gradient-text">
                BuyMatch
            </div>
            <div class="flex gap-8 items-center">
                <a href="index.php" class="text-gray-300 hover:text-white">Accueil</a>
                <a href="index.php?page=matchs" class="text-gray-300 hover:text-white">Matchs</a>
                <a href="index.php?page=mes_billets" class="text-cyan-400 font-semibold">Mes Billets</a>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-400 to-indigo-600 flex items-center justify-center font-orbitron font-bold text-white">
                        <?= strtoupper(substr($user['nom'], 0, 2)) ?>
                    </div>
                    <a href="index.php?page=logout" class="text-gray-300 hover:text-white text-sm">Déconnexion</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="pt-24 px-6 pb-12">

        <div class="max-w-6xl mx-auto">
             <div class="mb-8 flex items-center justify-between">
        <a href="index.php?page=home" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors group">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span class="font-medium">Retour au Matchs </span>
        </a>
    </div>
            <!-- Titre avec gradient -->
            <h1 class="text-4xl font-orbitron font-black gradient-text mb-2">
                Mes Billets
            </h1>
            <p class="text-gray-400 mb-8">Retrouvez tous vos billets achetés</p>

            <?php if (empty($billets)): ?>
                <!-- État vide stylisé -->
                <div class="empty-state p-12 rounded-xl text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2 1m2-1l-2-1m2 1v2.5"></path>
                    </svg>
                    <h2 class="text-xl font-orbitron text-gray-300 mb-2">Aucun billet acheté</h2>
                    <p class="text-gray-500 mb-6">Explorez nos matchs et achetez vos premiers billets maintenant</p>
                    <a href="index.php?page=matchs" class="btn-primary inline-block px-6 py-3 rounded-lg">
                        Voir les matchs
                    </a>
                </div>
            <?php else: ?>
                <!-- Tableau glassmorphism -->
                <div class="glass-card rounded-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gradient-to-r from-cyan-500/20 to-indigo-600/20 border-b border-cyan-500/20">
                                    <th class="p-4 text-left font-orbitron font-bold text-cyan-400">Match</th>
                                    <th class="p-4 text-center font-orbitron font-bold text-cyan-400">Date</th>
                                    <th class="p-4 text-center font-orbitron font-bold text-cyan-400">Catégorie</th>
                                    <th class="p-4 text-center font-orbitron font-bold text-cyan-400">Place</th>
                                    <th class="p-4 text-center font-orbitron font-bold text-cyan-400">Prix</th>
                                    <th class="p-4 text-center font-orbitron font-bold text-cyan-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($billets as $b): ?>
                                    <tr>
                                        <td class="p-4 font-semibold text-white">
                                            <?= htmlspecialchars($b['equipe1']) ?> vs <?= htmlspecialchars($b['equipe2']) ?>
                                        </td>
                                        <td class="p-4 text-center text-gray-300">
                                            <?= date('d/m/Y', strtotime($b['date_heure'])) ?>
                                        </td>
                                        <td class="p-4 text-center">
                                            <span class="badge">
                                                <?= htmlspecialchars($b['categorie']) ?>
                                            </span>
                                        </td>
                                        <td class="p-4 text-center text-gray-300 font-mono">
                                            #<?= $b['numero_place'] ?>
                                        </td>
                                        <td class="p-4 text-center font-bold text-cyan-400">
                                            <?= number_format($b['prix'], 2) ?> DH
                                        </td>
                                        <td class="p-4 text-center">
                                            <a href="index.php?page=ticket_print&ticket_id=<?= $b['id'] ?>"
                                                target="_blank"
                                                class="btn-primary px-4 py-2 rounded-lg text-sm inline-block">
                                                Imprimer
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
       
    </div>

</body>
</html>
