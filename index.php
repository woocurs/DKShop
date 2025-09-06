<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome to File Encryption System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="#">Encryption System</a>
    <div>
      <a href="index.php" class="btn btn-outline-light me-2">Home</a>
      <?php if(isset($_SESSION['user_id'])): ?>
        <a href="dashboard.php" class="btn btn-success">Dashboard</a>
        <a href="logout.php" class="btn btn-danger ms-2">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-primary">Login</a>
        <a href="register.php" class="btn btn-secondary ms-2">Register</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- New Half-Page Background Section with Cards -->
<section style="background: url('images/Cryptography.png') no-repeat center center/cover; min-height: 50vh; padding: 3rem 0;">
  <div class="container">
    </div>
  </div>
</section>


<div class="container my-5">

  <div class="text-center mb-5">
    <h1>Welcome to Your File Encryption & Decryption System</h1>
    <p class="lead">Securely encrypt and decrypt your documents online with ease.</p>
  </div>

  <div class="row g-4">

    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">User Registration & Login</h5>
          <p class="card-text">Create your account and securely access your encrypted files anytime</p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Upload & Encrypt Files</h5>
          <p class="card-text">Upload documents and choose encryption algorithms to protect your data.</p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Online Decryption & Management</h5>
          <p class="card-text">Decrypt your files online by providing keys, download or delete files securely.</p>
        </div>
      </div>
    </div>

  </div>
</div>

<footer class="bg-dark text-white text-center py-3 mt-5">
  &copy; <?= date('Y') ?> Encryption System. All rights reserved.
</footer>

</body>
</html>
