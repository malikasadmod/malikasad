<?php
session_start();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = "admin";
    $password = "admin";

    // Validate username and password
    if ($_POST["username"] === $username && $_POST["password"] === $password) {
        // Authentication successful; set session variables
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = $username;

        // Redirect to admin panel page
        header("Location: admin_panel.php");
        exit;
    } else {
        // Authentication failed; display error message
        $login_err = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel Login</title>
<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: Arial, sans-serif;
  line-height: 1.6;
  background-color: #f0f0f0;
}

.container {
  max-width: 400px;
  margin: 100px auto;
  padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.container h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #333;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  color: #333;
}

.form-group input {
  width: calc(100% - 10px);
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

.form-group input[type="submit"] {
  background-color: #333;
  color: #fff;
  border: none;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.form-group input[type="submit"]:hover {
  background-color: #555;
}
</style>
</head>
<body>
  <div class="container">
    <h2>Admin Panel Login</h2>
    <?php if(isset($login_err)): ?>
      <p style="color: red; text-align: center;"><?php echo $login_err; ?></p>
    <?php endif; ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
      <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div class="form-group" style="text-align: center;">
        <input type="submit" value="Login">
      </div>
    </form>
  </div>
</body>
</html>
