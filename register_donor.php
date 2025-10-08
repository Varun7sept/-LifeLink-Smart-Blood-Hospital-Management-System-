<?php
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $blood_group = $_POST['blood_group'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];

    $sql = "INSERT INTO donors (name, age, gender, blood_group, contact, email) 
            VALUES ('$name','$age','$gender','$blood_group','$contact','$email')";
    if (mysqli_query($conn, $sql)) {
        echo "Donor registered successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Register Donor</title></head>
<body>
  <h2>Donor Registration</h2>
  <form method="POST">
    Name: <input type="text" name="name" required><br>
    Age: <input type="number" name="age" required><br>
    Gender: 
    <select name="gender">
      <option>Male</option><option>Female</option><option>Other</option>
    </select><br>
    Blood Group: <input type="text" name="blood_group" required><br>
    Contact: <input type="text" name="contact" required><br>
    Email: <input type="email" name="email" required><br>
    <button type="submit">Register</button>
  </form>
</body>
</html>

