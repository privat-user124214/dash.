<?php
$host = 'ep-nameless-water-a2zd2tqy-pooler.eu-central-1.aws.neon.tech';
$db   = 'neondb';
$user = 'neondb_owner';
$pass = 'npg_I5GjqmM6iKxR'; // dein echtes Passwort
$sslmode = 'require';

$dsn = "pgsql:host=$host;dbname=$db;sslmode=$sslmode";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Fehler bei der DB-Verbindung: " . $e->getMessage());
}
