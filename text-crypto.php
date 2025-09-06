<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

function encryptText($plaintext, $key) {
    $cipher = "AES-128-CTR";
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $encryptedRaw = openssl_encrypt($plaintext, $cipher, $key, 0, $iv);
    return base64_encode($iv . $encryptedRaw);
}

function decryptText($ciphertext, $key) {
    $cipher = "AES-128-CTR";
    $data = base64_decode($ciphertext);
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = substr($data, 0, $ivlen);
    $encryptedRaw = substr($data, $ivlen);
    return openssl_decrypt($encryptedRaw, $cipher, $key, 0, $iv);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = $_POST['text'] ?? '';
    $mode = $_POST['mode'] ?? '';
    $key = $_POST['key'] ?? '';

    $result = '';

    if ($mode === 'encrypt') {
        if (empty($key)) {
            die("Key is required for AES encryption.");
        }
        $result = encryptText($text, $key);
    } elseif ($mode === 'decrypt') {
        if (empty($key)) {
            die("Key is required for AES decryption.");
        }
        $result = decryptText($text, $key);
        if ($result === false) $result = "Decryption failed. Check your key and input.";
    } else {
        die("Invalid mode.");
    }
} else {
    die("Invalid request.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Text Crypto Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3>Result</h3>
    <textarea class="form-control" rows="8" readonly><?= htmlspecialchars($result) ?></textarea>
    <a href="dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>
</div>
</body>
</html>
