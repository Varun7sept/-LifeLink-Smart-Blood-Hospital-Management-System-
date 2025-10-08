<?php
session_start();
include("db.php");

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Delete donor
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM blood_inventory WHERE donor_id=$id");
    mysqli_query($conn, "DELETE FROM donors WHERE donor_id=$id");
    echo "<script>alert('Donor deleted successfully!'); window.location='manage_donors.php';</script>";
}

// Add donor
if (isset($_POST['add_donor'])) {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $blood_group = $_POST['blood_group'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $bloodbank_id = $_POST['bloodbank_id'];
    $last_donation = $_POST['last_donation'];
    $quantity = $_POST['quantity'];

    // calculate expiry date (42 days after donation)
    $expiry_date = date('Y-m-d', strtotime($last_donation . ' +42 days'));

    // Insert donor
    mysqli_query($conn, "INSERT INTO donors (name, age, gender, blood_group, contact, email, last_donation)
                         VALUES ('$name', $age, '$gender', '$blood_group', '$contact', '$email', '$last_donation')");
    $donor_id = mysqli_insert_id($conn);

    // Insert blood units into inventory
    for ($i=1; $i<=$quantity; $i++) {
        mysqli_query($conn, "INSERT INTO blood_inventory (blood_group, donor_id, bloodbank_id, collection_date, expiry_date, status)
                             VALUES ('$blood_group', $donor_id, $bloodbank_id, '$last_donation', '$expiry_date', 'available')");
    }

    echo "<script>alert('Donor added successfully with expiry auto-calculated (42 days).'); window.location='manage_donors.php';</script>";
}

// Fetch donor list
$donors = mysqli_query($conn, "SELECT * FROM donors");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Donors</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background: #f8f9fa;
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
      width: 90%;
      margin: 30px auto;
    }

    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
      margin-bottom: 30px;
    }

    h2 { color: #c82333; }

    form label {
      display: block;
      margin: 10px 0 5px;
      font-weight: bold;
    }
    input, select, button {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      background: #c82333;
      color: white;
      font-size: 16px;
      border: none;
      cursor: pointer;
      transition: background 0.2s;
    }
    button:hover { background: #a71d2a; }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
      background: white;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
    }
    table th, table td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: center;
    }
    table th {
      background: #c82333;
      color: white;
    }
    a.delete {
      color: white;
      background: red;
      padding: 6px 10px;
      border-radius: 5px;
      text-decoration: none;
    }
    a.delete:hover { background: darkred; }
    a.back {
      display: inline-block;
      margin: 15px 0;
      text-decoration: none;
      color: white;
      background: #007bff;
      padding: 10px 15px;
      border-radius: 5px;
    }
    a.back:hover { background: #0056b3; }
  </style>
</head>
<body>
  <header>
    <h1>🩸 Manage Donors</h1>
  </header>

  <div class="container">

    <a class="back" href="admin_dashboard.php">⬅ Back to Dashboard</a>

    <div class="card">
      <h2>Add New Donor</h2>
      <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" required>
        <label>Age:</label>
        <input type="number" name="age" required>
        <label>Gender:</label>
        <select name="gender" required>
          <option value="">--Select--</option>
          <option>Male</option><option>Female</option><option>Other</option>
        </select>
        <label>Blood Group:</label>
        <select name="blood_group" required>
          <option value="">--Select--</option>
          <option>O-</option><option>O+</option><option>A-</option><option>A+</option>
          <option>B-</option><option>B+</option><option>AB-</option><option>AB+</option>
        </select>
        <label>Contact:</label>
        <input type="text" name="contact" required>
        <label>Email:</label>
        <input type="email" name="email" required>
        <label>Blood Bank:</label>
        <select name="bloodbank_id" required>
          <?php
            $bbanks = mysqli_query($conn, "SELECT * FROM bloodbanks");
            while($bb = mysqli_fetch_assoc($bbanks)){
                echo "<option value='".$bb['bloodbank_id']."'>".$bb['name']."</option>";
            }
          ?>
        </select>
        <label>Last Donation Date:</label>
        <input type="date" name="last_donation" required>
        <label>Quantity (Units):</label>
        <input type="number" name="quantity" min="1" required>
        <button type="submit" name="add_donor">➕ Add Donor</button>
      </form>
    </div>

    <div class="card">
      <h2>Registered Donors</h2>
      <table>
        <tr>
          <th>ID</th><th>Name</th><th>Age</th><th>Gender</th>
          <th>Blood Group</th><th>Contact</th><th>Email</th>
          <th>Last Donation</th><th>Action</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($donors)){ ?>
        <tr>
          <td><?php echo $row['donor_id']; ?></td>
          <td><?php echo $row['name']; ?></td>
          <td><?php echo $row['age']; ?></td>
          <td><?php echo $row['gender']; ?></td>
          <td><?php echo $row['blood_group']; ?></td>
          <td><?php echo $row['contact']; ?></td>
          <td><?php echo $row['email']; ?></td>
          <td><?php echo $row['last_donation']; ?></td>
          <td>
            <a class="delete" href="manage_donors.php?delete_id=<?php echo $row['donor_id']; ?>" onclick="return confirm('Are you sure you want to delete this donor?')">Delete</a>
          </td>
        </tr>
        <?php } ?>
      </table>
    </div>

  </div>
</body>
</html>
