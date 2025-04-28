<?php
session_start();

// config.php einbinden
require_once 'config.php';

// Zugriff prüfen
if (!isset($_SESSION['user'], $_SESSION['access_token'])) {
    header("Location: index.php");
    exit();
}

$access_token = $_SESSION['access_token'];

// Server-ID prüfen
if (!isset($_GET['id'])) {
    die("Keine Server-ID angegeben.");
}

$server_id = $_GET['id'];

// Verbindung zur Datenbank aufbauen
try {
    $db = new PDO(
        "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
} catch (PDOException $e) {
    die("Fehler bei der Datenbankverbindung: " . $e->getMessage());
}

// Wenn Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? '';
    $channel_id = $_POST['channel_id'] ?? '';
    $color = $_POST['color'] ?? '';

    // In die Datenbank speichern (INSERT oder UPDATE)
    $stmt = $db->prepare("
        INSERT INTO welcome_settings (server_id, message, channel_id, color) 
        VALUES (:server_id, :message, :channel_id, :color)
        ON CONFLICT (server_id) DO UPDATE 
        SET message = EXCLUDED.message, channel_id = EXCLUDED.channel_id, color = EXCLUDED.color
    ");

    $stmt->execute([
        ':server_id' => $server_id,
        ':message' => $message,
        ':channel_id' => $channel_id,
        ':color' => $color
    ]);

    // ✨ Nach dem Speichern: an BotGhost schicken
    $webhook_url = 'https://api.botghost.com/webhook/1284484623279067196/9lu3lsd6cga8azenej3kvu';
    $api_key = '3b27eb9ec4cabc04e1a9ddae62e0da7e96964ae8a5cd2e4d4f66b37e21fbf123'; // <<<<<<<<<<< DEIN API-KEY HIER!!!

    $payload = [
        "variables" => [
            [
                "name" => "message",
                "variable" => "{event_message}",
                "value" => $message
            ],
            [
                "name" => "channel_id",
                "variable" => "{event_channel}",
                "value" => $channel_id
            ],
            [
                "name" => "color",
                "variable" => "{event_color}",
                "value" => $color
            ]
        ]
    ];

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code != 200) {
        echo "Fehler beim Senden an BotGhost: " . htmlspecialchars($response);
    } else {
        echo "Einstellungen gespeichert und an BotGhost gesendet!";
    }
}

// Bisherige Daten laden, falls vorhanden
$stmt = $db->prepare("SELECT * FROM welcome_settings WHERE server_id = :server_id");
$stmt->execute([':server_id' => $server_id]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Werte vorbereiten
$message = $settings['message'] ?? '';
$channel_id = $settings['channel_id'] ?? '';
$color = $settings['color'] ?? '#FFFFFF'; // Standardfarbe Weiß
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Welcomer-System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Welcomer-System konfigurieren</h1>

<form method="post">
    <label>Willkommensnachricht:</label><br>
    <textarea name="message" rows="5" cols="50" required><?= htmlspecialchars($message) ?></textarea><br><br>

    <label>Channel-ID:</label><br>
    <input type="text" name="channel_id" value="<?= htmlspecialchars($channel_id) ?>" required><br><br>

    <label>Farbe (HEX):</label><br>
    <input type="color" name="color" value="<?= htmlspecialchars($color) ?>"><br><br>

    <button type="submit">Speichern</button>
</form>

<br>
<a href="dashboard.php">🔙 Zurück zum Dashboard</a>

</body>
</html>
