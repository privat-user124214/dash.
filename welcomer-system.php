<?php
session_start();

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

// Verbindung zur Datenbank
$db = new PDO('pgsql:host=DEIN_HOST;port=5432;dbname=DEIN_DBNAME;user=DEIN_USER;password=DEIN_PASS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Willkommens-Einstellungen abrufen
$stmt = $db->prepare("SELECT server_id, message, channel_id, color FROM welcome_settings WHERE server_id = :server_id");
$stmt->execute(['server_id' => $server_id]);
$welcome_settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Wenn keine Einstellungen vorhanden sind -> Standardwerte und neuen Eintrag anlegen
if (!$welcome_settings) {
    $default_settings = [
        'server_id' => $server_id,
        'message' => '',
        'channel_id' => '',
        'color' => '#000000'
    ];
    $insert_stmt = $db->prepare("INSERT INTO welcome_settings (server_id, message, channel_id, color) VALUES (:server_id, :message, :channel_id, :color)");
    $insert_stmt->execute($default_settings);
    $welcome_settings = $default_settings;
}

// Änderungen speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? '';
    $channel_id = $_POST['channel_id'] ?? '';
    $color = $_POST['color'] ?? '#000000';

    $update_stmt = $db->prepare("UPDATE welcome_settings SET message = :message, channel_id = :channel_id, color = :color WHERE server_id = :server_id");
    $update_stmt->execute([
        'server_id' => $server_id,
        'message' => $message,
        'channel_id' => $channel_id,
        'color' => $color
    ]);

    // Optional: kleine Bestätigung oder Weiterleitung
    header("Location: welcomer-system.php?id=" . urlencode($server_id));
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Welcomer-System bearbeiten</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>Welcomer-System konfigurieren</h1>
    <nav>
        <ul>
            <li><a href="dashboard.php">Zurück zur Server Auswahl</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main class="container">
    <h2>Willkommensnachricht einstellen</h2>
    <form method="POST">
        <label for="message">Willkommensnachricht:</label><br>
        <textarea name="message" id="message" rows="5" cols="60" placeholder="Willkommensnachricht hier eingeben..." required><?= htmlspecialchars($welcome_settings['message']) ?></textarea><br><br>

        <label for="channel_id">Channel-ID (wo die Nachricht gesendet werden soll):</label><br>
        <input type="text" name="channel_id" id="channel_id" value="<?= htmlspecialchars($welcome_settings['channel_id']) ?>" placeholder="z.B. 123456789012345678" required><br><br>

        <label for="color">Farbe der Nachricht (HEX):</label><br>
        <input type="color" name="color" id="color" value="<?= htmlspecialchars($welcome_settings['color']) ?>"><br><br>

        <button type="submit">Speichern</button>
    </form>
</main>

<footer>
    <p>&copy; Novarix Studio</p>
</footer>

</body>
</html>
