<?php
session_start();
include("db.php");

// Security: Only hospital users can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Hospital') {
    header("Location: hospital_login.php");
    exit();
}

// Step 1: Get hospital_id from hospitals table based on logged-in user
$uid = intval($_SESSION['user_id']);
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM users WHERE user_id=$uid"));
$username = $user['username'];

// Find hospital_id where hospital name matches username
$hosp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT hospital_id FROM hospitals WHERE name='$username'"));

if ($hosp) {
    $hospital_id = $hosp['hospital_id'];
} else {
    die("<h2 style='color:red;text-align:center'>⚠ Hospital account not linked! Please ask admin to register hospital properly.</h2>");
}

// Step 2: Fetch all requests for this hospital
$sql = "SELECT r.request_id, r.blood_group, r.quantity, r.status, r.request_date, 
               p.name AS patient_name, p.age, p.gender, p.contact
        FROM requests r
        JOIN patients p ON r.patient_id = p.patient_id
        WHERE r.hospital_id = $hospital_id
        ORDER BY r.request_date DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Blood Requests</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 0; }
    header { background: #c82333; color: white; text-align: center; padding: 20px; }
    header h1 { margin: 0; }
    .container { width: 95%; margin: 30px auto; }
    .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0px 4px 12px rgba(0,0,0,0.1); }
    h2 { color: #c82333; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0px 3px 8px rgba(0,0,0,0.05); }
    table th, table td { padding: 12px; text-align: center; border: 1px solid #ddd; }
    table th { background: #c82333; color: white; }
    .status { padding: 6px 12px; border-radius: 6px; font-weight: bold; display: inline-block; }
    .status-pending { background: orange; color: white; }
    .status-fulfilled { background: green; color: white; }
    .status-denied { background: red; color: white; }
    a.back { display: inline-block; margin-bottom: 15px; text-decoration: none; color: white; background: #007bff; padding: 10px 15px; border-radius: 6px; }
    a.back:hover { background: #0056b3; }
  </style>
</head>
<body>
  <header>
    <h1>🏥 My Blood Requests</h1>
  </header>

  <div class="container">
    <a class="back" href="hospital_dashboard.php">⬅ Back to Dashboard</a>

    <div class="card">
      <h2>Blood Requests Status</h2>
      <table>
        <tr>
          <th>ID</th>
          <th>Patient</th>
          <th>Age</th>
          <th>Gender</th>
          <th>Contact</th>
          <th>Blood Group</th>
          <th>Quantity</th>
          <th>Date</th>
          <th>Status</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
          <td><?php echo $row['request_id']; ?></td>
          <td><?php echo $row['patient_name']; ?></td>
          <td><?php echo $row['age']; ?></td>
          <td><?php echo $row['gender']; ?></td>
          <td><?php echo $row['contact']; ?></td>
          <td><?php echo $row['blood_group']; ?></td>
          <td><?php echo $row['quantity']; ?></td>
          <td><?php echo $row['request_date']; ?></td>
          <td>
            <span class="status status-<?php echo strtolower($row['status']); ?>">
              <?php echo ucfirst($row['status']); ?>
            </span>
          </td>
        </tr>
        <?php } ?>
      </table>
    </div>
  </div>
</body>
</html>
