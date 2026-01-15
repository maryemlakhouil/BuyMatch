<?php

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
       switch ($userData['role']) {
            case 'admin':
                header("Location: index.php?page=admin_dashbord");
                break;

            case 'organisateur':
                header("Location: index.php?page=organisateur_dashbord");
                break;

            default:
                header("Location: index.php?page=home");
                break;
        }
    }
    $error = "Email ou mot de passe incorrect";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, .title {
            font-family: 'Orbitron', sans-serif;
            font-weight: 900;
        }
        body {
            background: linear-gradient(135deg, #050505 0%, #0f0f1e 100%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(15, 15, 30, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(34, 211, 238, 0.2);
            border-radius: 16px;
        }
        .glass-card:hover {
            border-color: rgba(34, 211, 238, 0.5);
            box-shadow: 0 0 20px rgba(34, 211, 238, 0.15);
        }
        .gradient-text {
            background: linear-gradient(135deg, #22d3ee 0%, #6366f1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(34, 211, 238, 0.2);
            color: #fff;
        }
        input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(34, 211, 238, 0.6);
            outline: none;
            box-shadow: 0 0 15px rgba(34, 211, 238, 0.2);
        }
        .btn-gradient {
            background: linear-gradient(135deg, #22d3ee 0%, #6366f1 100%);
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(34, 211, 238, 0.3);
        }
        .btn-gradient:active {
            transform: translateY(0);
        }
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
        }
        label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        a {
            color: #22d3ee;
            transition: all 0.3s ease;
        }
        a:hover {
            color: #6366f1;
            text-decoration: underline;
        }
    </style>
</head>
<body class="flex items-center justify-center py-12 px-4">
 
<div class="glass-card w-full max-w-md p-8">
    
    <div class="text-center mb-8">
        <h1 class="gradient-text text-4xl font-black mb-2">BuyMatch</h1>
        <p class="text-gray-400 text-sm uppercase tracking-widest">Accédez à votre espace</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="error-message p-4 rounded-lg mb-6 text-center text-sm font-medium">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-5">
        <div>
            <label for="email" class="block mb-2">Email</label>
            <input 
                type="email" 
                id="email"
                name="email" 
                required
                placeholder="votre@email.com"
                class="w-full px-4 py-3 rounded-lg transition duration-300">
        </div>

        <div>
            <label for="password" class="block mb-2">Mot de passe</label>
            <input 
                type="password" 
                id="password"
                name="password" 
                required
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-lg transition duration-300">
        </div>

        <button 
            type="submit"
            class="btn-gradient w-full text-white font-bold py-3 rounded-lg uppercase tracking-wider text-sm">
            Se connecter
        </button>
    </form>

    <p class="text-center text-sm text-gray-400 mt-6">
        Pas encore de compte ?
        <a href="index.php?page=register" class="font-semibold hover:text-indigo-400">
            Créer un compte
        </a>
    </p>
</div>

</body>
</html>
