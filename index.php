<?php


   ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


session_start();
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/vendor/autoload.php';


/* REDIRECTION AVANT TOUT OUTPUT */
if (isset($_SESSION['role']) && !isset($_GET['page'])) {

    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: index.php?page=admin_dashbord");
            exit;

        case 'organisateur':
            header("Location: index.php?page=organisateur_dashbord");
            exit;

        default:
            header("Location: index.php?page=home");
            exit;
    }
}   



/* ROUTING VISITEUR / ACHETEUR */
$page = $_GET['page'] ?? 'home';

$allowedPages = [
    'home',
    'admin_comments',
    'admin_dashbord',
    'admin_users',
    'admin_validate_match',
    'commnter_match',
    'create_match',
    'match_details',
    'buy_ticket',
    'login',
    'matchs',
    'logout',
    'mes_billets',
    'register',
    '404',
    'profile',
    'stats',
    'organisateur_dashbord',
    'ticket_print',
];

if (!in_array($page, $allowedPages)) {
    http_response_code(404);
    require BASE_PATH . '/pages/404.php';
    exit;
}

require BASE_PATH . "/pages/$page.php";

?>