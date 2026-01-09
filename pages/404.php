<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Page introuvable | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #050505;
            color: #e5e7eb;
            font-family: 'Inter', sans-serif;
        }
        .sport-title {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 0.15em;
        }
        .glass-card {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.08);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

<div class="glass-card max-w-xl w-full p-10 rounded-2xl text-center">
    <h1 class="sport-title text-6xl text-emerald-400 mb-4">404</h1>
    <h2 class="text-2xl font-bold mb-3">Page introuvable</h2>
    <p class="text-gray-400 mb-8">
        Oups… Cette page n'existe pas ou a été déplacée.
    </p>

    <div class="flex flex-col gap-4">
        <a href="/BuyMatch/index.php"
           class="bg-emerald-500 hover:bg-emerald-600 text-black font-bold py-3 rounded-xl transition">
            Retour à l'accueil
        </a>

        <a href="/BuyMatch/auth/login.php"
           class="text-gray-400 hover:text-white transition">
            Se connecter
        </a>
    </div>
</div>

</body>
</html>
