<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}


// Zugriffstoken holen
$access_token = $_SESSION['access_token'] ?? null;

if (!$access_token) {
    die("Kein Zugriffstoken gefunden.");
}

// Gilden abrufen
$guilds_response = file_get_contents("https://discord.com/api/users/@me/guilds", false, stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $access_token"
    ]
]));

$guilds = json_decode($guilds_response, true);

if (!is_array($guilds)) {
    die("Fehler beim Abrufen der Gilden.");
}

// Nur Gilden mit Administrator-Rechten filtern
$admin_guilds = array_filter($guilds, function($guild) {
    // Administrator = 0x00000008 (Bit 3)
    return ($guild['permissions'] & 0x00000008);
});
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – Serverübersicht</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>Novarix Dashboard</h1>
    <nav>
        <ul>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<main>
    <h2>Serverübersicht</h2>

    <?php if (empty($admin_guilds)): ?>
        <p>Du hast auf keinem Server Administratorrechte.</p>
    <?php else: ?>
        <div class="server-list" style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;">
            <?php foreach ($admin_guilds as $guild): ?>
                <div class="server-card" style="background: #1a1a1a; padding: 20px; border-radius: 10px; width: 220px;">
                    <img src="https://cdn.discordapp.com/icons/<?= $guild['id'] ?>/<?= $guild['icon'] ?>.png" alt="<?= htmlspecialchars($guild['name']) ?>" style="width: 100%; border-radius: 8px;">
                    <h3 style="margin: 10px 0;"><?= htmlspecialchars($guild['name']) ?></h3>

                    <!-- Wenn Bot auf dem Server ist oder nicht -->
                    <?php if ($guild['owner']): ?>
                        <p>Du bist der Serverbesitzer</p>
                    <?php endif; ?>

                    <a href="server.php?id=<?= $guild['id'] ?>" style="display: inline-block; margin-top: 10px; background: #a64eff; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none;">
                        Server bearbeiten
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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
