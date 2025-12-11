<?php
// Function to retrieve pending data from file
function getPendingData($file) {
    $pendingData = [];
    if (file_exists($file)) {
        $pendingEntries = file($file, FILE_IGNORE_NEW_LINES);
        foreach ($pendingEntries as $entry) {
            $pendingData[] = json_decode($entry, true);
        }
    }
    return $pendingData;
}

// Function to update pending data in file
function updatePendingData($file, $index, $status, $link, $image) {
    $tempFile = $file . '.tmp';
    $updated = false;
    $pendingData = getPendingData($file);

    if (isset($pendingData[$index])) {
        $pendingData[$index]['status'] = $status;
        $pendingData[$index]['link'] = $link;
        $pendingData[$index]['image'] = $image;
        $updated = true;
    }

    if ($updated) {
        $fp = fopen($tempFile, 'w');
        foreach ($pendingData as $entry) {
            fwrite($fp, json_encode($entry) . PHP_EOL);
        }
        fclose($fp);
        rename($tempFile, $file);
        return true;
    } else {
        return false;
    }
}

// Check if form is submitted for update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['index'])) {
    $index = $_POST['index'];
    $status = $_POST['status'];
    $link = isset($_POST['link']) ? $_POST['link'] : '';
    
    // Handle file upload for image
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image']['name'];
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile);
    }

    // Update pending data in file
    $pendingFile = 'pending_data.txt';
    if (updatePendingData($pendingFile, $index, $status, $link, $image)) {
        // Redirect to refresh admin panel or provide success message
        header('Location: admin_panel.php?status=updated');
        exit;
    } else {
        echo "Failed to update pending data.";
        exit;
    }
}

// Display pending data in the admin panel
$pendingData = getPendingData('pending_data.txt');

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Balance & Log Out Buttons</title>
<style>
  .button-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
  }

  .custom-button {
    background-color: #4CAF50; /* Green */
    border: none;
    color: white;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    font-size: 16px;
    cursor: pointer;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  }

  .custom-button:hover {
    background-color: #45a049; /* Darker Green */
  }
</style>
</head>
<body>

<div class="button-container">
  <button class="custom-button" onclick="redirectToAddBalance()">Add Balance</button>
  <button class="custom-button" onclick="logOut()">Log Out</button>
</div>

<script>
function redirectToAddBalance() {
  // Replace 'https://example.com/add-balance' with your add balance page URL
  window.location.href = 'balance_update.php';
}

function logOut() {
  // Replace 'https://example.com/logout' with your logout page URL
  window.location.href = 'Asad.php';
}
</script>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .edit-form {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        .edit-form label {
            display: block;
            margin-bottom: 10px;
        }
        .edit-form input, .edit-form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .edit-form button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .edit-form button:hover {
            background-color: #45a049;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <h1>Admin Panel</h1>

    <table>
        <tr>
            <th>User</th>
            <th>Option</th>
            <th>Phone Number</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($pendingData as $index => $data): ?>
        <tr>
            <td><?php echo $data['username']; ?></td>
            <td><?php echo $data['option']; ?></td>
            <td><?php echo $data['phoneNumber']; ?></td>
            <td><?php echo $data['status']; ?></td>
            <td>
                <button onclick="editEntry(<?php echo $index; ?>)">Edit</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- Edit Form Popup -->
    <div class="edit-form" id="editForm">
        <span class="close" onclick="closeForm()">&times;</span>
        <h2>Edit Pending Entry</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" id="index" name="index">
            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="pending">Pending</option>
                <option value="activate">Activate</option>
            </select>
            <br>
            <label for="link">Link:</label>
            <input type="text" id="link" name="link">
            <br>
            <label for="image">Image:</label>
            <input type="file" id="image" name="image">
            <br>
            <button type="submit">Update</button>
        </form>
    </div>

    <script>
        function editEntry(index) {
            var editForm = document.getElementById('editForm');
            var statusField = document.getElementById('status');
            var linkField = document.getElementById('link');
            var indexField = document.getElementById('index');

            statusField.value = '<?php echo isset($pendingData[$index]['status']) ? $pendingData[$index]['status'] : 'pending'; ?>';
            linkField.value = '<?php echo isset($pendingData[$index]['link']) ? $pendingData[$index]['link'] : ''; ?>';
            indexField.value = index;

            editForm.style.display = 'block';
        }

        function closeForm() {
            document.getElementById('editForm').style.display = 'none';
        }
    </script>

</body>
</html>
