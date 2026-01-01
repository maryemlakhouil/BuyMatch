<?php
session_start();

// Autoload des classes
spl_autoload_register(function ($class) {
    require_once __DIR__ . "/classes/" . $class . ".php";
});

// Redirection selon le role

if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    }

    if ($_SESSION['role'] === 'organisateur') {
        header("Location: organizer/create_match.php");
        exit;
    }

    if ($_SESSION['role'] === 'acheteur') {
        header("Location: pages/home.php");
        exit;
    }

}

// Visiteur (non connecté)
require_once "pages/home.php";
?>
