<?php
session_start();
include("db.php");

// ✅ Ensure hospital is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Hospital') {
    header("Location: hospital_login.php");
    exit();
}

// Get hospital details from users table
$login_user_id = $_SESSION['user_id'];
$res_user = mysqli_query($conn, "SELECT username FROM users WHERE user_id = '$login_user_id' LIMIT 1");
$user = mysqli_fetch_assoc($res_user);

if (!$user) {
    die("❌ User not found.");
}

// Now map username to hospital in hospitals table
$res_hospital = mysqli_query($conn, "SELECT hospital_id, name FROM hospitals WHERE name = '{$user['username']}' LIMIT 1");
$hospital = mysqli_fetch_assoc($res_hospital);

if (!$hospital) {
    die("❌ Hospital record not found. Please ask admin to add this hospital in hospitals table.");
}

$hospital_id = $hospital['hospital_id'];
$hospital_name = $hospital['name'];

// ✅ Handle Request Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['make_request'])) {
    $patient_name = $_POST['patient_name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $blood_group = strtoupper(trim($_POST['blood_group'])); // normalize format
    $contact = $_POST['contact'];
    $quantity = $_POST['quantity'];
    $request_date = date("Y-m-d");

    // Insert patient
    $sql_patient = "INSERT INTO patients (name, age, gender, blood_group, contact, hospital_id) 
                    VALUES ('$patient_name','$age','$gender','$blood_group','$contact','$hospital_id')";
    mysqli_query($conn, $sql_patient) or die("❌ Error inserting patient: " . mysqli_error($conn));
    $patient_id = mysqli_insert_id($conn);

    // Insert request
    $sql_request = "INSERT INTO requests (patient_id, hospital_id, blood_group, quantity, request_date, status) 
                    VALUES ('$patient_id','$hospital_id','$blood_group','$quantity','$request_date','pending')";
    mysqli_query($conn, $sql_request) or die("❌ Error inserting request: " . mysqli_error($conn));

    echo "<script>alert('Blood request submitted successfully!'); window.location='request_blood.php';</script>";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Request Blood</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f9fa;
      margin: 0;
      padding: 0;
    }
    header {
      background: #c82333;
      color: white;
      text-align: center;
      padding: 20px;
    }
    header h1 {
      margin: 0;
    }
    .container {
      max-width: 600px;
      margin: 40px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      color: #c82333;
      margin-bottom: 20px;
    }
    label {
      font-weight: bold;
      display: block;
      margin-bottom: 6px;
      color: #333;
    }
    input, select {
      width: 100%;
      padding: 10px;
      margin-bottom: 18px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 14px;
    }
    input:focus, select:focus {
      border-color: #c82333;
      outline: none;
      box-shadow: 0 0 5px rgba(200,35,51,0.5);
    }
    button {
      width: 100%;
      padding: 12px;
      background: #c82333;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    button:hover {
      background: #a71d2a;
    }
    .back {
      display: inline-block;
      margin: 15px 0;
      padding: 10px 15px;
      background: #007bff;
      color: white;
      text-decoration: none;
      border-radius: 6px;
    }
    .back:hover {
      background: #0056b3;
    }
    .readonly-box {
      background: #eee;
      padding: 10px;
      border-radius: 6px;
      font-weight: bold;
      margin-bottom: 15px;
      border: 1px solid #ccc;
    }
  </style>
</head>
<body>
  <header>
    <h1>🩸 Request Blood</h1>
  </header>

  <div class="container">
    <a class="back" href="hospital_dashboard.php">⬅ Back to Dashboard</a>
    <h2>Blood Request Form</h2>

    <!-- ✅ Show Hospital Name -->
    <div class="readonly-box">Hospital: <?php echo htmlspecialchars($hospital_name); ?></div>

    <form method="POST">
      <input type="hidden" name="make_request" value="1">

      <label>Patient Name</label>
      <input type="text" name="patient_name" required>

      <label>Age</label>
      <input type="number" name="age" required>

      <label>Gender</label>
      <select name="gender">
        <option>Male</option>
        <option>Female</option>
        <option>Other</option>
      </select>

      <label>Blood Group</label>
      <select name="blood_group" required>
        <option value="">-- Select Group --</option>
        <option>O-</option>
        <option>O+</option>
        <option>A-</option>
        <option>A+</option>
        <option>B-</option>
        <option>B+</option>
        <option>AB-</option>
        <option>AB+</option>
      </select>

      <label>Contact</label>
      <input type="text" name="contact" required>

      <label>Quantity (units)</label>
      <input type="number" name="quantity" required>

      <button type="submit">Submit Request</button>
    </form>
  </div>
</body>
</html>
