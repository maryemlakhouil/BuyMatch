<?php
session_start();

// Autoload des classes
spl_autoload_register(function ($class) {
    require_once __DIR__ . "/classes/" . $class . ".php";
});

// Redirection selon le role
if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashbord.php");
        exit;
    }

    if ($_SESSION['role'] === 'organisateur') {
        header("Location: organizer/dashbord.php");
        exit;
    }

    if ($_SESSION['role'] === 'acheteur') {
        header("Location: pages/home.php");
        exit;
    }

}

$page = $_GET['page'] ?? 'home';

$file = __DIR__ . "/pages/$page.php";

if (file_exists($file)) {
    require $file;
} else {
    require __DIR__ . '/pages/404.php';
}        
?>
