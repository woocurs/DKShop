<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch user's uploaded files
$sql = "SELECT * FROM files WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Encryption System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Welcome, <?= htmlspecialchars($username) ?></h3>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <!-- File Upload -->
    <div class="card mb-4">
        <div class="card-header">Upload File for Encryption</div>
        <div class="card-body">
            <form id="uploadEncryptForm" action="upload.php" method="POST" enctype="multipart/form-data">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Choose file (PDF, DOC, TXT)</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Encryption Method</label>
                        <select name="encryption_type" class="form-select" required>
                            <option value="AES">AES (OpenSSL)</option>
                            <option value="BASE64">Base64</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Encryption Key</label>
                        <input type="text" name="encryption_key" class="form-control" placeholder="Enter key" required>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" type="submit">Upload & Encrypt</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

        <!-- Upload Encrypted File for Decryption -->
    <div class="card mb-4">
        <div class="card-header">Upload Encrypted File to Decrypt</div>
        <div class="card-body">
            <form action="upload-decrypt.php" method="POST" enctype="multipart/form-data">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Choose Encrypted File (.enc)</label>
                        <input type="file" name="file" class="form-control" accept=".enc" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Encryption Type</label>
                        <select name="encryption_type" class="form-select" required>
                            <option value="AES">AES (OpenSSL)</option>
                            <option value="BASE64">Base64</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Decryption Key (for AES)</label>
                        <input type="text" name="decryption_key" class="form-control" placeholder="Key if AES" >
                    </div>
                    <div class="col-md-12 mt-2">
                        <button class="btn btn-warning w-100" type="submit">Upload & Decrypt</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <!-- Text Encryption/Decryption -->
    <div class="card mb-4">
        <div class="card-header">Encrypt / Decrypt Text</div>
        <div class="card-body">
            <form action="text-crypto.php" method="POST">
                <div class="mb-2">
                    <textarea name="text" class="form-control" placeholder="Enter your text here..." required></textarea>
                </div>
                <div class="row g-2">
                    <div class="col-md-4">
                        <select name="mode" class="form-select" required>
                            <option value="encrypt">Encrypt</option>
                            <option value="decrypt">Decrypt</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="key" class="form-control" placeholder="Enter Key (for AES)">
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-secondary w-100" type="submit">Run</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Uploaded Files Table -->
    <div class="card">
        <div class="card-header">Your Encrypted Files</div>
        <div class="card-body p-0">
            <table class="table table-striped m-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Original Filename</th>
                        <th>Encryption</th>
                        <th>Uploaded On</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): $i = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($row['original_filename']) ?></td>
                                <td><?= $row['encryption_type'] ?></td>
                                <td><?= $row['created_at'] ?></td>
                                <td>
                                    <?php if ($row['encryption_type'] === 'Decrypted'): ?>
                                    <a href="uploads/<?= htmlspecialchars($row['stored_filename']) ?>" class="btn btn-sm btn-outline-success" download>Download Decrypted</a>
                                    <?php else: ?>
                                        <a href="uploads/<?= $row['stored_filename'] ?>" class="btn btn-sm btn-outline-primary" download>Download Encrypted</a>
                                        <a href="decrypt-file-form.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-success">Download Decrypted</a>
                                    <?php endif; ?>

                                    <a href="delete-file.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this file?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No files uploaded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
document.getElementById('uploadEncryptForm').addEventListener('submit', function(e) {
  const keyInput = this.querySelector('input[name="encryption_key"]');
  if (keyInput.value.trim().length < 6) {
    alert("Encryption key must be at least 6 characters.");
    e.preventDefault();
  }
});
</script>
</body>
</html>
