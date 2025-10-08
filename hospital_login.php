<?php
session_start();
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ✅ Case-insensitive role check
    $sql = "SELECT * FROM users WHERE username='$username' AND LOWER(role)='hospital'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['role'] = $row['role']; // stores "Hospital" from DB
        header("Location: hospital_dashboard.php");
        exit();
    } else {
        $error = "Invalid Hospital Credentials!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Hospital Login</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f9f9f9; }
    .login-box { width: 350px; margin: 80px auto; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0px 3px 8px rgba(0,0,0,0.2); text-align:center; }
    h2 { color: #007bff; margin-bottom: 20px; }
    input { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
    button { width: 95%; padding: 10px; background: #007bff; border: none; color: white; border-radius: 5px; font-size: 16px; }
    button:hover { background: #0056b3; cursor: pointer; }
    .error { color: red; margin-top: 10px; }
    a { text-decoration: none; color: #c82333; }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>🏥 Hospital Login</h2>
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
