<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Blood Inventory Management System</title>
  <style>
    body { margin:0; font-family: Arial, sans-serif; background:#f8f9fa; }
    header { background: #c82333; color: white; padding: 20px; text-align: center; }
    header h1 { margin: 0; font-size: 2.5em; }
    header p { margin: 5px 0; font-size: 18px; }

    .hero { background: url('https://img.freepik.com/free-photo/medical-banner-with-red-blood-cells_23-2149611219.jpg') no-repeat center center/cover; color:white; padding:80px 20px; text-align:center; }
    .hero h2 { font-size: 36px; margin-bottom: 10px; }
    .hero p { font-size: 20px; }

    .container { width: 90%; margin: auto; padding: 30px 0; }
    .facts { display: flex; justify-content: space-around; margin: 30px 0; flex-wrap: wrap; }
    .fact { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 6px rgba(0,0,0,0.1); width: 28%; min-width:200px; margin:10px; }
    .fact h3 { color: #c82333; font-size: 28px; margin: 10px 0; }

    .compatibility { background:#fff3f3; padding:20px; border-radius:10px; margin-top:20px; }
    .compatibility h2 { text-align:center; color:#c82333; }
    table { width:100%; border-collapse: collapse; margin-top:15px; }
    table, th, td { border:1px solid #ccc; }
    th, td { padding:10px; text-align:center; }
    th { background:#c82333; color:white; }

    .cta { text-align:center; margin:40px 0; }
    .btn { display:inline-block; margin:10px; padding:15px 25px; background:#c82333; color:white; text-decoration:none; border-radius:6px; font-size:18px; transition:0.3s; }
    .btn:hover { background:#a71d2a; }

    footer { background:#343a40; color:white; text-align:center; padding:15px; margin-top:30px; }
  </style>
</head>
<body>

<header>
  <h1>🩸 Blood Inventory Management System</h1>
  <p>Saving Lives Through Smart Blood Management</p>
</header>

<div class="hero">
  <h2>Donate Blood, Save Lives</h2>
  <p>Every 2 seconds, someone needs blood. Your contribution matters!</p>
</div>

<div class="container">
  <!-- Facts Section -->
  <div class="facts">
    <div class="fact">
      <h3>1</h3>
      <p>Donation can save up to 3 lives</p>
    </div>
    <div class="fact">
      <h3>120M+</h3>
      <p>Blood donations collected worldwide each year</p>
    </div>
    <div class="fact">
      <h3>365</h3>
      <p>Blood is needed every day, not just in emergencies</p>
    </div>
  </div>

  <!-- Compatibility Chart -->
  <div class="compatibility">
    <h2>Blood Compatibility Chart</h2>
    <table>
      <tr>
        <th>Blood Group</th>
        <th>Can Donate To</th>
        <th>Can Receive From</th>
      </tr>
      <tr><td>O-</td><td>All Blood Groups</td><td>O-</td></tr>
      <tr><td>O+</td><td>O+, A+, B+, AB+</td><td>O+, O-</td></tr>
      <tr><td>A-</td><td>A-, A+, AB-, AB+</td><td>A-, O-</td></tr>
      <tr><td>A+</td><td>A+, AB+</td><td>A+, A-, O+, O-</td></tr>
      <tr><td>B-</td><td>B-, B+, AB-, AB+</td><td>B-, O-</td></tr>
      <tr><td>B+</td><td>B+, AB+</td><td>B+, B-, O+, O-</td></tr>
      <tr><td>AB-</td><td>AB-, AB+</td><td>AB-, A-, B-, O-</td></tr>
      <tr><td>AB+</td><td>AB+</td><td>All Blood Groups</td></tr>
    </table>
  </div>

  <!-- Call to Actions -->
  <div class="cta">
    <h2>Get Started</h2>
    <a href="admin_login.php" class="btn">🔑 Admin Login</a>
    <a href="donor_register.php" class="btn">📝 Register as Donor</a>
    <a href="hospital_login.php" class="btn">🏥 Request Blood (Hospital)</a>
    <a href="contact.php" class="btn">📞 Contact Us</a> <!-- ✅ Added Contact Button -->
  </div>
</div>

<footer>
  <p>© <?php echo date("Y"); ?> Blood Inventory Management System | Developed for Life-Saving Management</p>
</footer>

</body>
</html>
