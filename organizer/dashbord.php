<?php
session_start();

// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisateur') {
//     header("Location: ../auth/login.php");
//     exit;
// }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Organisateur | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-indigo-700 text-white flex flex-col">
        <div class="p-6 text-2xl font-bold border-b border-indigo-500">
            BuyMatch
        </div>

        <nav class="flex-1 p-4 space-y-3">
            <a href="dashboard.php" class="block px-4 py-2 rounded bg-indigo-600">
                 Dashboard
            </a>
            <a href="create_match.php" class="block px-4 py-2 rounded hover:bg-indigo-600">
                 Créer un match
            </a>
            <a href="stats.php" class="block px-4 py-2 rounded hover:bg-indigo-600">
                 Statistiques
            </a>
        </nav>

        <div class="p-4 border-t border-indigo-500">
            <a href="../auth/logout.php" class="block text-center bg-red-500 py-2 rounded">
                Déconnexion
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-8">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    Bonjour <?= $_SESSION['nom']; ?> 
                </h1>
                <p class="text-gray-500">Rôle : Organisateur</p>
            </div>
        </div>

        <!-- KPI CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="text-gray-500">Matchs créés</h3>
                <p class="text-3xl font-bold text-indigo-600">4</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="text-gray-500">Billets vendus</h3>
                <p class="text-3xl font-bold text-green-600">320</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="text-gray-500">Chiffre d'affaires</h3>
                <p class="text-3xl font-bold text-blue-600">25 000 MAD</p>
            </div>

        </div>

        <!-- QUICK ACTIONS -->
        <div class="bg-white p-6 rounded-xl shadow">
            <h2 class="text-xl font-semibold mb-4">Actions rapides</h2>

            <div class="flex gap-4">
                <a href="create_match.php"
                   class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition">
                     Nouveau match
                </a>

                <a href="stats.php"
                   class="bg-gray-200 px-6 py-3 rounded-lg hover:bg-gray-300 transition">
                     Voir statistiques
                </a>
            </div>
        </div>

    </main>

</div>

</body>
</html>
