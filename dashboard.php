<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];

// Function to retrieve user data from file
function getUserData($username, $file) {
    $userData = null;
    if (file_exists($file)) {
        $users = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($users as $user) {
            $details = explode(':', $user);
            if ($details[0] === $username) {
                $userData = [
                    'username' => $details[0],
                    'password' => $details[1], // Normally hashed in a real scenario
                    'balance' => $details[2],
                ];
                break;
            }
        }
    }
    return $userData;
}

// Function to update user balance in file
function updateUserBalance($username, $file, $newBalance) {
    $tempFile = $file . '.tmp';
    $updated = false;
    $users = file($file, FILE_IGNORE_NEW_LINES);
    $fp = fopen($tempFile, 'w');

    foreach ($users as $user) {
        $details = explode(':', $user);
        if ($details[0] === $username) {
            $details[2] = $newBalance;
            $updated = true;
        }
        fwrite($fp, implode(':', $details) . PHP_EOL);
    }

    fclose($fp);
    if ($updated) {
        rename($tempFile, $file);
        return true;
    } else {
        unlink($tempFile);
        return false;
    }
}

// Function to retrieve pending requests for the current user
function getPendingRequests($username, $file) {
    $pendingRequests = [];
    if (file_exists($file)) {
        $pendingData = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($pendingData as $data) {
            $request = json_decode($data, true);
            if ($request['username'] === $username) {
                $pendingRequests[] = $request;
            }
        }
    }
    return $pendingRequests;
}

// Load user data to display current balance
$userData = getUserData($username, 'userdata.txt');
$balance = $userData['balance'] ?? 0;

// Array to store pricing for each option
$optionPrices = [
    'Jazz Database' => 300,
    'Zong Database' => 300,
    'Telenor Database' => 300,
    'Ufone Database' => 300,
    'Nadra Picture' => 1400,
    'CNIC Color Copy' => 1800,
    'Family Tree' => 5500,
    'CDR' => 3000,
    'CNIC Issue Date' => 180,
    'Father Name' => 120,
    'Mother Name' => 120,
    'Marriage Certificate' => 10000,
];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phoneNumber'], $_POST['network'])) {
    // Process purchase here
    $selectedOption = $_POST['network'];
    $selectedPhoneNumber = $_POST['phoneNumber'];

    // Check if selected option exists in $optionPrices array
    if (array_key_exists($selectedOption, $optionPrices)) {
        $optionPrice = $optionPrices[$selectedOption];

        // Deduct price from user's balance
        $newBalance = $balance - $optionPrice;

        // Check if user has sufficient balance
        if ($newBalance < 0) {
            // Handle insufficient balance error if needed
            echo "Insufficient balance.";
            exit;
        }

        // Update user's balance in file
        if (updateUserBalance($username, 'userdata.txt', $newBalance)) {
            // Save the pending data to a file or database for admin review
            $pendingData = [
                'username' => $username,
                'option' => $selectedOption,
                'phoneNumber' => $selectedPhoneNumber,
                'status' => 'pending', // Initial status
                'picture' => '', // Placeholder for picture path
                'link' => '',    // Placeholder for link to picture
            ];

            // Example: Save pending data to a file
            $pendingFile = 'pending_data.txt'; // Adjust file path as needed
            $fp = fopen($pendingFile, 'a'); // Append mode
            fwrite($fp, json_encode($pendingData) . PHP_EOL); // Store as JSON for easier retrieval
            fclose($fp);

            // Redirect to user dashboard with a success message
            header('Location: dashboard.php?status=success');
            exit;
        } else {
            echo "Failed to update balance.";
            exit;
        }
    } else {
        echo "Invalid option selected.";
        exit;
    }
}

// Function to display pending requests in a table with view button for 'activate' status
function displayPendingRequests($requests) {
    if (empty($requests)) {
        echo "<p>No pending requests.</p>";
    } else {
        echo "<table border='1'>
                <tr>
                    <th>Option</th>
                    <th>Phone Number</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>";
        foreach ($requests as $request) {
            echo "<tr>
                    <td>{$request['option']}</td>
                    <td>{$request['phoneNumber']}</td>
                    <td>{$request['status']}</td>
                    <td>";
            if ($request['status'] === 'activate') {
                echo "<button onclick=\"viewDetails('{$request['picture']}', '{$request['link']}')\">View</button>";
            }
            echo "</td>
                  </tr>";
        }
        echo "</table>";
    }
}

// Retrieve pending requests for the current user
$pendingRequests = getPendingRequests($username, 'pending_data.txt');

// HTML and CSS for the user dashboard
?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>User Dashboard</title>
<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'> <!-- Font Awesome for icons -->
<style>
body {
  font-family: Arial, sans-serif;
  background-color: #f0f0f0;
  margin: 0;
  padding: 0;
}

.navigation {
  background-color: #333;
  color: #fff;
  padding: 10px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.navigation .logo {
  font-size: 1.1rem;
  font-weight: bold;
  background-color: #4CAF50; /* Green background for logo */
  color: #fff;
  padding: 4px 4px;
  border-radius: 4px;
}

.navigation .user-details {
  display: none;
  position: absolute;
  top: 60px;
  right: 20px;
  background-color: #fff;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
  padding: 10px;
  border-radius: 4px;
  z-index: 1000;
}

.navigation .user-details.active {
  display: block;
}

.navigation .icon {
  color: #fff;
  font-size: 24px;
  cursor: pointer;
  padding: 10px;
  text-align: center;
}

.navigation .icon:hover {
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}

.navigation .sidebar {
  display: none;
  position: absolute;
  top: 50px;
  left: 0;
  width: 200px;
  height: 100%;
  background-color: #333;
  color: #fff;
  padding: 20px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
  z-index: 1000;
}

.navigation .sidebar.active {
  display: block;
}

.navigation .sidebar .item {
  margin-bottom: 10px;
}

.navigation .sidebar .item a {
  color: #fff;
  text-decoration: none;
}

.container {
  max-width: 800px; /* Adjusted max-width */
  margin: 20px auto;
  padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.label {
  font-weight: bold;
  margin-bottom: 5px; /* Reduced margin */
}

.info-box {
  background-color: #f9f9f9;
  border: 1px solid #ccc;
  padding: 10px;
  border-radius: 4px;
  margin-bottom: 10px;
  text-align: left; /* Align text to the left */
}

.info {
  margin-bottom: 5px; /* Reduced margin */
  font-size: 14px; /* Smaller font size */
}

.input-group {
  margin-bottom: 10px;
}

.input-group label {
  display: block;
  margin-bottom: 5px;
  text-align: left;
}

.input-group select,
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

.button-group button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.logout {
  background-color: #f44336;
  color: white;
  padding: 10px 20px;
  text-decoration: none;
  border-radius: 4px;
}

.logout:hover {
  background-color: #d32f2f;
}

.footer {
  margin-top: 20px;
  font-size: 12px; /* Smaller font size */
  color: #666;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

table, th, td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

th {
  background-color: #f2f2f2;
}

/* Modal Styles */
.modal {
  display: none; /* Hidden by default */
  position: fixed; /* Stay in place */
  z-index: 1000; /* Sit on top */
  left: 0;
  top: 0;
  width: 100%; /* Full width */
  height: 100%; /* Full height */
  overflow: auto; /* Enable scroll if needed */
  background-color: rgb(0,0,0); /* Fallback color */
  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

.modal-content {
  background-color: #fefefe;
  margin: 15% auto; /* 15% from the top and centered */
  padding: 20px;
  border: 1px solid #888;
  width: 80%; /* Could be more or less, depending on screen size */
}

.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

</style>
</head>
<body>
<div class='navigation'>
  <div class='icon' onclick='toggleSidebar()'><i class='fas fa-bars'></i></div>
  <div class='logo'>Fake Numbers</div>
  <a href='logout.php' class='logout'>Logout</a>
</div>

<div class='sidebar' id='sidebar'>
  <!-- Sidebar content -->
  <div class='item' id='addFundItem'><a href='#'>Add Fund</a></div>
  <div class='item' id='contactAdminItem'><a href='#'>Contact Admin</a></div>
</div>

<div class='container'>
  <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

  <div class='info-box'>
    <div class='label'>Username:</div>
    <div class='info'><?php echo htmlspecialchars($username); ?></div>
  </div>
  
  <div class='info-box'>
    <div class='label'>Current Balance:</div>
    <div class='info'><?php echo $balance; ?> PKR</div>
  </div>
</div>

<div class='container'>
  <form id='purchaseForm' method='post' action=''>
    <div class='info-box'>
      <div class='input-group'>
        <label for='network'>Select Your Option:</label>
        <select id='network' name='network' onchange='validateForm()'>
          <option value=''>Choose option</option>
          <?php
          foreach ($optionPrices as $option => $price) {
              echo "<option value='" . htmlspecialchars($option) . "'>" . htmlspecialchars($option) . " - Rs {$price}</option>";
          }
          ?>
        </select>
      </div>
  
      <div class='input-group'>
        <label for='phoneNumber'>CNIC Phone Number:</label>
        <input type='text' id='phoneNumber' name='phoneNumber' onchange='validateForm()'>
      </div>
  
      <div class='button-group'>
        <button type='submit' name='purchase' id='purchaseButton' disabled>Purchase Now</button>
      </div>
    </div>
  </form>
</div>

<div class='container'>
  <div class='info-box'>
    <h3>Pending Requests</h3>
    <?php displayPendingRequests($pendingRequests); ?>
  </div>
</div>

<div class='footer'>PHP User Management | &copy; 2024 YourCompany</div>

<script src='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js'></script> <!-- Font Awesome script -->
<script>
function toggleSidebar() {
  var sidebar = document.getElementById('sidebar');
  sidebar.classList.toggle('active');
}

function validateForm() {
  var phoneNumber = document.getElementById('phoneNumber').value;
  var network = document.getElementById('network').value;
  var purchaseButton = document.getElementById('purchaseButton');
  
  if (phoneNumber !== '' && network !== '') {
    purchaseButton.disabled = false;
  } else {
    purchaseButton.disabled = true;
  }
}

function viewDetails(picture, link) {
  var modal = document.createElement('div');
  modal.classList.add('modal');
  
  var modalContent = `
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <img src="${picture}" alt="Picture" style="max-width: 100%; max-height: 400px;">
      <p><a href="${link}" target="_blank">View Link</a></p>
    </div>
  `;
  
  modal.innerHTML = modalContent;
  document.body.appendChild(modal);
  modal.style.display = 'block';
  
  // Close the modal if clicked outside of the modal content
  window.onclick = function(event) {
    if (event.target == modal) {
      closeModal();
    }
  }
}

function closeModal() {
  var modal = document.querySelector('.modal');
  modal.style.display = 'none';
  modal.remove();
}
</script>

</body>
</html>
