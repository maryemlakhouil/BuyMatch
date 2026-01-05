<?php
    session_start();
    require_once "../config/database.php";
    require_once "../classes/Admin.php";

    // Sécurité
    // if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    //     header("Location: ../auth/login.php");
    //     exit;
    // }

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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col">
        <div class="p-6 text-2xl font-bold border-b border-gray-700">
            BuyMatch Admin
        </div>

        <nav class="flex-1 p-4 space-y-3">
            <a href="../admin/dashbord.php" class="block px-4 py-2 rounded bg-gray-700">
                 Dashboard
            </a>
            <a href="../admin/users.php" class="block px-4 py-2 rounded hover:bg-gray-700">
                 Utilisateurs
            </a>
            <a href="../admin/validate_match.php" class="block px-4 py-2 rounded hover:bg-gray-700">
                 Matchs
            </a>
            <a href="../admin/comments.php" class="block px-4 py-2 rounded hover:bg-gray-700">
                 Commentaires
            </a>
        </nav>

        <div class="p-4 border-t border-gray-700">
            <a href="../auth/logout.php"
               class="block text-center bg-red-600 py-2 rounded">
                Déconnexion
            </a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 p-8">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">
                    Bienvenue <?= htmlspecialchars($user['nom']) ?>
                </h1>
                <p class="text-gray-500">Rôle : Administrateur</p>
            </div>
        </div>

        <!-- STATS --><div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">

        <div class="bg-white p-6 rounded-xl shadow text-center">
            <h3 class="text-gray-500">Matchs créés</h3>
            <p class="text-3xl font-bold text-indigo-600"><?= $stats['total_matchs'] ?></p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow text-center">
            <h3 class="text-gray-500">Billets vendus</h3>
            <p class="text-3xl font-bold text-green-600"><?= $stats['total_billets'] ?></p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow text-center">
            <h3 class="text-gray-500">Chiffre d'affaires</h3>
            <p class="text-3xl font-bold text-blue-600"><?= number_format($stats['chiffre_affaires'], 2) ?> DH</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow text-center">
            <h3 class="text-gray-500">Utilisateurs</h3>
            <p class="text-3xl font-bold text-purple-600"><?= $stats['total_utilisateurs'] ?></p>
        </div>

    </div>

    </main>
</div>

</body>
</html>
