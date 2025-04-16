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
       <a href="login.php" class="discord-btn">
    <img src="discord.png" alt="Discord Logo" width="20" height="20">
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
