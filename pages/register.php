<?php

require_once BASE_PATH . "/config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = htmlspecialchars($_POST['nom']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $role = 'acheteur';

    if ($email && !empty($password)) {

        $db = Database::connect();

        // 1 - Vérifier si l'email existe déjà
        $check = $db->prepare("SELECT id FROM users WHERE email = :email");
        $check->execute([':email' => $email]);

        if ($check->fetch()) {
            $error = "Cet email est déjà utilisé.";
        } else {

           
            $sql = "INSERT INTO users (nom, email, password, role)
                    VALUES (:nom, :email, :password, :role)";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':nom' => $nom,
                ':email' => $email,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => $role
            ]);

            //  Redirection 
            header("Location: index.php?page=login");
            exit;
        }
    }
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 flex items-center justify-center px-4 py-12" style="font-family: 'Inter', sans-serif;">
    <div class="bg-slate-900 bg-opacity-50 backdrop-blur-xl border border-cyan-500 border-opacity-20 rounded-2xl w-full max-w-md p-8 shadow-2xl hover:border-opacity-40 transition-all duration-300">
        
        <div class="text-center mb-8">
            <h1 class="text-4xl font-black mb-2" style="font-family: 'Orbitron', sans-serif; color: #22d3ee;">BUYMATCH</h1>
            <p class="text-2xl font-bold" style="font-family: 'Orbitron', sans-serif; color: #06b6d4;">Créer un compte</p>
            <p class="text-sm text-slate-400 mt-2">Rejoignez la communauté sportive</p>
        </div>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold mb-2" style="font-family: 'Orbitron', sans-serif; color: #22d3ee;">Nom complet</label>
                <input type="text" name="nom" required class="w-full px-4 py-3 rounded-lg bg-slate-800 bg-opacity-50 border border-cyan-400 border-opacity-30 text-indigo-100 placeholder-slate-500 focus:border-opacity-100 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-opacity-50 transition-all" placeholder="Jean Dupont">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2" style="font-family: 'Orbitron', sans-serif; color: #22d3ee;">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-3 rounded-lg bg-slate-800 bg-opacity-50 border border-cyan-400 border-opacity-30 text-indigo-100 placeholder-slate-500 focus:border-opacity-100 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-opacity-50 transition-all" placeholder="vous@example.com">
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2" style="font-family: 'Orbitron', sans-serif; color: #22d3ee;">Mot de passe</label>
                <input type="password" name="password" required minlength="4" class="w-full px-4 py-3 rounded-lg bg-slate-800 bg-opacity-50 border border-cyan-400 border-opacity-30 text-indigo-100 placeholder-slate-500 focus:border-opacity-100 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-opacity-50 transition-all" placeholder="Minimum 4 caractères">
            </div>

            <button type="submit" class="w-full text-white font-bold py-3 rounded-lg mt-6 uppercase tracking-wider transition-all duration-300 hover:shadow-lg hover:shadow-cyan-500 hover:-translate-y-1 active:translate-y-0" style="font-family: 'Orbitron', sans-serif; background: linear-gradient(135deg, #0ea5e9 0%, #4f46e5 100%);">S'inscrire</button>
        </form>

        <p class="text-center text-sm text-slate-400 mt-6">
            Déjà inscrit ?
            <a href="index.php?page=login" class="font-bold hover:text-cyan-300 transition" style="font-family: 'Orbitron', sans-serif; color: #22d3ee;">Se connecter</a>
        </p>
    </div>
</body>
</html>
