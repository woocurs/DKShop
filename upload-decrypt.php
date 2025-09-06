<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$allowedTypes = ['application/octet-stream', 'application/x-msdownload', 'text/plain', 'application/pdf']; // Accept .enc mimetypes loosely

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && isset($_POST['encryption_type'])) {
        $file = $_FILES['file'];
        $encType = $_POST['encryption_type'];
        $key = $_POST['decryption_key'] ?? '';

        if ($file['error'] !== UPLOAD_ERR_OK) {
            die("Upload error.");
        }

        // Optional: You can check $file['type'] or extension for .enc but it might be inconsistent

        // Read file content
        $encryptedContent = file_get_contents($file['tmp_name']);
        $decryptedContent = '';

        if ($encType === 'AES') {
            if (empty($key)) {
                die("Decryption key is required for AES.");
            }
            $cipher = "AES-128-CTR";
            $data = base64_decode($encryptedContent);
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = substr($data, 0, $ivlen);
            $encryptedRaw = substr($data, $ivlen);
            $decryptedContent = openssl_decrypt($encryptedRaw, $cipher, $key, 0, $iv);

            if ($decryptedContent === false) {
                die("Decryption failed. Wrong key or corrupted file.");
            }
        } elseif ($encType === 'BASE64') {
            $decryptedContent = base64_decode($encryptedContent);
        } else {
            die("Unknown encryption type.");
        }

        // Save decrypted file (overwrite existing filename without .enc)
        $originalName = preg_replace('/\.enc$/i', '', $file['name']);
        if (!$originalName) {
            $originalName = $file['name'] . '_decrypted';
        }

        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $newFileName = time() . '_decrypted_' . $originalName;
        $filePath = $uploadDir . $newFileName;

        if (file_put_contents($filePath, $decryptedContent)) {
            // Insert into DB as decrypted file with encryption_type = None or "Decrypted"
            $stmt = $conn->prepare("INSERT INTO files (user_id, original_filename, stored_filename, encryption_type) VALUES (?, ?, ?, ?)");
            $encLabel = "Decrypted";
            $stmt->bind_param("isss", $user_id, $originalName, $newFileName, $encLabel);
            $stmt->execute();

            header("Location: dashboard.php?msg=File+uploaded+and+decrypted+successfully");
            exit;
        } else {
            die("Failed to save decrypted file.");
        }

    } else {
        die("Invalid request.");
    }
} else {
    die("Invalid request method.");
}
