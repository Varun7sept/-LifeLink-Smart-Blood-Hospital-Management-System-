<?php
session_start();
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' AND role='admin'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row && $password == $row['password']) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['role'] = $row['role'];
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid Admin Credentials!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Login</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f2f2f2; }
    .login-box { width: 350px; margin: 80px auto; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0px 3px 8px rgba(0,0,0,0.2); text-align:center; }
    h2 { color: #c82333; margin-bottom: 20px; }
    input { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
    button { width: 95%; padding: 10px; background: #c82333; border: none; color: white; border-radius: 5px; font-size: 16px; }
    button:hover { background: #a71d2a; cursor: pointer; }
    .error { color: red; margin-top: 10px; }
    a { text-decoration: none; color: #007bff; }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>🔑 Admin Login</h2>
    <form method="POST">
      <input type="text" name="username" placeholder="Enter Username" required><br>
      <input type="password" name="password" placeholder="Enter Password" required><br>
      <button type="submit">Login</button>
    </form>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <p><a href="index.php">⬅ Back to Home</a></p>
  </div>
</body>
</html>
