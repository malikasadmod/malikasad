<?php
session_start();

// CSS style for the form (embedded for simplicity)
echo "
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>User Registration</title>
<style>
body {
  font-family: Arial, sans-serif;
  background-color: #f0f0f0;
}

.container {
  max-width: 400px;
  margin: 50px auto;
  padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h2 {
  text-align: center;
}

form {
  display: flex;
  flex-direction: column;
}

input[type='text'],
input[type='password'],
button {
  margin-bottom: 10px;
  padding: 10px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

button {
  background-color: #4CAF50;
  color: white;
  cursor: pointer;
}

button:hover {
  background-color: #45a049;
}
</style>
</head>
<body>
<div class='container'>
  <h2>Register</h2>
  <form action='register.php' method='post'>
    <input type='text' name='username' placeholder='Username' required>
    <input type='password' name='password' placeholder='Password' required>
    <button type='submit'>Register</button>
  </form>
</div>
</body>
</html>
";

// Registration logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        echo "Please fill in all fields.";
    } else {
        // Check if the file exists
        $file = 'userdata.txt';
        if (!file_exists($file)) {
            // Create the file if it doesn't exist
            fopen($file, 'w');
        }
        
        // Check if username already exists
        $users = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($users as $user) {
            $details = explode(':', $user);
            if ($details[0] === $username) {
                echo "Username already exists. Please choose another.";
                exit;
            }
        }
        
        // Append user details to the file
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash password
        file_put_contents($file, "$username:$hashed_password:0\n", FILE_APPEND); // Balance initialized to 0
        
        echo "Registration successful. <a href='login.php'>Go to Login</a>";
    }
}
?>
