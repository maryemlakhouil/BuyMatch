<?php
    session_start();

    require_once "../config/database.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit;
    }

    $db = Database::connect();

    /* Charger infos utilisateur */
    $stmt = $db->prepare("SELECT nom, email, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Utilisateur introuvable");
    }

    $message = "";

    /* Mise à jour profil */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        /* Modifier infos */
        if (isset($_POST['update_info'])) {
            $nom = trim($_POST['nom']);
            $email = trim($_POST['email']);

            $stmt = $db->prepare("UPDATE users SET nom = ?, email = ? WHERE id = ?");
            $stmt->execute([$nom, $email, $_SESSION['user_id']]);

            $_SESSION['nom'] = $nom;
            $message = " Profil mis à jour avec succès";
        }

        /* Modifier mot de passe */
        if (isset($_POST['update_password'])) {
            $password = $_POST['password'];
            $confirm = $_POST['confirm_password'];

            if ($password !== $confirm) {
                $message = "Les mots de passe ne correspondent pas";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $_SESSION['user_id']]);
                $message = " Mot de passe modifié avec succès";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon profil | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #050505; color: #e5e7eb; }
        .font-sport { font-family: 'Orbitron', sans-serif; }
        .glass-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .cyber-input { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: white; transition: all 0.3s ease; }
        .cyber-input:focus { border-color: #6366f1; background: rgba(255, 255, 255, 0.08); outline: none; box-shadow: 0 0 15px rgba(99, 102, 241, 0.2); }
    </style>
</head>

<body class="min-h-screen pb-12">

<div class="max-w-4xl mx-auto px-4 pt-8">
    <a href="dashbord.php" class="inline-flex items-center text-indigo-400 hover:text-indigo-300 transition-colors mb-8 group">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Retour au Dashboard
    </a>

    <h1 class="text-4xl font-sport text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 to-purple-500 text-center mb-12 tracking-wider uppercase">
        👤 Mon Profil
    </h1>

    <?php if ($message): ?>
        <div class="mb-8 p-4 rounded-xl glass-card border-l-4 border-indigo-500 text-indigo-200 text-center animate-pulse">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <div class="glass-card p-8 rounded-2xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            
            <h2 class="text-xl font-sport text-white mb-8 border-b border-white/10 pb-4">Identité</h2>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="update_info">

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Nom complet</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>"
                           class="w-full cyber-input p-4 rounded-xl font-medium" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Adresse Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                           class="w-full cyber-input p-4 rounded-xl font-medium" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Statut Compte</label>
                    <div class="w-full p-4 rounded-xl bg-white/5 border border-white/5 text-indigo-300 font-bold uppercase tracking-widest text-sm">
                        <?= ucfirst($user['role']) ?>
                    </div>
                </div>

                <button class="w-full bg-gradient-to-r from-indigo-600 to-indigo-800 text-white font-bold py-4 rounded-xl hover:from-indigo-500 hover:to-indigo-700 transition-all transform hover:scale-[1.02] active:scale-95 shadow-lg shadow-indigo-500/20 uppercase tracking-wider">
                    Enregistrer les modifications
                </button>
            </form>
        </div>


        <div class="glass-card p-8 rounded-2xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>

            <h2 class="text-xl font-sport text-white mb-8 border-b border-white/10 pb-4">Sécurité</h2>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="update_password">

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Nouveau mot de passe</label>
                    <input type="password" name="password"
                           class="w-full cyber-input p-4 rounded-xl" placeholder="••••••••" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Confirmation</label>
                    <input type="password" name="confirm_password"
                           class="w-full cyber-input p-4 rounded-xl" placeholder="••••••••" required>
                </div>

                <div class="pt-10">
                    <button class="w-full bg-transparent border-2 border-indigo-500 text-indigo-400 font-bold py-4 rounded-xl hover:bg-indigo-500/10 transition-all transform hover:scale-[1.02] active:scale-95 uppercase tracking-wider">
                        Modifier le mot de passe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
