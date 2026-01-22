<?php
require __DIR__ . '/../../vendor/autoload.php';

use App\Database;

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('Missing image ID');
}

$db = Database::connect();

$stmt = $db->prepare("
    SELECT cdn_url, original_name
    FROM images
    WHERE id = ?
");
$stmt->execute([(int) $_GET['id']]);
$image = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$image) {
    http_response_code(404);
    exit('Image not found');
}

$filename = basename($image['original_name']);

// Fetch file from Cloudinary
$ch = curl_init($image['cdn_url']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$data = curl_exec($ch);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

header('Content-Type: ' . ($contentType ?: 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($data));

echo $data;
exit;
