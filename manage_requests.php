<?php
session_start();
include("db.php");

// Security: Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

// Normalize blood group format
function normalizeGroup($group) {
    $group = strtoupper(trim($group));
    $group = str_replace("VE", "", $group); // convert A+VE -> A+
    return $group;
}

// Blood compatibility rules
function getCompatibleGroups($recipient) {
    $compatibility = [
        "O-" => ["O-"],
        "O+" => ["O-", "O+"],
        "A-" => ["O-", "A-"],
        "A+" => ["O-", "O+", "A-", "A+"],
        "B-" => ["O-", "B-"],
        "B+" => ["O-", "O+", "B-", "B+"],
        "AB-" => ["O-", "A-", "B-", "AB-"],
        "AB+" => ["O-", "O+", "A-", "A+", "B-", "B+", "AB-", "AB+"]
    ];
    $recipient = normalizeGroup($recipient);
    return $compatibility[$recipient] ?? [];
}

function isCompatible($donor, $recipient) {
    return in_array(normalizeGroup($donor), getCompatibleGroups($recipient));
}

// Handle Approve/Deny
if (isset($_GET['action']) && isset($_GET['id'])) {
    $request_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == "approve") {
        // Fetch request details
        $req = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM requests WHERE request_id=$request_id"));
        $recipient_group = $req['blood_group'];
        $quantity = intval($req['quantity']);

        // Only available AND not expired stock
        $today = date("Y-m-d");
        $stock_query = "SELECT blood_id, blood_group, expiry_date FROM blood_inventory 
                        WHERE status='available' AND expiry_date >= '$today'
                        ORDER BY expiry_date ASC";
        $stock_result = mysqli_query($conn, $stock_query);

        $used_units = [];
        while ($row = mysqli_fetch_assoc($stock_result)) {
            if (isCompatible($row['blood_group'], $recipient_group)) {
                $used_units[] = $row['blood_id'];
            }
            if (count($used_units) == $quantity) break;
        }

        if (count($used_units) >= $quantity) {
            mysqli_query($conn, "UPDATE requests SET status='fulfilled' WHERE request_id=$request_id");
            foreach ($used_units as $blood_id) {
                mysqli_query($conn, "UPDATE blood_inventory SET status='used' WHERE blood_id=$blood_id");
            }
            echo "<script>alert('Request Approved! Compatible units allocated.'); window.location='manage_requests.php';</script>";
        } else {
            echo "<script>alert('Insufficient compatible stock! Request cannot be fulfilled.'); window.location='manage_requests.php';</script>";
        }
    } elseif ($action == "deny") {
        mysqli_query($conn, "UPDATE requests SET status='denied' WHERE request_id=$request_id");
        echo "<script>alert('Request Denied!'); window.location='manage_requests.php';</script>";
    }
}

// Fetch all requests with details
$sql = "SELECT r.request_id, r.blood_group, r.quantity, r.status, r.request_date, 
               p.name AS patient_name, p.contact, p.age, p.gender, 
               h.name AS hospital_name
        FROM requests r
        JOIN patients p ON r.patient_id = p.patient_id
        JOIN hospitals h ON r.hospital_id = h.hospital_id
        ORDER BY r.request_date DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Requests</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f8f9fa; }
    h1 { color: #c82333; }
    table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
    table { width: 100%; background: white; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); }
    a.btn { padding: 5px 10px; text-decoration: none; border-radius: 4px; }
    a.approve { background: green; color: white; }
    a.deny { background: red; color: white; }
    a.back { display: inline-block; margin: 10px 0; padding: 8px 12px; background: #007bff; color: white; border-radius: 5px; text-decoration: none; }
    a.back:hover { background: #0056b3; }
  </style>
</head>
<body>
  <h1>Manage Blood Requests</h1>
  <a class="back" href="admin_dashboard.php">⬅ Back to Dashboard</a>
  <br><br>

  <table>
    <tr>
      <th>ID</th>
      <th>Patient</th>
      <th>Age</th>
      <th>Gender</th>
      <th>Contact</th>
      <th>Hospital</th>
      <th>Blood Group</th>
      <th>Quantity</th>
      <th>Date</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
      <td><?php echo $row['request_id']; ?></td>
      <td><?php echo $row['patient_name']; ?></td>
      <td><?php echo $row['age']; ?></td>
      <td><?php echo $row['gender']; ?></td>
      <td><?php echo $row['contact']; ?></td>
      <td><?php echo $row['hospital_name']; ?></td>
      <td><?php echo normalizeGroup($row['blood_group']); ?></td>
      <td><?php echo $row['quantity']; ?></td>
      <td><?php echo $row['request_date']; ?></td>
      <td><?php echo ucfirst($row['status']); ?></td>
      <td>
        <?php if ($row['status'] == 'pending') { ?>
            <a class="btn approve" href="manage_requests.php?action=approve&id=<?php echo $row['request_id']; ?>">Approve</a>
            <a class="btn deny" href="manage_requests.php?action=deny&id=<?php echo $row['request_id']; ?>">Deny</a>
        <?php } else { echo ucfirst($row['status']); } ?>
      </td>
    </tr>
    <?php } ?>
  </table>
</body>
</html>
