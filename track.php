<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Open or create the SQLite database
$db_path = __DIR__ . '/telemetry.sqlite';
try {
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create clicks table if it doesn't exist
    $pdo->exec('CREATE TABLE IF NOT EXISTS clicks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        ip TEXT,
        city TEXT,
        region TEXT,
        postal TEXT,
        country TEXT,
        isp TEXT,
        page TEXT,
        cta TEXT,
        user_agent TEXT,
        referer TEXT
    )');
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Retrieve input data
$input = json_decode(file_get_contents('php://input'), true);
$page = $input['page'] ?? $_POST['page'] ?? '';
$cta = $input['cta'] ?? $_POST['cta'] ?? '';

// Get IP address
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
}
$ip = trim($ip);

// Basic local IP bypass to avoid slow lookup during dev
$city = 'Local/Unknown';
$region = 'Local/Unknown';
$postal = '';
$country = 'Local/Unknown';
$isp = 'Local/Unknown';

if ($ip && $ip !== '127.0.0.1' && $ip !== '::1' && !preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1]))/', $ip)) {
    // Perform Geolocation lookup via ip-api.com (1.5 seconds timeout)
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 1.5,
            'header' => "User-Agent: RodentControlTruckee-Telemetry/1.0\r\n"
        ]
    ]);
    $geo_json = @file_get_contents("http://ip-api.com/json/{$ip}", false, $ctx);
    if ($geo_json) {
        $geo = json_decode($geo_json, true);
        if (isset($geo['status']) && $geo['status'] === 'success') {
            $city = $geo['city'] ?? 'Unknown';
            $region = $geo['regionName'] ?? 'Unknown';
            $postal = $geo['zip'] ?? '';
            $country = $geo['country'] ?? 'Unknown';
            $isp = $geo['isp'] ?? 'Unknown';
        }
    }
}

// Insert event record into SQLite database
try {
    $stmt = $pdo->prepare('INSERT INTO clicks (ip, city, region, postal, country, isp, page, cta, user_agent, referer) VALUES (:ip, :city, :region, :postal, :country, :isp, :page, :cta, :user_agent, :referer)');
    $stmt->execute([
        ':ip' => $ip,
        ':city' => $city,
        ':region' => $region,
        ':postal' => $postal,
        ':country' => $country,
        ':isp' => $isp,
        ':page' => $page,
        ':cta' => $cta,
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ':referer' => $_SERVER['HTTP_REFERER'] ?? ''
    ]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save track event: ' . $e->getMessage()]);
}
