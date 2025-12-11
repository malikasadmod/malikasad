<?php
session_start();

// Check if admin is already logged in
if (isset($_SESSION['admin_username'])) {
    header('Location: admin_panel.php');
    exit;
}

// Dummy admin credentials (replace with actual authentication mechanism)
$adminUsername = 'admin';
$adminPassword = 'admin123';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate admin credentials
    if ($username === $adminUsername && $password === $adminPassword) {
        // Authentication successful, set session variables
        $_SESSION['admin_username'] = $username;
        header('Location: admin_panel.php');
        exit;
    } else {
        // Authentication failed
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome for icons -->
<style>
body {
  font-family: Arial, sans-serif;
  background-color: #f0f0f0;
  margin: 0;
  padding: 0;
}

.container {
  max-width: 300px;
  margin: 100px auto;
  padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.label {
  font-weight: bold;
  margin-bottom: 5px;
}

.input-group {
  margin-bottom: 10px;
}

.input-group label {
  display: block;
  margin-bottom: 5px;
  text-align: left;
}

.input-group input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

.button-group {
  text-align: center;
}

.button-group button {
  background-color: #4CAF50;
  color: white;
  padding: 10px 20px;
  text-decoration: none;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.button-group button:hover {
  background-color: #45a049;
}

.error {
  color: red;
  margin-bottom: 10px;
}
</style>
</head>
<body>

<div class="container">
  <h2>Admin Login</h2>

  <form method="post" action="">
    <div class="input-group">
      <label for="username">Username:</label>
      <input type="text" id="username" name="username" required>
    </div>

    <div class="input-group">
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>
    </div>

    <div class="button-group">
      <button type="submit" name="login">Login</button>
    </div>

    <?php if (isset($error)): ?>
      <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
  </form>
</div>

</body>
</html>
