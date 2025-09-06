<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$user_id = $_SESSION['user_id'];
$file_id = intval($_POST['file_id'] ?? 0);
$key = $_POST['decryption_key'] ?? '';

if (empty($key) || $file_id <= 0) {
    die("File ID and key are required.");
}

// Fetch file info & check ownership
$stmt = $conn->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("File not found or access denied.");
}

$file = $result->fetch_assoc();

$filePath = 'uploads/' . $file['stored_filename'];
if (!file_exists($filePath)) {
    die("File not found on server.");
}

$encryptedContent = file_get_contents($filePath);
$encryptionType = $file['encryption_type'];

// AES decrypt function
function aesDecryptFileContent($encryptedBase64, $key) {
    $cipher = "AES-128-CTR";
    $data = base64_decode($encryptedBase64);
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = substr($data, 0, $ivlen);
    $encryptedRaw = substr($data, $ivlen);
    return openssl_decrypt($encryptedRaw, $cipher, $key, 0, $iv);
}

// Base64 decode function
function base64DecryptFileContent($encryptedBase64) {
    return base64_decode($encryptedBase64);
}

if ($encryptionType === 'AES') {
    $decryptedContent = aesDecryptFileContent($encryptedContent, $key);
    if ($decryptedContent === false) {
        die("Decryption failed. Wrong key or corrupted file.");
    }
} elseif ($encryptionType === 'BASE64') {
    $decryptedContent = base64DecryptFileContent($encryptedContent);
} else {
    die("Unknown encryption type.");
}

// Serve decrypted file for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['original_filename']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($decryptedContent));

echo $decryptedContent;
exit;
