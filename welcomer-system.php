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

// Bestehende Einstellungen laden
$query = $db->prepare("SELECT message, channel_id, color FROM welcome_settings WHERE server_id = :server_id");
$query->execute(['server_id' => $server_id]);
$settings = $query->fetch(PDO::FETCH_ASSOC);

// Falls nichts gefunden -> Standardwerte
if (!$settings) {
    $settings = [
        'message' => '',
        'channel_id' => '',
        'color' => '#5865F2' // Discord Blau
    ];
}

// Formular abgeschickt?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    $channel_id = trim($_POST['channel_id']);
    $color = trim($_POST['color']);

    // Entweder UPDATE oder INSERT
    $query = $db->prepare("
        INSERT INTO welcome_settings (server_id, message, channel_id, color)
        VALUES (:server_id, :message, :channel_id, :color)
        ON CONFLICT (server_id) DO UPDATE
        SET message = EXCLUDED.message,
            channel_id = EXCLUDED.channel_id,
            color = EXCLUDED.color
    ");

    $query->execute([
        'server_id' => $server_id,
        'message' => $message,
        'channel_id' => $channel_id,
        'color' => $color
    ]);

    // Nach Speichern neu laden
    header("Location: welcomer-system.php?id=" . urlencode($server_id));
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Welcomer System – Server bearbeiten</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; }
        input[type="text"], input[type="color"], textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #5865F2;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #4752C4;
        }
    </style>
</head>
<body>

<header>
    <h1>Welcomer-Einstellungen für Server ID: <?= htmlspecialchars($server_id) ?></h1>
    <nav>
        <ul>
            <li><a href="dashboard.php">Zurück zur Übersicht</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
<div class="container">
    <h2>Willkommens-Nachricht bearbeiten</h2>

    <form method="POST">
        <div class="form-group">
            <label for="message">Nachricht</label>
            <textarea id="message" name="message" rows="4" required><?= htmlspecialchars($settings['message']) ?></textarea>
            <small>Platzhalter: <code>{user}</code> wird automatisch ersetzt.</small>
        </div>

        <div class="form-group">
            <label for="channel_id">Channel ID</label>
            <input type="text" id="channel_id" name="channel_id" value="<?= htmlspecialchars($settings['channel_id']) ?>" required>
        </div>

        <div class="form-group">
            <label for="color">Farbe (HEX)</label>
            <input type="color" id="color" name="color" value="<?= htmlspecialchars($settings['color']) ?>">
        </div>

        <button type="submit">Speichern</button>
    </form>
</div>
</main>

<footer>
    <p>&copy; Novarix Studio</p>
</footer>

</body>
</html>
