<?php
$host = 'ep-nameless-water-a2zd2tqy-pooler.eu-central-1.aws.neon.tech';
$db   = 'neondb';
$user = 'neondb_owner';
$pass = 'npg_I5GjqmM6iKxR';
$charset = 'utf8';

$dsn = "pgsql:host=$host;dbname=$db;sslmode=require";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    // Verbindung erfolgreich
} catch (PDOException $e) {
    die('Verbindung fehlgeschlagen: ' . $e->getMessage());
}
?>
