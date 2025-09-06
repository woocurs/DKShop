<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$file_id = intval($_GET['id'] ?? 0);

if ($file_id <= 0) {
    die("Invalid file ID.");
}

// Get file info
$stmt = $conn->prepare("SELECT stored_filename FROM files WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("File not found or access denied.");
}

$file = $result->fetch_assoc();
$filePath = 'uploads/' . $file['stored_filename'];

// Delete file from server
if (file_exists($filePath)) {
    unlink($filePath);
}

// Delete DB record
$stmtDel = $conn->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
$stmtDel->bind_param("ii", $file_id, $user_id);
$stmtDel->execute();

header("Location: dashboard.php?msg=File+deleted+successfully");
exit;
