<?php
// start_oauth.php
$client_id = '1284484623279067196';
$redirect_uri = urlencode('https://dash.novarix-studio.de/callback.php');
$scope = 'identify email guilds';

$oauth_url = "https://discord.com/oauth2/authorize?client_id=$client_id&response_type=code&redirect_uri=$redirect_uri&scope=$scope";

header("Location: $oauth_url");
exit;
