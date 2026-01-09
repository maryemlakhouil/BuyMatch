<?php
session_start();
define('BASE_PATH', __DIR__);

/* 🔁 REDIRECTION AVANT TOUT OUTPUT */
if (isset($_SESSION['role'])) {

    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    }

    if ($_SESSION['role'] === 'organisateur') {
        header("Location: organizer/dashboard.php");
        exit;
    }
}

/* ROUTING VISITEUR / ACHETEUR */
$page = $_GET['page'] ?? 'home';

$allowedPages = [
    'home',
    'match_details',
    'buy_ticket',
    'login',
    'register',
    '404'
];

if (!in_array($page, $allowedPages)) {
    http_response_code(404);
    $page = '404';
}

/* AFFICHAGE */
require BASE_PATH . "/pages/$page.php";
