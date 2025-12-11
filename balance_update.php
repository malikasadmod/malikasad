<?php
// Function to retrieve all usernames from file
function getAllUsernames($file) {
    $usernames = [];
    if (file_exists($file)) {
        $users = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($users as $user) {
            $details = explode(':', $user);
            $usernames[] = $details[0];
        }
    }
    return $usernames;
}

// Function to update balance for a specific user
function updateBalance($targetUser, $amount, $file, $operation) {
    $updated = false;
    if (file_exists($file)) {
        $users = file($file, FILE_IGNORE_NEW_LINES);
        $updatedUsers = [];
        foreach ($users as $user) {
            $details = explode(':', $user);
            if ($details[0] === $targetUser) {
                if ($operation === 'add') {
                    $details[2] += $amount; // Add balance
                } elseif ($operation === 'remove') {
                    if ($details[2] >= $amount) {
                        $details[2] -= $amount; // Remove balance if sufficient funds
                    } else {
                        return false; // Return false if insufficient balance
                    }
                }
                $updated = true;
            }
            $updatedUsers[] = implode(':', $details);
        }
        if ($updated) {
            file_put_contents($file, implode("\n", $updatedUsers) . "\n");
        }
    }
    return $updated;
}

// Handle balance update logic
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $targetUser = $_POST['target_user'];
    $amount = (float) $_POST['amount'];
    $operation = $_POST['operation']; // 'add' or 'remove'

    // Validate input (for demo purposes)
    if (!in_array($targetUser, getAllUsernames('userdata.txt'))) {
        $message = "Invalid target user.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $message = "Invalid amount input.";
    } else {
        // Update balance for the target user based on operation
        if ($operation === 'add') {
            if (updateBalance($targetUser, $amount, 'userdata.txt', 'add')) {
                $message = "Balance added successfully for $targetUser.";
            } else {
                $message = "Failed to add balance. Please try again.";
            }
        } elseif ($operation === 'remove') {
            if (updateBalance($targetUser, $amount, 'userdata.txt', 'remove')) {
                $message = "Balance removed successfully for $targetUser.";
            } else {
                $message = "Insufficient balance or failed to remove balance. Please try again.";
            }
        }
    }
}

// CSS style for the form (embedded for simplicity)
echo "
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Balance Update</title>
<style>
body {
  font-family: Arial, sans-serif;
  background-color: #f0f0f0;
  margin: 0;
  padding: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}

.container {
  max-width: 400px;
  padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  text-align: center;
}

form {
  margin-bottom: 20px;
}

.label {
  font-weight: bold;
  margin-bottom: 10px;
}

.input-group {
  margin-bottom: 15px;
  text-align: left;
}

.input-group label {
  display: block;
  margin-bottom: 5px;
}

.input-group select,
.input-group input[type='number'] {
  width: 100%;
  padding: 8px;
  font-size: 16px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

.input-group input[type='submit'] {
  background-color: #4CAF50;
  color: white;
  padding: 10px 20px;
  text-decoration: none;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.input-group input[type='submit']:hover {
  background-color: #45a049;
}

.message {
  color: #ff0000;
  margin-bottom: 10px;
}

.footer {
  font-size: 14px;
  color: #666;
}
</style>
</head>
<body>
<div class='container'>
  <h2>Balance Update</h2>
  
  <form method='post'>
    <div class='input-group'>
      <label for='target_user'>Select User:</label>
      <select id='target_user' name='target_user'>";
      // Option to select all usernames
      foreach (getAllUsernames('userdata.txt') as $user) {
          echo "<option value='$user'>$user</option>";
      }
      echo "
      </select>
    </div>
    
    <div class='input-group'>
      <label for='operation'>Operation:</label>
      <select id='operation' name='operation'>
        <option value='add'>Add Balance</option>
        <option value='remove'>Remove Balance</option>
      </select>
    </div>
    
    <div class='input-group'>
      <label for='amount'>Amount:</label>
      <input type='number' id='amount' name='amount' step='0.01' placeholder='Enter amount' required>
    </div>
    
    <input type='submit' value='Submit'>
  </form>
  
  <div class='message'>$message</div>
  
  <div class='footer'>PHP User Management Example | &copy; 2024 YourCompany</div>
</div>
</body>
</html>
";
?>
