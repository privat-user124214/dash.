<?php
// login.php
$redirect = urlencode("https://dash.novarix-studio.de/start_oauth.php");
header("Location: https://discord.com/login?redirect_to=$redirect");
exit;
