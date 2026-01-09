<?php
session_start();
require_once BASE_PATH ."/config/database.php";
require_once BASE_PATH ."/classes/User.php";
require_once BASE_PATH ."/classes/Acheteur.php";
require_once BASE_PATH ."/classes/Organisateur.php";
require_once BASE_PATH . "/classes/Admin.php";



if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $db = Database::connect();

    $sql = "SELECT * FROM users WHERE email = :email AND is_active = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':email' => $email]);

    $userData = $stmt->fetch();

    if ($userData && password_verify($password, $userData['password'])) {

        // Création de l'objet selon le rôle
        switch ($userData['role']) {
            case 'admin':
                $user = new Admin(
                    $userData['id'],
                    $userData['nom'],
                    $userData['email'],
                    $userData['password'],
                    $userData['role']
                );
                break;

            case 'organisateur':
                $user = new Organisateur(
                    $userData['id'],
                    $userData['nom'],
                    $userData['email'],
                    $userData['password'],
                    $userData['role']
                );
                break;

            default:
                $user = new Acheteur(
                    $userData['id'],
                    $userData['nom'],
                    $userData['email'],
                    $userData['password'],
                    $userData['role'],
                    $userData['estActif']
                );
        }

        $user->seConnecter();
        // 
        header("Location: ../index.php");
        exit;
    }

    $error = "Email ou mot de passe incorrect";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-600 to-blue-800 flex items-center justify-center">
 
<div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8">
    
    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-indigo-700">BuyMatch</h1>
        <p class="text-gray-500 mt-2">Accédez à votre espace</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" required
                   class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
            <input type="password" name="password" required
                   class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 rounded-lg font-semibold hover:bg-indigo-700 transition">
            Se connecter
        </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
        Pas encore de compte ?
        <a href="register.php" class="text-indigo-600 font-semibold hover:underline">
            Créer un compte
        </a>
    </p>
</div>

</body>
</html> 
