<?php
session_start();

if (!isset($_SESSION['user'], $_SESSION['access_token'])) {
    header("Location: index.php");
    exit();
}

$access_token = $_SESSION['access_token'];

if (!isset($_GET['id'])) {
    die("Keine Server-ID angegeben.");
}

$server_id = $_GET['id'];

$guilds_response = file_get_contents("https://discord.com/api/users/@me/guilds", false, stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $access_token"
    ]
]));

$guilds = json_decode($guilds_response, true);

$selected_guild = null;
foreach ($guilds as $guild) {
    if ($guild['id'] === $server_id && ($guild['permissions'] & 0x00000008)) {
        $selected_guild = $guild;
        break;
    }
}

if (!$selected_guild) {
    die("Du hast keine Administratorrechte auf diesem Server oder der Server existiert nicht.");
}
