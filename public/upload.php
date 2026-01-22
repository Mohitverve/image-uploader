<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/helpers.php';

use App\Config;
use App\Database;
use App\ImageService;

/**
 * Load ENV FIRST (critical)
 */
Config::loadEnv();

/**
 * Now Cloudinary can safely read $_ENV
 */
require __DIR__ . '/../src/config/cloudinary.php';
require __DIR__ . '/../src/helpers/cloudinary.php';

/**
 * Helper for redirecting with error
 */
function fail(string $message): void
{
    $_SESSION['error'] = $message;
    header('Location: index.php');
    exit;
}

/**
 * Validate upload
 */
if (!isset($_FILES['image'])) {
    fail('No file uploaded');
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    fail('Upload failed');
}

/**
 * Validate MIME type
 */
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowed = [
    'image/jpeg',
    'image/png',
    'image/webp'
];

if (!in_array($mime, $allowed, true)) {
    fail('Unsupported image type');
}

/**
 * Temporary file paths
 */
$tmpInput  = sys_get_temp_dir() . '/' . uniqid('img_in_', true);
$tmpOutput = sys_get_temp_dir() . '/' . uniqid('img_out_', true) . '.webp';

if (!move_uploaded_file($file['tmp_name'], $tmpInput)) {
    fail('Failed to move uploaded file');
}

/**
 * Optimise image locally (Imagick)
 */
$result = ImageService::optimise($tmpInput, $tmpOutput);

/**
 * Upload optimised image to Cloudinary
 */
$publicId = uniqid('img_', true);
$cdnUrl   = uploadToCloudinary($tmpOutput, $publicId);

/**
 * Save metadata to database
 */
$db = Database::connect();

$stmt = $db->prepare("
    INSERT INTO images
        (original_name, original_mime, optimized_path, width, height, size_kb, cdn_url, created_at)
    VALUES
        (?, ?, ?, ?, ?, ?, ?, NOW())
");

$stmt->execute([
    $file['name'],
    $mime,
    null, // no local storage anymore
    $result['width'],
    $result['height'],
    round($result['size'] / 1024),
    $cdnUrl
]);

/**
 * Cleanup temp files
 */
@unlink($tmpInput);
@unlink($tmpOutput);

/**
 * Success feedback
 */
$_SESSION['success'] = 'Image uploaded and optimised successfully';
$_SESSION['image'] = [
    'cdn_url' => $cdnUrl,
    'width'   => $result['width'],
    'height'  => $result['height'],
    'size_kb' => round($result['size'] / 1024),
];

/**
 * Redirect back to UI
 */
header('Location: index.php');
exit;
