<?php
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $blood_group = $_POST['blood_group'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $bloodbank_id = $_POST['bloodbank_id'];
    $last_donation = $_POST['last_donation'];
    $quantity = $_POST['quantity'];

    // Auto calculate expiry (42 days after donation)
    $expiry_date = date('Y-m-d', strtotime($last_donation . ' +42 days'));

    $today = date("Y-m-d");

    // Validation: last donation cannot be in the future
    if ($last_donation > $today) {
        echo "<script>alert('Error: Last donation date cannot be in the future.'); window.location='donor_register.php';</script>";
        exit();
    }

    // Insert donor profile
    $sql = "INSERT INTO donors (name, age, gender, blood_group, contact, email, last_donation) 
            VALUES ('$name', $age, '$gender', '$blood_group', '$contact', '$email', '$last_donation')";
    mysqli_query($conn, $sql);

    $donor_id = mysqli_insert_id($conn);

    // Insert valid blood units into inventory
    for ($i = 1; $i <= $quantity; $i++) {
        $sql2 = "INSERT INTO blood_inventory (blood_group, donor_id, bloodbank_id, collection_date, expiry_date, status) 
                 VALUES ('$blood_group', $donor_id, $bloodbank_id, '$last_donation', '$expiry_date', 'available')";
        mysqli_query($conn, $sql2);
    }

    echo "<script>alert('Registration successful! Expiry auto-calculated as $expiry_date. Blood units recorded in inventory.'); window.location='index.php';</script>";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Donor Registration</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f2f2f2; }
    .form-box { width: 400px; margin: 30px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; background: #f9f9f9; box-shadow: 0px 2px 6px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #333; }
    label { display: block; margin-top: 10px; font-weight: bold; }
    input, select { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; }
    button { margin-top: 15px; padding: 10px; width: 100%; background: green; color: white; border: none; border-radius: 5px; font-size: 16px; }
    button:hover { background: darkgreen; cursor: pointer; }
    .note { font-size: 12px; color: #555; }
  </style>
</head>
<body>
  <div class="form-box">
    <h2>🩸 Donor Registration</h2>
    <form method="POST">
      <label>Name:</label><input type="text" name="name" required>
      <label>Age:</label><input type="number" name="age" min="18" required>
      <label>Gender:</label>
      <select name="gender" required>
        <option value="">--Select--</option>
        <option>Male</option>
        <option>Female</option>
        <option>Other</option>
      </select>
      <label>Blood Group:</label>
      <select name="blood_group" required>
        <option value="">--Select--</option>
        <option>O-</option><option>O+</option>
        <option>A-</option><option>A+</option>
        <option>B-</option><option>B+</option>
        <option>AB-</option><option>AB+</option>
      </select>
      <label>Contact:</label><input type="text" name="contact" required>
      <label>Email:</label><input type="email" name="email" required>

      <label>Blood Bank:</label>
      <select name="bloodbank_id" required>
        <?php
        $bbanks = mysqli_query($conn, "SELECT * FROM bloodbanks");
        while($bb = mysqli_fetch_assoc($bbanks)) {
          echo "<option value='".$bb['bloodbank_id']."'>".$bb['name']."</option>";
        }
        ?>
      </select>

      <label>Last Donation Date:</label>
      <input type="date" name="last_donation" required max="<?php echo date('Y-m-d'); ?>">
      <p class="note">Expiry will be auto-calculated as 42 days from donation date.</p>

      <label>Quantity (Units):</label>
      <input type="number" name="quantity" min="1" required>

      <button type="submit">Register</button>
    </form>
  </div>
</body>
</html>
