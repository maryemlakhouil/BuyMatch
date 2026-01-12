<?php

    require_once  BASE_PATH . "/config/database.php";
    require_once  BASE_PATH . "/classes/Acheteur.php";


    $db = Database::connect();

    // Filtre exemple
    $lieuFilter = $_GET['lieu'] ?? '';
    $equipeFilter = $_GET['equipe'] ?? '';

    // Récupérer matchs publiés
    $matchs = Acheteur::listerMatchsDisponibles();

    // Filtrage simple
    if ($lieuFilter) {
        $matchs = array_filter($matchs, fn($m) => stripos($m['lieu'], $lieuFilter) !== false);
    }
    if ($equipeFilter) {
        $matchs = array_filter($matchs, fn($m) => stripos($m['equipe1'], $equipeFilter) !== false || stripos($m['equipe2'], $equipeFilter) !== false);
    }

    // Récupérer catégories pour chaque match
    $matchCategories = [];
    foreach ($matchs as $m) {
        $matchCategories[$m['id']] = Acheteur::getCategoriesMatch($m['id']);
    }

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BuyMatch – Billetterie sportive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, .hero-title, .section-title {
            font-family: 'Orbitron', sans-serif;
            font-weight: 900;
            letter-spacing: 0.05em;
        }
        body {
            background: linear-gradient(135deg, #050505 0%, #0a0a14 100%);
            color: #e5e7eb;
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(99, 102, 241, 0.2);
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            background: rgba(15, 23, 42, 0.6);
            border-color: rgba(99, 102, 241, 0.5);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
        }
        .gradient-text {
            background: linear-gradient(135deg, #6366f1 0%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.5);
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.5);
            transform: translateY(-2px);
        }
        .team-logo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid rgba(99, 102, 241, 0.3);
        }
        .search-input {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(99, 102, 241, 0.2);
            color: #e5e7eb;
            transition: all 0.3s ease;
        }
        .search-input:focus {
            background: rgba(30, 41, 59, 0.8);
            border-color: rgba(99, 102, 241, 0.5);
            box-shadow: 0 0 12px rgba(99, 102, 241, 0.3);
        }
        .search-input::placeholder {
            color: #9ca3af;
        }
        /* Styles améliorés pour la navbar professionnelle */
        .navbar-link {
            color: #d1d5db;
            font-weight: 500;
            position: relative;
            transition: all 0.3s ease;
        }
        .navbar-link:hover {
            color: #6366f1;
        }
        .navbar-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #6366f1 0%, #06b6d4 100%);
            transition: width 0.3s ease;
        }
        .navbar-link:hover::after {
            width: 100%;
        }
        .menu-toggle {
            display: none;
        }
        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            .nav-menu {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(5, 5, 5, 0.95);
                backdrop-filter: blur(16px);
                border-bottom: 1px solid rgba(99, 102, 241, 0.3);
                padding: 20px;
                flex-direction: column;
                gap: 15px;
                display: none;
            }
            .nav-menu.active {
                display: flex;
            }
        }
    </style>
</head>

<body class="min-h-screen flex flex-col">

    <!-- ================= HEADER / NAVBAR ================= -->

    <header class="sticky top-0 z-50 border-b border-indigo-500/30 bg-black/70 backdrop-blur-2xl">
        <div class="max-w-7xl mx-auto px-6 py-5">
            <div class="flex justify-between items-center">
                <!-- Logo Section -->
                <div class="flex items-center gap-3">
                    <a href="index.php?page=home" class="text-2xl font-black gradient-text hover:scale-105 transition duration-300">
                        BuyMatch
                    </a>
                    <span class="hidden md:block text-xs text-gray-500 uppercase tracking-wider">Billetterie Sportive</span>
                </div>

                <!-- Central Navigation -->
                <nav class="hidden md:flex items-center gap-10">
                    <a href="index.php?page=home" class="navbar-link text-sm">Accueil</a>
                    <a href="index.php?page=home" class="navbar-link text-sm">Matchs</a>
                </nav>

                <!-- Right Section: Auth/User -->
                <div class="flex items-center gap-4">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="index.php?page=login"
                        class="px-4 py-2 rounded-lg text-sm font-semibold text-white navbar-link transition">
                            Connexion
                        </a>
                        <a href="index.php?page=register"
                        class="px-5 py-2 rounded-lg btn-primary text-white font-semibold text-sm">
                            S'inscrire
                        </a>
                    <?php else: ?>
                        <div class="hidden md:flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-sm text-gray-300">Bienvenue</p>
                                <p class="text-xs font-semibold text-indigo-400"><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Utilisateur') ?></p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-indigo-500/20 border border-indigo-500/50 flex items-center justify-center">
                                <span class="text-sm font-bold text-indigo-400"><?= substr(htmlspecialchars($_SESSION['user_nom'] ?? 'U'), 0, 1) ?></span>
                            </div>
                        </div>
                        <a href="index.php?page=logout"
                        class="px-4 py-2 rounded-lg bg-red-600/70 hover:bg-red-600 text-white font-semibold text-sm transition">
                            Déconnexion
                        </a>
                    <?php endif; ?>

                    <!-- Mobile Menu Toggle -->
                    <button class="menu-toggle md:hidden text-indigo-400 hover:text-indigo-300 transition" onclick="toggleMenu()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <nav class="nav-menu md:hidden mt-4 border-t border-indigo-500/20 pt-4">
                <a href="index.php?page=home" class="navbar-link text-sm block py-2">Accueil</a>
                <a href="index.php?page=home" class="navbar-link text-sm block py-2">Matchs</a>
            </nav>
        </div>
    </header>

    <!-- ================= HERO SECTION ================= -->
    <section class="max-w-7xl mx-auto px-6 py-20 text-center">
        <h1 class="hero-title text-5xl md:text-6xl mb-6">
            <span class="gradient-text">Achetez vos billets</span><br>
            <span class="text-cyan-300">en toute simplicité</span>
        </h1>
        <p class="text-gray-400 text-lg max-w-3xl mx-auto leading-relaxed">
            Découvrez les matchs disponibles, comparez les catégories de places et réservez vos billets en quelques clics. 
            Accès illimité aux événements sportifs de première catégorie.
        </p>
    </section>

    <!-- ================= SECTION FILTRES ================= -->
    <section class="max-w-7xl mx-auto px-6 mb-16">
        <div class="glass-card p-8 rounded-2xl">
            <h2 class="section-title text-2xl mb-8 text-center gradient-text">Rechercher vos matchs</h2>
            <form method="GET" class="grid md:grid-cols-3 gap-4">
                <input type="hidden" name="page" value="home">

                <div class="relative">
                    <input type="text" name="lieu"
                        placeholder=" Filtrer par lieu"
                        value="<?= htmlspecialchars($lieuFilter) ?>"
                        class="w-full p-4 rounded-lg search-input">
                </div>

                <div class="relative">
                    <input type="text" name="equipe"
                        placeholder=" Filtrer par équipe"
                        value="<?= htmlspecialchars($equipeFilter) ?>"
                        class="w-full p-4 rounded-lg search-input">
                </div>

                <button type="submit" class="btn-primary px-6 py-4 rounded-lg text-white font-bold">
                    Rechercher
                </button>
            </form>
        </div>
    </section>

    <!-- ================= STATS SECTION ================= -->
    <section class="max-w-7xl mx-auto px-6 mb-16">
        <div class="grid md:grid-cols-3 gap-6">
            <div class="glass-card p-6 rounded-xl text-center">
                <div class="text-3xl font-black gradient-text mb-2"><?= count($matchs) ?></div>
                <p class="text-gray-400">Matchs disponibles</p>
            </div>
            <div class="glass-card p-6 rounded-xl text-center">
                <div class="text-3xl font-black text-emerald-400 mb-2">100%</div>
                <p class="text-gray-400">Paiements sécurisés</p>
            </div>
            <div class="glass-card p-6 rounded-xl text-center">
                <div class="text-3xl font-black text-cyan-400 mb-2">24/7</div>
                <p class="text-gray-400">Support client</p>
            </div>
        </div>
    </section>

    <!-- ================= MATCHS GRID ================= -->
    <main class="max-w-7xl mx-auto px-6 flex-1 mb-12">
        <h2 class="section-title text-3xl mb-8 gradient-text">Événements à venir</h2>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($matchs)): ?>
                <div class="col-span-full glass-card p-16 rounded-2xl text-center">
                    <p class="text-xl text-gray-400"> Aucun match disponible pour le moment.</p>
                    <p class="text-gray-500 mt-2">Revenez bientôt pour découvrir de nouveaux événements !</p>
                </div>
            <?php else: ?>
                <?php foreach ($matchs as $match): ?>
                    <?php
                        $logo1 = !empty($match['logo_equipe1']) ? htmlspecialchars($match['logo_equipe1']) : 'public/img/default-team.jpg';
                        $logo2 = !empty($match['logo_equipe2']) ? htmlspecialchars($match['logo_equipe2']) : 'public/img/default-team.jpg';
                    ?>
                    <div class="glass-card p-8 rounded-2xl flex flex-col justify-between h-full group">
                        <!-- Teams Section -->
                        <div class="flex items-center justify-center gap-4 mb-8">
                            <div class="flex flex-col items-center">
                                <img src="<?= $logo1 ?>" alt="<?= htmlspecialchars($match['equipe1']) ?>" class="team-logo mb-3 group-hover:scale-110 transition">
                                <span class="text-sm font-semibold text-center"><?= htmlspecialchars($match['equipe1']) ?></span>
                            </div>

                            <div class="flex flex-col items-center">
                                <span class="text-indigo-400 font-black text-xl mb-2">VS</span>
                                <span class="text-xs text-gray-500">Match</span>
                            </div>

                            <div class="flex flex-col items-center">
                                <img src="<?= $logo2 ?>" alt="<?= htmlspecialchars($match['equipe2']) ?>" class="team-logo mb-3 group-hover:scale-110 transition">
                                <span class="text-sm font-semibold text-center"><?= htmlspecialchars($match['equipe2']) ?></span>
                            </div>
                        </div>

                        <!-- Match Details Section -->
                        <div class="space-y-3 mb-6 pb-6 border-b border-indigo-500/20">
                            <div class="flex items-center gap-2 text-gray-300">
                                <span>📍</span>
                                <span><?= htmlspecialchars($match['lieu']) ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-300">
                                <span>🗓️</span>
                                <span><?= date('d M Y à H:i', strtotime($match['date_heure'])) ?></span>
                            </div>
                        </div>

                        <!-- Categories Section with Enhanced Styling -->
                        <div class="mb-6">
                            <h3 class="font-bold mb-3 text-indigo-300">Catégories disponibles</h3>
                            <?php if (empty($matchCategories[$match['id']])): ?>
                                <p class="text-gray-500 text-sm">Aucune catégorie disponible</p>
                            <?php else: ?>
                                <div class="space-y-2">
                                    <?php foreach ($matchCategories[$match['id']] as $cat): ?>
                                        <div class="flex justify-between items-center p-2 rounded bg-indigo-500/10 border border-indigo-500/20">
                                            <span class="text-sm text-gray-300"><?= htmlspecialchars($cat['nom']) ?></span>
                                            <span class="font-bold text-emerald-400"><?= number_format($cat['prix'], 2) ?> DH</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <a href="index.php?page=match_details&id=<?= $match['id'] ?>" class="btn-primary w-full py-3 rounded-lg text-center font-bold text-white">
                            Voir les détails →
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

<!-- ================= FOOTER ================= -->
<footer class="border-t border-indigo-500/20 mt-20 bg-black/40">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
            <div>
                <h4 class="font-bold text-indigo-400 mb-4">BuyMatch</h4>
                <p class="text-gray-500 text-sm">Votre plateforme de billetterie sportive en ligne.</p>
            </div>
            <div>
                <h4 class="font-bold text-indigo-400 mb-4">Navigation</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="index.php?page=home" class="hover:text-indigo-400 transition">Accueil</a></li>
                    <li><a href="#" class="hover:text-indigo-400 transition">À propos</a></li>
                    <li><a href="#" class="hover:text-indigo-400 transition">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-indigo-400 mb-4">Légal</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="#" class="hover:text-indigo-400 transition">CGU</a></li>
                    <li><a href="#" class="hover:text-indigo-400 transition">Politique de confidentialité</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-indigo-400 mb-4">Contact</h4>
                <p class="text-sm text-gray-400">Email: info@buymatch.com</p>
                <p class="text-sm text-gray-400">Tél: +212 (0)5 XX XX XX XX</p>
            </div>
        </div>
        <div class="border-t border-indigo-500/20 pt-8 text-center text-gray-500 text-sm">
            © <?= date('Y') ?> BuyMatch — Plateforme de billetterie sportive.
            <br>
            Tous droits réservés. Conçu avec passion pour les amateurs de sport.
        </div>
    </div>
</footer>

<script>
    function toggleMenu() {
        const menu = document.querySelector('.nav-menu');
        menu.classList.toggle('active');
    }
</script>

</body>
</html>
