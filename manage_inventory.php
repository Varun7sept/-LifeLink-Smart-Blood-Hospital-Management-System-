<?php
session_start();
include("db.php");

// Security: Only admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Auto update expiry
$today = date('Y-m-d');
mysqli_query($conn, "UPDATE blood_inventory 
                     SET status='expired' 
                     WHERE DATE(expiry_date) < '$today' 
                     AND LOWER(status)='available'");

// Delete inventory entry
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM blood_inventory WHERE blood_id=$id");
    echo "<script>alert('Inventory entry deleted successfully!'); window.location='manage_inventory.php';</script>";
}

// Fetch inventory list with donor & blood bank details
$sql = "SELECT b.blood_id, UPPER(TRIM(b.blood_group)) AS blood_group, 
               b.collection_date, DATE(b.expiry_date) as expiry_date, LOWER(b.status) AS status,
               d.name AS donor_name, d.contact, 
               bb.name AS bloodbank_name
        FROM blood_inventory b
        LEFT JOIN donors d ON b.donor_id = d.donor_id
        LEFT JOIN bloodbanks bb ON b.bloodbank_id = bb.bloodbank_id
        ORDER BY b.expiry_date ASC";
$inventory = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Inventory</title>
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
      width: 95%;
      margin: 30px auto;
    }

    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
      margin-bottom: 30px;
    }

    h2 { color: #c82333; margin-bottom: 15px; }

    table {
      width: 100%;
      border-collapse: collapse;
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
    .status-available { color: green; font-weight: bold; }
    .status-used { color: blue; font-weight: bold; }
    .status-expired { color: red; font-weight: bold; }
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
    <h1>🩸 Manage Blood Inventory</h1>
  </header>

  <div class="container">
    <a class="back" href="admin_dashboard.php">⬅ Back to Dashboard</a>

    <div class="card">
      <h2>Blood Inventory List</h2>
      <table>
        <tr>
          <th>ID</th>
          <th>Blood Group</th>
          <th>Donor</th>
          <th>Contact</th>
          <th>Blood Bank</th>
          <th>Collection Date</th>
          <th>Expiry Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($inventory)){ ?>
        <tr>
          <td><?php echo $row['blood_id']; ?></td>
          <td><?php echo $row['blood_group']; ?></td>
          <td><?php echo $row['donor_name']; ?></td>
          <td><?php echo $row['contact']; ?></td>
          <td><?php echo $row['bloodbank_name']; ?></td>
          <td><?php echo $row['collection_date']; ?></td>
          <td><?php echo $row['expiry_date']; ?></td>
          <td class="status-<?php echo strtolower($row['status']); ?>"><?php echo ucfirst($row['status']); ?></td>
          <td>
            <a class="delete" href="manage_inventory.php?delete_id=<?php echo $row['blood_id']; ?>" onclick="return confirm('Delete this blood unit?')">Delete</a>
          </td>
        </tr>
        <?php } ?>
      </table>
    </div>
  </div>
</body>
</html>
