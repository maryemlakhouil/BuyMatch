<?php
session_start();

require_once "../classes/Organisateur.php";
require_once "../config/database.php";

// Vérification sécurité : accès organisateur uniquement
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisateur') {
//     header("Location: ../auth/login.php");
//     exit;
// }

// Connexion DB
$db = Database::connect();

// Récupérer infos organisateur depuis la BDD
$stmt = $db->prepare("SELECT nom, email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Instanciation de l'organisateur
$organisateur = new Organisateur(
    $_SESSION['user_id'],
    $user['nom'],
    $user['email'],
    '',
    true
);

$message = "";

// Fonction upload logo
function uploadLogo($file, $prefix) {
    $uploadsDir = "../uploads/";
    if ($file['error'] === 0) {
        $allowed = ['image/png', 'image/jpeg', 'image/jpg'];
        if (in_array($file['type'], $allowed)) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid($prefix . "_") . "." . $ext;
            move_uploaded_file($file['tmp_name'], $uploadsDir . $filename);
            return "uploads/" . $filename;
        }
    }
    return null;
}

// Traitement formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupérer les données du formulaire
    $equipe1   = trim($_POST['equipe1'] ?? '');
    $equipe2   = trim($_POST['equipe2'] ?? '');
    $dateHeure = $_POST['date_heure'] ?? '';
    $lieu      = trim($_POST['lieu'] ?? '');
    $nbPlaces  = (int) ($_POST['nb_places_total'] ?? 0);
    $duree     = 90;

    // Logos
    $logoEquipe1 = $_FILES['logo_equipe1']['error'] === 0
        ? uploadLogo($_FILES['logo_equipe1'], 'team1')
        : null;

    $logoEquipe2 = $_FILES['logo_equipe2']['error'] === 0
        ? uploadLogo($_FILES['logo_equipe2'], 'team2')
        : null;

    // Catégories
    $categories = [];
    if (!empty($_POST['cat_nom'])) {
        for ($i = 0; $i < count($_POST['cat_nom']); $i++) {
            if (!empty($_POST['cat_nom'][$i])) {
                $categories[] = [
                    'nom'    => $_POST['cat_nom'][$i],
                    'prix'   => (float) $_POST['cat_prix'][$i],
                    'places' => (int) $_POST['cat_places'][$i]
                ];
            }
        }
    }

    // Appel méthode POO
   $matchId = $organisateur->creerMatch(
    $equipe1,
    $equipe2,
    $logoEquipe1,
    $logoEquipe2,
    $dateHeure,
    $lieu,
    $duree,      // <-- ici on passe bien 90 minutes
    $nbPlaces,   // <-- nb places total
    $categories  // <-- tableau des catégories
);


    if ($matchId) {
        $message = "✅ Match créé avec succès et envoyé pour validation par l’administrateur";
    } else {
        $message = "❌ Erreur lors de la création du match (vérifier les données)";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BuyMatch | Créer un Match</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        secondary: '#4f46e5',
                        accent: '#818cf8',
                        dark: '#050505',
                        'dark-card': '#0f1115',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        orbitron: ['Orbitron', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark text-gray-200 min-h-screen font-sans">

<div class="max-w-4xl mx-auto py-12 px-4">

    <!-- Header -->
    <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-orbitron font-black text-white uppercase tracking-tighter italic">
            <span class="text-primary">Créer</span> un match
        </h1>
        <p class="text-gray-400 mt-2 font-medium">Configurez votre événement sportif haute performance</p>
    </div>

    <?php if ($message): ?>
    <div class="mb-8 p-4 rounded-xl bg-primary/10 border border-primary/20 text-primary text-center backdrop-blur-sm animate-pulse">
        <?= $message ?>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-8">
        
        <!-- Équipes Section -->
        <div class="bg-dark-card border border-white/5 rounded-2xl p-6 md:p-8 shadow-2xl relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
            <h2 class="text-xl font-orbitron font-bold text-white mb-6 flex items-center gap-3">
                <span class="w-8 h-8 rounded bg-primary/20 flex items-center justify-center text-primary italic">01</span>
                CONSTITUTION DES ÉQUIPES
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Équipe 1 -->
                <div class="space-y-4">
                    <input type="text" name="equipe1" placeholder="Nom équipe domicile" required 
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-600">
                    
                    <div class="relative group/upload h-48 rounded-xl border-2 border-dashed border-white/10 hover:border-primary/50 transition-colors flex flex-col items-center justify-center bg-white/[0.02]">
                        <img id="preview1" src="/placeholder.svg?height=100&width=100"
                             class="h-24 w-24 object-contain mb-3 opacity-80 group-hover/upload:opacity-100 transition-opacity">
                        <label class="cursor-pointer bg-white/5 hover:bg-primary text-white text-xs font-bold uppercase tracking-wider px-4 py-2 rounded-lg transition-colors border border-white/10">
                            Logo Domicile
                            <input type="file" name="logo_equipe1" accept="image/*" class="hidden" onchange="previewImage(event,'preview1')">
                        </label>
                    </div>
                </div>

                <!-- Équipe 2 -->
                <div class="space-y-4">
                    <input type="text" name="equipe2" placeholder="Nom équipe extérieur" required 
                           class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-gray-600">
                    
                    <div class="relative group/upload h-48 rounded-xl border-2 border-dashed border-white/10 hover:border-primary/50 transition-colors flex flex-col items-center justify-center bg-white/[0.02]">
                        <img id="preview2" src="/placeholder.svg?height=100&width=100"
                             class="h-24 w-24 object-contain mb-3 opacity-80 group-hover/upload:opacity-100 transition-opacity">
                        <label class="cursor-pointer bg-white/5 hover:bg-primary text-white text-xs font-bold uppercase tracking-wider px-4 py-2 rounded-lg transition-colors border border-white/10">
                            Logo Extérieur
                            <input type="file" name="logo_equipe2" accept="image/*" class="hidden" onchange="previewImage(event,'preview2')">
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détails Logistiques -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-dark-card border border-white/5 rounded-2xl p-6 shadow-xl col-span-1 md:col-span-2">
                <h2 class="text-lg font-orbitron font-bold text-white mb-6">INFOS LOGISTIQUES</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-primary uppercase tracking-widest ml-1">Date & Heure</label>
                        <input type="datetime-local" name="date_heure" required 
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:border-primary outline-none transition-all">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-primary uppercase tracking-widest ml-1">Lieu de l'événement</label>
                        <input type="text" name="lieu" placeholder="Stade ou Arena" required 
                               class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:border-primary outline-none transition-all placeholder:text-gray-600">
                    </div>
                </div>
            </div>
            
            <div class="bg-primary/5 border border-primary/20 rounded-2xl p-6 shadow-xl flex flex-col justify-center">
                <label class="text-[10px] font-bold text-primary uppercase tracking-widest mb-2">Capacité Max</label>
                <input type="number" name="nb_places_total" max="2000" placeholder="Ex: 500" required 
                       class="text-2xl font-orbitron bg-transparent border-b-2 border-primary/30 focus:border-primary outline-none py-2 text-white">
                <p class="text-[10px] text-gray-500 mt-2 italic">Limite fixée à 2000 places</p>
            </div>
        </div>

        <!-- Billetterie -->
        <div class="bg-dark-card border border-white/5 rounded-2xl p-6 md:p-8 shadow-2xl relative overflow-hidden">
            <h2 class="text-xl font-orbitron font-bold text-white mb-8 flex items-center gap-3">
                <span class="w-8 h-8 rounded bg-primary/20 flex items-center justify-center text-primary italic">02</span>
                CONFIGURATION BILLETTERIE
            </h2>
            
            <div class="grid grid-cols-1 gap-4">
                <?php for ($i=0; $i<3; $i++): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 rounded-xl bg-white/[0.02] border border-white/5 group hover:bg-white/[0.04] transition-colors">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Catégorie <?= $i+1 ?></label>
                        <input type="text" name="cat_nom[]" placeholder="Standard, VIP..." class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 outline-none focus:border-primary" <?= $i===0?'required':'' ?>>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Prix (€)</label>
                        <input type="number" name="cat_prix[]" placeholder="0.00" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 outline-none focus:border-primary" <?= $i===0?'required':'' ?>>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-gray-500 uppercase ml-1">Places dispo</label>
                        <input type="number" name="cat_places[]" placeholder="Nombre" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 outline-none focus:border-primary" <?= $i===0?'required':'' ?>>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit"
                class="group relative w-full overflow-hidden rounded-2xl bg-primary px-8 py-5 font-orbitron font-black text-white transition-all hover:scale-[1.01] active:scale-95 shadow-lg shadow-primary/20">
            <div class="absolute inset-0 bg-gradient-to-r from-primary via-accent to-primary bg-[length:200%_100%] animate-[shimmer_2s_infinite] opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <span class="relative flex items-center justify-center gap-3 text-lg uppercase tracking-wider">
                ➕ PUBLIER L'ÉVÉNEMENT
            </span>
        </button>
    </form>
</div>

<script>
function previewImage(event, previewId) {
    const reader = new FileReader();
    const preview = document.getElementById(previewId);
    reader.onload = () => {
        preview.src = reader.result;
        preview.classList.remove('opacity-80');
        preview.parentElement.classList.add('border-primary/50');
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<style>
    @keyframes shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    input[type="datetime-local"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
    }
</style>
</body>
</html>
