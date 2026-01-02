<?php

require_once "../config/database.php";
    session_start();

    /* Sécurité : accès organisateur uniquement */
    // if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organisateur') {
    //     header("Location: ../auth/login.php");
    //     exit;
    // }

    $message = "";

    /* Traitement formulaire */
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

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

        $equipe1 = htmlspecialchars($_POST['equipe1']);
        $equipe2 = htmlspecialchars($_POST['equipe2']);
        $dateHeure = $_POST['date_heure'];
        $lieu = htmlspecialchars($_POST['lieu']);
        $nbPlaces = (int) $_POST['nb_places_total'];

        $logoEquipe1 = uploadLogo($_FILES['logo_equipe1'], "team1");
        $logoEquipe2 = uploadLogo($_FILES['logo_equipe2'], "team2");

        if ($nbPlaces > 2000) {
            $message = "❌ Le nombre de places ne doit pas dépasser 2000";
        } else {
            $stmt = $db->prepare("
                INSERT INTO matches
                (organisateur_id, equipe1, equipe2, logo_equipe1, logo_equipe2,
                date_heure, lieu, nb_places_total)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $_SESSION['user_id'],
                $equipe1,
                $equipe2,
                $logoEquipe1,
                $logoEquipe2,
                $dateHeure,
                $lieu,
                $nbPlaces
            ]);
            $matchId = $db->lastInsertId();
            $message = "✅ Match envoyé pour validation par l’administrateur";
            /* Insertion catégories */
            $catNoms = $_POST['cat_nom'];
            $catPrix = $_POST['cat_prix'];
            $catPlaces = $_POST['cat_places'];

            $totalPlaces = 0;
            foreach ($catPlaces as $p) {
                $totalPlaces += (int)$p;
            }

            if ($totalPlaces > $nbPlaces) {
                $message = "❌ Le total des places par catégorie dépasse le total autorisé";
                return;
            }

            
            $stmtCat = $db->prepare("
                INSERT INTO categories (match_id, nom, prix, nb_places)
                VALUES (?, ?, ?, ?)
            ");
            
            for ($i = 0; $i < count($catNoms); $i++) {
                if (!empty($catNoms[$i]) && !empty($catPrix[$i]) && !empty($catPlaces[$i])) {
                    $stmtCat->execute([
                        $matchId,
                        htmlspecialchars($catNoms[$i]),
                        $catPrix[$i],
                        $catPlaces[$i]
                    ]);
                }
            }
        }


    }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un Match</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="max-w-4xl mx-auto py-10">

    <h1 class="text-3xl font-bold text-center text-indigo-700 mb-8">
        ⚽ Créer un événement sportif
    </h1>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-700 text-center">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data"
          class="bg-white p-8 rounded-2xl shadow space-y-6">

        <!-- Équipes -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <input type="text" name="equipe1" placeholder="Nom équipe 1"
                   required class="input-style">
            <input type="text" name="equipe2" placeholder="Nom équipe 2"
                   required class="input-style">
        </div>

        <!-- Logos -->
        <h2 class="text-lg font-semibold text-gray-700">🖼️ Logos des équipes</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="border-2 border-dashed rounded-xl p-4 text-center">
                <p class="font-semibold mb-2">Logo Équipe 1</p>
                <img id="preview1" src="https://via.placeholder.com/120"
                     class="mx-auto h-28 w-28 object-contain mb-3 rounded">
                <label class="cursor-pointer bg-indigo-600 text-white px-4 py-2 rounded-lg">
                    Choisir un logo
                    <input type="file" name="logo_equipe1" accept="image/*"
                           class="hidden"
                           onchange="previewImage(event,'preview1')">
                </label>
            </div>

            <div class="border-2 border-dashed rounded-xl p-4 text-center">
                <p class="font-semibold mb-2">Logo Équipe 2</p>
                <img id="preview2" src="https://via.placeholder.com/120"
                     class="mx-auto h-28 w-28 object-contain mb-3 rounded">
                <label class="cursor-pointer bg-indigo-600 text-white px-4 py-2 rounded-lg">
                    Choisir un logo
                    <input type="file" name="logo_equipe2" accept="image/*"
                           class="hidden"
                           onchange="previewImage(event,'preview2')">
                </label>
            </div>

        </div>

        <!-- Infos match -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <input type="datetime-local" name="date_heure" required class="input-style">
            <input type="text" name="lieu" placeholder="Lieu du match" required class="input-style">
        </div>

        <input type="number" name="nb_places_total" max="2000"
               placeholder="Nombre total de places (max 2000)"
               required class="input-style">
<h2 class="text-lg font-semibold text-gray-700 mt-8">🎟️ Catégories (max 3)</h2>

<div class="space-y-4">

    <!-- Catégorie 1 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input type="text" name="cat_nom[]" placeholder="Nom (VIP)"
               class="input-style" required>
        <input type="number" name="cat_prix[]" placeholder="Prix"
               class="input-style" required>
        <input type="number" name="cat_places[]" placeholder="Nb places"
               class="input-style" required>
    </div>

    <!-- Catégorie 2 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input type="text" name="cat_nom[]" placeholder="Nom (Standard)"
               class="input-style">
        <input type="number" name="cat_prix[]" placeholder="Prix"
               class="input-style">
        <input type="number" name="cat_places[]" placeholder="Nb places"
               class="input-style">
    </div>

    <!-- Catégorie 3 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input type="text" name="cat_nom[]" placeholder="Nom (Economy)"
               class="input-style">
        <input type="number" name="cat_prix[]" placeholder="Prix"
               class="input-style">
        <input type="number" name="cat_places[]" placeholder="Nb places"
               class="input-style">
    </div>

</div>

        <button type="submit"
                class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition">
            ➕ Créer le match
        </button>
        
    </form>
</div>

<script>
function previewImage(event, previewId) {
    const reader = new FileReader();
    reader.onload = () => document.getElementById(previewId).src = reader.result;
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<style>
.input-style {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid #d1d5db;
    outline: none;
}
.input-style:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 2px rgba(99,102,241,0.3);
}
</style>

</body>
</html>
