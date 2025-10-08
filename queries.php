<?php
session_start();
include("db.php");

// Import PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// ✅ Handle credential form submission (for Hospital/BloodBank)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['set_credentials'])) {
    $id = intval($_POST['id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Fetch query details
    $res = mysqli_query($conn, "SELECT * FROM queries WHERE id=$id LIMIT 1");
    $query = mysqli_fetch_assoc($res);

    if ($query) {
        $role = str_replace(' ', '', $query['role']); // normalize role

        if ($role == "Hospital") {
            // ✅ Insert into hospitals table first
            mysqli_query($conn, "INSERT INTO hospitals (name, location, contact, email)
                                 VALUES ('{$query['hospital_name']}', '{$query['location']}', '{$query['phone']}', '{$query['email']}')");
            $hospital_id = mysqli_insert_id($conn);

            // ✅ Insert into users table with hospital_id
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO users (username, password, role, hospital_id) 
                                 VALUES ('$username', '$hashed_pass', '$role', '$hospital_id')");
        } else {
            // Other roles → just insert into users
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO users (username, password, role) 
                                 VALUES ('$username', '$hashed_pass', '$role')");
        }

        // Update query status
        mysqli_query($conn, "UPDATE queries SET status='Approved' WHERE id=$id");

        // ✅ Send credentials via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "rvce2varunbanda@gmail.com"; 
            $mail->Password = "fasacqlsijxpzxrq"; // Gmail App Password
            $mail->SMTPSecure = "tls";
            $mail->Port = 587;

            $mail->setFrom("rvce2varunbanda@gmail.com", "Blood Bank Admin");
            $mail->addAddress($query['email'], $query['name']);

            $mail->isHTML(true);
            $mail->Subject = "Your Login Credentials - Blood Inventory System";
            $mail->Body = "Hello <b>" . htmlspecialchars($query['name']) . "</b>,<br><br>"
                        . "✅ Your request to join as a <b>" . htmlspecialchars($role) . "</b> has been <b>APPROVED</b>.<br><br>"
                        . "Here are your login credentials:<br>"
                        . "<b>Username:</b> $username <br>"
                        . "<b>Password:</b> $password <br><br>"
                        . "Please keep them safe.<br><br>"
                        . "Regards,<br>"
                        . "🩸 <b>Blood Inventory Management System</b>";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }
    }

    header("Location: queries.php");
    exit();
}

// ❌ Handle deny
if (isset($_GET['action']) && $_GET['action'] == 'deny' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    mysqli_query($conn, "UPDATE queries SET status='Denied' WHERE id=$id");

    $res = mysqli_query($conn, "SELECT name, email, role FROM queries WHERE id=$id LIMIT 1");
    $query = mysqli_fetch_assoc($res);

    if ($query) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "rvce2varunbanda@gmail.com"; 
            $mail->Password = "fasacqlsijxpzxrq"; 
            $mail->SMTPSecure = "tls";
            $mail->Port = 587;

            $mail->setFrom("rvce2varunbanda@gmail.com", "Blood Bank Admin");
            $mail->addAddress($query['email'], $query['name']);

            $mail->isHTML(true);
            $mail->Subject = "Your Request Status - Blood Inventory System";
            $mail->Body = "Hello " . htmlspecialchars($query['name']) . ",<br><br>"
                        . "❌ Unfortunately, your request to join as a <b>" . htmlspecialchars($query['role']) . "</b> has been <b>DENIED</b>.<br>"
                        . "If you believe this is a mistake, please contact us again.<br><br>"
                        . "Regards,<br>"
                        . "🩸 <b>Blood Inventory Management System</b>";

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }
    }

    header("Location: queries.php");
    exit();
}

// Fetch all queries
$result = mysqli_query($conn, "SELECT * FROM queries ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin - Queries</title>
  <style>
    body { font-family: Arial; background:#f8f9fa; margin:0; }
    header { background:#c82333; color:white; padding:20px; text-align:center; font-size:22px; }
    .container { width:95%; margin:20px auto; }
    table { width:100%; border-collapse:collapse; margin-top:20px; background:white; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
    th, td { border:1px solid #ddd; padding:10px; text-align:center; }
    th { background:#c82333; color:white; }
    .btn { padding:6px 12px; border:none; border-radius:5px; cursor:pointer; text-decoration:none; font-size:14px; }
    .approve { background:green; color:white; }
    .deny { background:red; color:white; }
    .form-inline { display:flex; gap:5px; justify-content:center; }
    input[type=text], input[type=password] { padding:5px; }
    .show-pass { cursor:pointer; font-size:12px; color:#333; }
  </style>
</head>
<body>
<header>📩 Admin - Queries Management</header>
<div class="container">
  <h2>All Queries from Contact Us</h2>
  <table>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Role</th>
      <th>Message</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
    <?php while($row = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td><?php echo $row['id']; ?></td>
      <td><?php echo htmlspecialchars($row['name']); ?></td>
      <td><?php echo htmlspecialchars($row['email']); ?></td>
      <td><?php echo htmlspecialchars($row['phone']); ?></td>
      <td><?php echo htmlspecialchars($row['role']); ?></td>
      <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
      <td class="status"><?php echo $row['status']; ?></td>
      <td>
        <?php if ($row['status'] == 'Pending' && ($row['role'] == 'Hospital' || str_replace(' ', '', $row['role']) == 'BloodBank')): ?>
          <!-- Credential form -->
          <form method="POST" class="form-inline">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <input type="text" name="username" placeholder="Set Username" required>
            <input type="password" name="password" placeholder="Set Password" id="pass_<?php echo $row['id']; ?>" required>
            <span class="show-pass" onclick="togglePass(<?php echo $row['id']; ?>)">👁 Show</span>
            <button type="submit" name="set_credentials" class="btn approve">Approve ✅</button>
          </form>
          <a href="?action=deny&id=<?php echo $row['id']; ?>" class="btn deny">Deny ❌</a>
        <?php elseif ($row['status'] == 'Pending'): ?>
          <a href="?action=deny&id=<?php echo $row['id']; ?>" class="btn deny">Deny ❌</a>
        <?php else: ?>
          ✅ Done
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>

<script>
function togglePass(id){
  let field = document.getElementById("pass_"+id);
  if(field.type === "password"){
    field.type = "text";
  } else {
    field.type = "password";
  }
}
</script>
</body>
</html>
