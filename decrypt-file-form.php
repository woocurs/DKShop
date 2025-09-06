<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("File ID missing.");
}

$file_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Fetch file info & check ownership
$stmt = $conn->prepare("SELECT original_filename FROM files WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("File not found or access denied.");
}

$file = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Enter Decryption Key</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 col-md-6 offset-md-3">
    <h3>Decrypt File: <?= htmlspecialchars($file['original_filename']) ?></h3>
    <form method="POST" action="decrypt-file.php">
        <input type="hidden" name="file_id" value="<?= $file_id ?>">
        <div class="mb-3">
            <label>Enter Decryption Key</label>
            <input type="text" name="decryption_key" class="form-control" required>
        </div>
        <button class="btn btn-success" type="submit">Download Decrypted File</button>
        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
