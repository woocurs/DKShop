<?php
session_start();
require 'includes/db.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$allowedTypes = ['application/pdf', 'application/msword', 'text/plain', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']; // PDF, DOC, TXT, DOCX

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && isset($_POST['encryption_type'])) {
        $file = $_FILES['file'];
        $encType = $_POST['encryption_type'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            die("Upload error.");
        }

        if (!in_array($file['type'], $allowedTypes)) {
            die("File type not allowed.");
        }

        // Read file content
        $content = file_get_contents($file['tmp_name']);
        $encryptedContent = '';

        // Encryption key - In real, generate or ask user; for demo hardcoded
        // $key = 'MySecretKey12345'; // 16 chars for AES-128

        $key = $_POST['encryption_key'] ?? '';
        if (empty($key)) {
            die("Encryption key is required.");
        }
        // Optional: enforce key length or complexity here


        if ($encType === 'AES') {
            $cipher = "AES-128-CTR";
            $ivlen = openssl_cipher_iv_length($cipher);
            $iv = openssl_random_pseudo_bytes($ivlen);
            $encryptedRaw = openssl_encrypt($content, $cipher, $key, 0, $iv);
            $encryptedContent = base64_encode($iv . $encryptedRaw);
        } elseif ($encType === 'BASE64') {
            $encryptedContent = base64_encode($content);
        } else {
            die("Unknown encryption type.");
        }

        // Save encrypted file
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $newFileName = time() . '_' . basename($file['name']) . '.enc';
        $filePath = $uploadDir . $newFileName;

        if (file_put_contents($filePath, $encryptedContent)) {
            // Insert into DB
            $stmt = $conn->prepare("INSERT INTO files (user_id, original_filename, stored_filename, encryption_type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $file['name'], $newFileName, $encType);
            $stmt->execute();

            header("Location: dashboard.php?msg=File+encrypted+and+uploaded+successfully");
            exit;
        } else {
            die("Failed to save encrypted file.");
        }

    } else {
        die("Invalid request.");
    }
} else {
    die("Invalid request method.");
}
