<?php
session_start();

// Zugriff prüfen
if (!isset($_SESSION['user'], $_SESSION['access_token'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Keine Server-ID angegeben.");
}

$server_id = $_GET['id'];

require 'db.php'; // <--- Deine funktionierende Datenbankverbindung

// --- Daten aus der Datenbank laden
$stmt = $pdo->prepare("SELECT * FROM welcome_settings WHERE server_id = :server_id");
$stmt->execute(['server_id' => $server_id]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// --- Falls noch keine Daten existieren: leere Defaults setzen
if (!$settings) {
    $settings = [
        'message' => '',
        'channel_id' => '',
        'color' => '#5865F2' // Discord Standardfarbe
    ];
}

// --- Wenn Formular abgesendet wird
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'];
    $channel_id = $_POST['channel_id'];
    $color = $_POST['color'];

    // Update oder Insert
    $stmt = $pdo->prepare("INSERT INTO welcome_settings (server_id, message, channel_id, color) 
                            VALUES (:server_id, :message, :channel_id, :color)
                            ON CONFLICT (server_id) DO UPDATE SET 
                                message = EXCLUDED.message,
                                channel_id = EXCLUDED.channel_id,
                                color = EXCLUDED.color");

    $stmt->execute([
        'server_id' => $server_id,
        'message' => $message,
        'channel_id' => $channel_id,
        'color' => $color
    ]);

    // Erfolgreich gespeichert, neu laden
    header("Location: welcomer-system.php?id=" . urlencode($server_id) . "&success=1");
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
    <h1>Willkommenseinstellungen</h1>
    <nav>
        <ul>
            <li><a href="server.php?id=<?= htmlspecialchars($server_id) ?>">🔙 Zurück</a></li>
            <li><a href="dashboard.php">🏠 Dashboard</a></li>
            <li><a href="logout.php">🚪 Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <div class="container">
        <h2>Begrüßungsnachricht einstellen</h2>
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">✅ Erfolgreich gespeichert!</p>
        <?php endif; ?>

        <form method="post">
            <div>
                <label for="message">📝 Nachricht:</label><br>
                <textarea id="message" name="message" rows="4" cols="50" required><?= htmlspecialchars($settings['message']) ?></textarea>
                <p><small>Hinweis: Verwende <code>{user}</code> um den Namen des neuen Mitglieds einzufügen.</small></p>
            </div>

            <div>
                <label for="channel_id">#️⃣ Channel-ID:</label><br>
                <input type="text" id="channel_id" name="channel_id" value="<?= htmlspecialchars($settings['channel_id']) ?>" required>
                <p><small>Gib die ID des Channels an, in dem die Willkommensnachricht gesendet werden soll.</small></p>
            </div>

            <div>
                <label for="color">🎨 Embed-Farbe:</label><br>
                <input type="color" id="color" name="color" value="<?= htmlspecialchars($settings['color']) ?>">
                <p><small>Wähle eine Farbe für die Nachricht.</small></p>
            </div>

            <button type="submit" style="margin-top: 20px;">💾 Speichern</button>
        </form>
    </div>
</main>

<footer>
    <p>&copy; Novarix Studio</p>
</footer>
</body>
</html>
