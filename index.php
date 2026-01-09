<?php
session_start();
define('BASE_PATH', __DIR__);


if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashbord.php");
        exit;
    }

    if ($_SESSION['role'] === 'organisateur') {
        header("Location: organizer/dashbord.php");
        exit;
    }
}

/* ROUTING VISITEUR / ACHETEUR */
$page = $_GET['page'] ?? 'home';

$allowedPages = ['home','match_details','login','register','404'];

if (!in_array($page, $allowedPages)) {
    http_response_code(404);
    $page = '404';
}

/* AFFICHAGE */
require BASE_PATH . "/pages/$page.php";
?>