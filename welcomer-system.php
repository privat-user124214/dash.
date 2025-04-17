<?php
session_start();
if (!isset($_SESSION['user'], $_SESSION['access_token'])) {
    header("Location: index.php");
    exit();
}

$server_id = $_GET['id'] ?? null;
if (!$server_id) {
    die("Keine Server-ID angegeben.");
}

// DB verbinden (Daten hier anpassen)
$db = new PDO("pgsql:host=HOSTNAME;port=5432;dbname=DBNAME;user=USER;password=PASSWORD;sslmode=require");

// Aktuelle Einstellungen laden
$stmt = $db->prepare("SELECT * FROM welcome_settings WHERE server_id = :server_id");
$stmt->execute([':server_id' => $server_id]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Formular abgeschickt?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'];
    $channel_id = $_POST['channel_id'];
    $color = $_POST['color'];

    // Speichern oder aktualisieren
    $stmt = $db->prepare("
        INSERT INTO welcome_settings (server_id, message, channel_id, color)
        VALUES (:server_id, :message, :channel_id, :color)
        ON CONFLICT (server_id) DO UPDATE
        SET message = EXCLUDED.message,
            channel_id = EXCLUDED.channel_id,
            color = EXCLUDED.color
    ");
    $stmt->execute([
        ':server_id' => $server_id,
        ':message' => $message,
        ':channel_id' => $channel_id,
        ':color' => $color
    ]);

    $settings = ['message' => $message, 'channel_id' => $channel_id, 'color' => $color];
    $success = true;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Welcomer-System – Einstellungen</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Willkommen-Einstellungen für Server ID: <?= htmlspecialchars($server_id) ?></h1>
</header>

<main>
    <?php if (!empty($success)): ?>
        <p style="color: green;">Änderungen wurden gespeichert.</p>
    <?php endif; ?>

    <form method="POST">
        <label for="channel_id">📢 Channel ID:</label><br>
        <input type="text" name="channel_id" value="<?= htmlspecialchars($settings['channel_id'] ?? '') ?>" required><br><br>

        <label for="message">💬 Nachricht:</label><br>
        <textarea name="message" rows="4" cols="50" required><?= htmlspecialchars($settings['message'] ?? '') ?></textarea><br><br>

        <label for="color">🎨 Embed-Farbe:</label><br>
        <input type="color" name="color" value="<?= htmlspecialchars($settings['color'] ?? '#5865F2') ?>"><br><br>

        <button type="submit">Speichern</button>
    </form>

    <h3>🧩 Platzhalter-Hilfe:</h3>
    <ul>
        <li><code>{user}</code> – Der neue User (z. B. @Max)</li>
        <li><code>{server}</code> – Der Servername</li>
        <li><code>{event_message}</code> – Optionale BotGhost Info</li>
    </ul>
</main>
</body>
</html>
