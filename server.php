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

// Gilden vom Nutzer abrufen
$guilds_response = file_get_contents("https://discord.com/api/users/@me/guilds", false, stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $access_token"
    ]
]));

$guilds = json_decode($guilds_response, true);

// Server suchen und Berechtigungen prüfen
$selected_guild = null;
foreach ($guilds as $guild) {
    if ($guild['id'] === $server_id && ($guild['permissions'] & 0x00000008)) { // Admin-Recht (bit 3)
        $selected_guild = $guild;
        break;
    }
}

if (!$selected_guild) {
    die("Du hast keine Administratorrechte auf diesem Server oder der Server existiert nicht.");
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Server bearbeiten – <?= htmlspecialchars($selected_guild['name']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1><?= htmlspecialchars($selected_guild['name']) ?> bearbeiten</h1>
    <nav>
        <ul>
            <li><a href="dashboard.php">Zurück zur Server Auswahl</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
<div class="container">
    <aside class="sidebar">
        <ul>
            <li><a href="#">🔧 Allgemein</a></li>
            <li><a href="#">⚙️ Bot-Einstellungen</a></li>
            <li><a href="#">📢 Logging</a></li>
            <li><a href="#">🎭 Rollen</a></li>
            <li><a href="#">🤖 Automationen</a></li>
            <li><a href="#">🧪 Custom Commands</a></li>
        </ul>
    </aside>

    <main class="content">
        <div class="hero">
            <img src="https://cdn.discordapp.com/icons/<?= $selected_guild['id'] ?>/<?= $selected_guild['icon'] ?>.png"
                alt="Server Icon"
                style="width: 128px; border-radius: 50%; margin-bottom: 20px;">
            <h2>Willkommen im Server-Editor</h2>
            <p>Hier kannst du später Einstellungen vornehmen (z. B. Webhooks, Prefix, Rollen usw.).</p>

            <!-- Beispiel Button -->
            <a href="#" class="discord-btn" style="margin-top: 20px;">
                Funktion hinzufügen
            </a>
        </div>
    </main>
</div>

</main>

<footer>
    <p>&copy; Novarix Studio</p>
    <div>
        <a href="https://novarix-studio.de">Hauptseite</a> |
        <a href="#">Dokumentation</a>
    </div>
</footer>
</body>
</html>
