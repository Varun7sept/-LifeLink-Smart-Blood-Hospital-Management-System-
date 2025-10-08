<?php
session_start();
include("db.php");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Extra fields if role is Hospital
    $hospital_name = isset($_POST['hospital_name']) ? mysqli_real_escape_string($conn, $_POST['hospital_name']) : NULL;
    $location = isset($_POST['location']) ? mysqli_real_escape_string($conn, $_POST['location']) : NULL;

    $sql = "INSERT INTO queries (name, email, phone, role, message, hospital_name, location) 
            VALUES ('$name', '$email', '$phone', '$role', '$message', '$hospital_name', '$location')";
    if (mysqli_query($conn, $sql)) {
        $success = "✅ Your query has been submitted successfully!";
    } else {
        $error = "❌ Something went wrong. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Us - Blood Bank</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f8f9fa; margin:0; }
        header { background:#c82333; color:white; padding:20px; text-align:center; font-size:24px; font-weight:bold; }
        .container { width:50%; margin:30px auto; background:white; padding:30px; border-radius:12px;
                     box-shadow:0 4px 12px rgba(0,0,0,0.1); }
        h2 { color:#c82333; margin-bottom:20px; text-align:center; }
        label { display:block; margin-top:15px; font-weight:bold; }
        input, select, textarea { width:100%; padding:10px; margin-top:5px; border:1px solid #ccc;
                                  border-radius:6px; font-size:15px; }
        textarea { resize:none; height:100px; }
        button { margin-top:20px; width:100%; padding:12px; background:#c82333; color:white; 
                 border:none; border-radius:6px; font-size:16px; cursor:pointer; transition:0.3s; }
        button:hover { background:#a71d2a; }
        .alert { padding:12px; border-radius:6px; margin-bottom:20px; text-align:center; }
        .success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .error { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
        .hidden { display:none; }
    </style>
</head>
<body>
<header>📞 Contact Us</header>

<div class="container">
    <h2>Send Us Your Query</h2>

    <?php if(isset($success)) echo "<div class='alert success'>$success</div>"; ?>
    <?php if(isset($error)) echo "<div class='alert error'>$error</div>"; ?>

    <form method="POST" onsubmit="return validateForm()">
        <label>Full Name</label>
        <input type="text" name="name" id="name" required>

        <label>Email</label>
        <input type="email" name="email" id="email" required>

        <label>Phone</label>
        <input type="text" name="phone" id="phone" pattern="[0-9]{10}" placeholder="10-digit number">

        <label>Role</label>
        <select name="role" id="role" onchange="showRoleFields()" required>
            <option value="">-- Select --</option>
            <option>Donor</option>
            <option>Hospital</option>
            <option>Patient</option>
            <option>BloodBank</option>
            <option>Other</option>
        </select>

        <!-- Extra fields for Hospital -->
        <div id="hospitalFields" class="hidden">
            <label>Hospital Name (⚠️ This will be your Username)</label>
            <input type="text" name="hospital_name">

            <label>Hospital Location</label>
            <input type="text" name="location">
        </div>

        <label>Message / Query</label>
        <textarea name="message" id="message" required></textarea>

        <button type="submit">📤 Submit Query</button>
    </form>
</div>

<script>
function validateForm(){
    let phone = document.getElementById("phone").value;
    if(phone && !/^[0-9]{10}$/.test(phone)){
        alert("⚠️ Phone number must be 10 digits!");
        return false;
    }
    return true;
}

function showRoleFields(){
    let role = document.getElementById("role").value;
    document.getElementById("hospitalFields").style.display = (role === "Hospital") ? "block" : "none";
}
</script>
</body>
</html>
