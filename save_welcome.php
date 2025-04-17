<?php
session_start();
require 'db.php'; // Verbindung zur DB

if (!isset($_SESSION['user'], $_SESSION['access_token'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $server_id = $_POST['server_id'];
    $channel_id = $_POST['welcome_channel_id'];
    $welcome_message = $_POST['welcome_message'];

    // In der DB speichern
    $query = "INSERT INTO welcome_settings (server_id, welcome_channel_id, welcome_message)
              VALUES (:server_id, :channel_id, :welcome_message)
              ON CONFLICT (server_id) DO UPDATE 
              SET welcome_channel_id = EXCLUDED.welcome_channel_id,
                  welcome_message = EXCLUDED.welcome_message,
                  updated_at = CURRENT_TIMESTAMP";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':server_id' => $server_id,
        ':channel_id' => $channel_id,
        ':welcome_message' => $welcome_message
    ]);

    // Danach an BotGhost senden
    $botghost_webhook_url = 'https://api.botghost.com/webhook/1284484623279067196/4366m2s0uetzriyyuukyvh';
    $api_key = 'DEIN_API_KEY'; // <-- HIER DEIN API KEY EINTRAGEN

    $payload = json_encode([
        "variables" => [
            [
                "name" => "message",
                "variable" => "{event_message}",
                "value" => $welcome_message
            ]
        ]
    ]);

    $ch = curl_init($botghost_webhook_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: ' . $api_key
    ]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // Erfolgsmeldung oder Weiterleitung
    header("Location: server.php?id=" . urlencode($server_id));
    exit();
}
?>
