<?php
session_start();
include("db.php");

// Security: Only hospital users can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Hospital') {
    header("Location: hospital_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Hospital Dashboard</title>
  <style>
    body { font-family: Arial, sans-serif; }
    .container { width: 60%; margin: 50px auto; text-align: center; }
    h1 { color: #333; }
    .menu { margin-top: 30px; }
    a.btn {
      display: inline-block;
      margin: 10px;
      padding: 12px 20px;
      background: #007BFF;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-size: 16px;
    }
    a.btn:hover { background: #0056b3; }
    a.logout { background: red; }
  </style>
</head>
<body>
  <div class="container">
    <h1>🏥 Hospital Dashboard</h1>
    <p>Welcome, <?php echo $_SESSION['role']; ?> user!</p>

    <div class="menu">
      <a class="btn" href="request_blood.php">➕ Request Blood</a>
      <a class="btn" href="hospital_requests.php">📋 View My Requests</a>
      <br><br>
      <a class="btn logout" href="logout.php">🚪 Logout</a>
    </div>
  </div>
</body>
</html>
