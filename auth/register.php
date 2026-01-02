<?php
session_start();
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = htmlspecialchars($_POST['nom']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $role = 'acheteur'; 

    if ($email && !empty($password)) {

        $db = Database::connect();

        $sql = "INSERT INTO users (nom, email, password, role)
                VALUES (:nom, :email, :password, :role)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => $role
        ]);

        header("Location: login.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-700 to-indigo-800 flex items-center justify-center">

<div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8">
    
    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-indigo-700">Créer un compte</h1>
        <p class="text-gray-500 mt-2">Rejoignez BuyMatch</p>
    </div>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Nom complet</label>
            <input type="text" name="nom" required
                   class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" required
                   class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
            <input type="password" name="password" required minlength="4"
                   class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
            S'inscrire
        </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
        Déjà inscrit ?
        <a href="login.php" class="text-indigo-600 font-semibold hover:underline">
            Se connecter
        </a>
    </p>
</div>

</body>
</html>
