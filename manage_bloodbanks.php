<?php
session_start();
include("db.php");

// Security: Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Handle Delete Action
if (isset($_GET['delete'])) {
    $bloodbank_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM bloodbanks WHERE bloodbank_id=$bloodbank_id");
    header("Location: manage_bloodbanks.php");
    exit();
}

// Handle Add Blood Bank Action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_bloodbank'])) {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $contact = $_POST['contact'];

    $sql = "INSERT INTO bloodbanks (name, location, contact)
            VALUES ('$name','$location','$contact')";
    mysqli_query($conn, $sql);

    header("Location: manage_bloodbanks.php");
    exit();
}

// Fetch all blood banks
$sql = "SELECT * FROM bloodbanks";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Blood Banks</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f9fa;
      margin: 0;
      color: #333;
    }
    header {
      background: #c82333;
      color: white;
      padding: 20px;
      text-align: center;
    }
    header h1 { margin: 0; font-size: 2em; }

    .container {
      width: 85%;
      margin: 30px auto;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    table th, table td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: center;
    }
    table th {
      background: #c82333;
      color: white;
    }
    table tr:nth-child(even) { background: #f2f2f2; }
    table tr:hover { background: #ffe6e6; }

    a.delete-btn {
      color: white;
      background: red;
      padding: 6px 12px;
      border-radius: 5px;
      text-decoration: none;
      font-size: 14px;
    }
    a.delete-btn:hover { background: darkred; }

    .form-box {
      margin-top: 40px;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 10px;
      background: #fefefe;
      width: 400px;
    }
    .form-box h2 {
      text-align: center;
      margin-bottom: 15px;
      color: #c82333;
    }
    .form-box label {
      display: block;
      margin-top: 10px;
      font-weight: bold;
    }
    .form-box input {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .form-box button {
      width: 100%;
      margin-top: 15px;
      padding: 10px;
      background: #c82333;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }
    .form-box button:hover { background: #a71d2a; }

    .back-btn {
      display: inline-block;
      margin-bottom: 15px;
      background: #6c757d;
      color: white;
      padding: 8px 15px;
      border-radius: 5px;
      text-decoration: none;
    }
    .back-btn:hover { background: #5a6268; }

    footer {
      margin-top: 40px;
      text-align: center;
      padding: 15px;
      background: #343a40;
      color: white;
    }
  </style>
</head>
<body>
  <header>
    <h1>🏦 Manage Blood Banks</h1>
  </header>

  <div class="container">
    <a href="admin_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

    <!-- Blood Banks Table -->
    <h2>Registered Blood Banks</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Location</th>
        <th>Contact</th>
        <th>Action</th>
      </tr>
      <?php while ($row = mysqli_fetch_assoc($result)) { ?>
      <tr>
        <td><?php echo $row['bloodbank_id']; ?></td>
        <td><?php echo $row['name']; ?></td>
        <td><?php echo $row['location']; ?></td>
        <td><?php echo $row['contact']; ?></td>
        <td><a class="delete-btn" href="manage_bloodbanks.php?delete=<?php echo $row['bloodbank_id']; ?>" onclick="return confirm('Are you sure you want to delete this blood bank?');">Delete</a></td>
      </tr>
      <?php } ?>
    </table>

    <!-- Add Blood Bank Form -->
    <div class="form-box">
      <h2>Add New Blood Bank</h2>
      <form method="POST">
        <input type="hidden" name="add_bloodbank" value="1">
        <label>Name:</label>
        <input type="text" name="name" required>
        <label>Location:</label>
        <input type="text" name="location" required>
        <label>Contact:</label>
        <input type="text" name="contact" required>
        <button type="submit">Add Blood Bank</button>
      </form>
    </div>
  </div>

  <footer>
    <p>© <?php echo date("Y"); ?> Blood Inventory Management System</p>
  </footer>
</body>
</html>
