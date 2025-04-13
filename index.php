<?php
session_start();

// Wenn der Nutzer bereits eingeloggt ist, leite ihn weiter zum Dashboard
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

// Discord OAuth2 URL
$client_id = "1284484623279067196";
$redirect_uri = urlencode("https://dash.novarix-studio.de/callback.php");

$discord_oauth_url = "https://discord.com/oauth2/authorize?client_id=1284484623279067196&response_type=code&redirect_uri=https%3A%2F%2Fdash.novarix-studio.de%2Fcallback.php&scope=identify+email+guilds
";
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Login mit Discord</title>
    <link rel="stylesheet" href="style.css"> <!-- Falls du ein zentrales Stylesheet nutzt -->
</head>
<body>
    <header>
        <h1>Novarix Studio Dashboard</h1>
    </header>
    <main>
        <h2>Login mit Discord</h2>
        <a href="<?= $discord_oauth_url ?>" style="display: inline-flex; align-items: center; background-color: #5865F2; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 16px;">
            <img src="https://cdn.icon-icons.com/icons2/2108/PNG/512/discord_icon_130958.png" alt="Discord" style="width: 24px; height: 24px; margin-right: 10px;">
            Mit Discord einloggen
        </a>
    </main>
    <footer>
        <p>&copy; Novarix Studio</p>
        <a href="https://novarix-studio.de">Hauptseite</a> |
        <a href="#">Documentation</a>
    </footer>
</body>
</html>
