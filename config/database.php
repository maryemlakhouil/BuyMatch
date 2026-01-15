<?php

class Database {
    
    private static $host = "127.0.0.1";
    private static $db_name = "buymatch_db";
    private static $username = "phpmyadmin";
    private static $password = "lakhouil2003";
    private static $conn = null;

    public static function connect(){

        if (self::$conn === null) {
            try {
                self::$conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4",
                    self::$username,
                    self::$password
                );

                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                die("Erreur de connexion : " . $e->getMessage());
            }
        }

        return self::$conn;
    }
    // Empêcher l'instanciation
    private function __construct() {}
    
}

?>
