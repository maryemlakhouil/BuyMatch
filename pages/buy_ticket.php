<?php

    require_once  BASE_PATH . "/config/database.php";
    require_once BASE_PATH .  "/classes/User.php";
    require_once BASE_PATH. "/classes/Acheteur.php";

    /* Sécurité */
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?page=login");
        exit;
    }

    $db = Database::connect();

    /* Infos utilisateur */
    $stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        die("Utilisateur introuvable");
    }

    /* Objet Acheteur */

    $acheteur = new Acheteur($_SESSION['user_id'], $user['nom'], $user['email'], '', 'acheteur', true);

    /* Vérifier match */
    $matchId = $_GET['match_id'] ?? null;

    if (!$matchId || !is_numeric($matchId)) {
        die("Match invalide");
    }

    $match = $acheteur->getMatchById((int)$matchId);
    if (!$match) {
        die("Match introuvable ou non disponible");
    }

    /* Catégories */
    $categories = $acheteur->getCategoriesMatch($matchId);

    /* Nombre de billets déjà achetés */
    $nbBillets = $acheteur->nombreBilletsAchetes($matchId);

    /* Messages */
    $error = "";
    $success = "";

    /* Achat billet */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $categorieId  = (int) $_POST['categorie_id'];
            $numeroPlace  = (int) $_POST['numero_place'];

            $ticket = $acheteur->acheterBillet($matchId, $categorieId, $numeroPlace);
            // ENVOI EMAIL
            $acheteur->envoyerBilletParEmail($ticket, $match);

            $success = "Billet acheté avec succès ! Un email vous a été envoyé ";

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Achat Billet | BuyMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-orbitron { font-family: 'Orbitron', sans-serif; }
        
        body { 
            background: linear-gradient(135deg, #050505 0%, #0a0e27 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
        }

        .glass-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(100, 200, 255, 0.3);
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00d4ff 0%, #0099ff 100%);
            color: white;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 212, 255, 0.3);
        }

        .input-dark {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(100, 200, 255, 0.2);
            color: white;
            transition: all 0.3s ease;
        }

        .input-dark:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(100, 200, 255, 0.5);
            outline: none;
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.2);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        .badge-warning {
            background: rgba(250, 204, 21, 0.15);
            border: 1px solid rgba(250, 204, 21, 0.4);
            color: #fef08a;
        }

        .match-header {
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.1), rgba(0, 153, 255, 0.1));
            border-left: 4px solid #00d4ff;
        }
    </style>
</head>
<body class="text-gray-100 py-8 px-4">

    <!-- Navbar professionnelle -->
    <nav class="fixed top-0 left-0 right-0 glass-card m-4 z-50">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="index.php" class="font-orbitron text-xl font-black bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                BUY<span class="text-yellow-400">MATCH</span>
            </a>
            <a href="index.php?page=match_details&id=<?= $matchId ?>" class="text-cyan-400 hover:text-cyan-300 transition flex items-center gap-2">
                <span>←</span> Retour aux détails
            </a>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto mt-24">
        
        <!-- Carte d'informations du match -->
        <div class="glass-card p-8 mb-8 match-header">
            <div class="flex items-center justify-between mb-4">
                <h1 class="font-orbitron text-3xl font-black bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                    <?= htmlspecialchars($match['equipe1']) ?> vs <?= htmlspecialchars($match['equipe2']) ?>
                </h1>
                <span class="bg-yellow-400/20 text-yellow-300 px-4 py-2 rounded-full font-bold text-sm">
                    MATCH EN VENTE
                </span>
            </div>
            
            <p class="text-cyan-300 text-lg">
                <?= date('d/m/Y', strtotime($match['date_heure'])) ?> à <?= date('H:i', strtotime($match['date_heure'])) ?>
            </p>
            <p class="text-gray-400 mt-2">
                <?= htmlspecialchars($match['lieu'] ?? 'Lieu à confirmer') ?>
            </p>
        </div>

        <!-- Messages d'alerte -->
        <?php if ($error): ?>
            <div class="alert-error p-4 rounded-lg mb-6 border">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success p-4 rounded-lg mb-6 border">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire d'achat ou message limite -->
        <?php if ($nbBillets >= 4): ?>
            <div class="glass-card p-8 text-center badge-warning border">
                <h2 class="font-orbitron text-xl font-bold mb-2">Limite atteinte</h2>
                <p class="text-gray-300">Vous avez atteint le maximum de 4 billets pour ce match.</p>
            </div>
        <?php else: ?>
        
        <div class="glass-card p-8">
            <h2 class="font-orbitron text-2xl font-bold mb-6 text-cyan-400">
                Sélectionner votre billet
            </h2>

            <form method="POST" class="space-y-6">

                <!-- Catégories avec design amélioré -->
                <div>
                    <label class="block mb-3 font-orbitron font-bold text-cyan-300">
                        Catégorie de place
                    </label>
                    <select name="categorie_id" required class="input-dark w-full p-4 rounded-lg focus:ring-2 focus:ring-cyan-400">
                        <option value="" class="bg-gray-900">-- Choisir une catégorie --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" class="bg-gray-900">
                                <?= htmlspecialchars($cat['nom']) ?> — <span class="text-yellow-400"><?= number_format($cat['prix'],2) ?> DH</span>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Numéro de place avec design amélioré -->
                <div>
                    <label class="block mb-3 font-orbitron font-bold text-cyan-300">
                        Numéro de place
                    </label>
                    <input type="number" name="numero_place" min="1" required
                        class="input-dark w-full p-4 rounded-lg focus:ring-2 focus:ring-cyan-400"
                        placeholder="Ex: 42">
                </div>

                <!-- Bouton d'achat avec design dégradé -->
                <button type="submit" class="btn-primary w-full py-4 rounded-lg font-orbitron font-bold text-lg tracking-wide">
                    ACHETER LE BILLET MAINTENANT
                </button>

            </form>
        </div>

        <?php endif; ?>

        <!-- Section de téléchargement du billet -->
        <?php if ($success): ?>
        <div class="glass-card p-8 mt-8 border border-green-500/30">
            <h3 class="font-orbitron text-lg font-bold text-green-400 mb-4"> Billet généré</h3>
            <a href="index.php?page=ticket_print&ticket_id=<?= $ticket['id'] ?>" target="_blank"
            class="btn-primary w-full py-4 rounded-lg font-bold text-center block hover:shadow-lg transition">
                Télécharger / Imprimer mon billet
            </a>
        </div>
        <?php endif; ?>

    </div>

</body>
</html>
