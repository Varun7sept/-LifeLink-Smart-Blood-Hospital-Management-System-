<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
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
    header h1 { margin: 0; font-size: 2.2em; }
    header p { margin: 5px 0; font-size: 18px; }

    .container {
      width: 80%;
      margin: 40px auto;
      text-align: center;
    }
    .menu {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }
    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0px 6px 15px rgba(0,0,0,0.15);
    }
    .card a {
      text-decoration: none;
      display: block;
      color: white;
      background: #c82333;
      padding: 12px;
      border-radius: 6px;
      font-size: 16px;
      margin-top: 10px;
    }
    .card a:hover { background: #a71d2a; }
    footer {
      margin-top: 50px;
      background: #343a40;
      color: white;
      text-align: center;
      padding: 15px;
    }
  </style>
</head>
<body>
  <header>
    <h1>🩸 Admin Dashboard</h1>
    <p>Welcome, <b><?php echo $_SESSION['role']; ?></b>!</p>
  </header>

  <div class="container">
    <h2>Manage the Blood Bank System</h2>
    <div class="menu">
      <div class="card">
        <h3>👥 Donors</h3>
        <p>View and manage registered donors</p>
        <a href="manage_donors.php">Manage Donors</a>
      </div>
      <div class="card">
        <h3>🏥 Hospitals</h3>
        <p>View, approve, and manage hospitals</p>
        <a href="manage_hospitals.php">Manage Hospitals</a>
      </div>
      <div class="card">
        <h3>🏦 Blood Banks</h3>
        <p>Manage all blood bank branches and stock</p>
        <a href="manage_bloodbanks.php">Manage Blood Banks</a>
      </div>
      <div class="card">
        <h3>📦 Inventory</h3>
        <p>Track and manage blood stock availability</p>
        <a href="manage_inventory.php">Manage Inventory</a>
      </div>
      <div class="card">
        <h3>📋 Requests</h3>
        <p>Approve or deny blood requests</p>
        <a href="manage_requests.php">Manage Requests</a>
      </div>
      <div class="card">
        <h3>📊 Reports</h3>
        <p>View analytics and summary reports</p>
        <a href="inventory_report.php">View Reports</a>
      </div>
      <div class="card">
        <h3>📞 Contact</h3>
        <p>View and respond to contact queries</p>
        <a href="queries.php">Contact Queries</a>
      </div>
      <div class="card">
        <h3>🚪 Logout</h3>
        <p>Exit from admin session</p>
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </div>

  <footer>
    <p>© <?php echo date("Y"); ?> Blood Inventory Management System | Admin Panel</p>
  </footer>
</body>
</html>
