<?php
session_start();

$client_id = "1284484623279067196";
$client_secret = "rWr9kGV33-rTIvhv6ACi-NrJVxbQFQKy";
$redirect_uri = "https://dash.novarix-studio.de/callback.php";

if (!isset($_GET['code'])) {
    die("Kein Code erhalten.");
}

$code = $_GET['code'];

$token_url = "https://discord.com/api/oauth2/token";

$data = [
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'grant_type' => 'authorization_code',
    'code' => $code,
    'redirect_uri' => $redirect_uri,
    'scope' => 'identify email'
];

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    die("Fehler: " . curl_error($ch));
}
curl_close($ch);

$token_data = json_decode($response, true);

if (!isset($token_data['access_token'])) {
    die("Zugriffstoken fehlt.");
}

$access_token = $token_data['access_token'];
$_SESSION['access_token'] = $access_token;


// Nutzerdaten abfragen
$user_response = file_get_contents("https://discord.com/api/users/@me", false, stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer $access_token"
    ]
]));

$user = json_decode($user_response, true);

if (isset($user['id'])) {
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'] . '#' . $user['discriminator'],
        'email' => $user['email'] ?? null,
        'avatar' => "https://cdn.discordapp.com/avatars/{$user['id']}/{$user['avatar']}.png"
    ];

    // === DISCORD WEBHOOK LOGIN LOGGER ===
    $webhook_url = 'https://discord.com/api/webhooks/1360954499925278950/pveoTWeFUJTtXaX2sdLN9rA2CGvlQo9_k4cMtYQRsydaiApKJm5EVQxRmF_sDp5IpkiP';

    $embed = [
        "title" => "🔐 Neuer Dashboard Login",
        "color" => hexdec("8c4f97"),
        "thumbnail" => [
            "url" => "https://cdn.discordapp.com/avatars/{$user['id']}/{$user['avatar']}.png"
        ],
        "fields" => [
            [
                "name" => "Benutzername",
                "value" => $user['username'] . "#" . $user['discriminator'],
                "inline" => true
            ],
            [
                "name" => "E-Mail",
                "value" => $user['email'] ?? 'Keine E-Mail angegeben',
                "inline" => true
            ]
        ],
        "footer" => [
            "text" => "Novarix Studio Login-System"
        ],
        "timestamp" => date("c")
    ];

    $payload = json_encode([
        "username" => "Dashboard Logger",
        "embeds" => [$embed]
    ]);

    $ch = curl_init($webhook_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_exec($ch);
    curl_close($ch);

    // Weiterleitung zum Dashboard
    header("Location: dashboard.php");
    exit();
} else {
    die("Fehler beim Abrufen der Nutzerdaten.");
}
