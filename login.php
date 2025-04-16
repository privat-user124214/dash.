<?php
$client_id = '1284484623279067196';
$redirect_uri = urlencode('https://dash.novarix-studio.de/callback.php');
$scope = 'identify email guilds';

$oauth_path = "/oauth2/authorize?client_id=$client_id&response_type=code&redirect_uri=$redirect_uri&scope=$scope";

// Leitet zu Discord Login, wenn nicht eingeloggt – sonst direkt zu Oauth2
$auth_url = "https://discord.com/login?redirect_to=" . urlencode($oauth_path);

header("Location: $auth_url");
exit;
?>
