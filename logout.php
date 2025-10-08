<?php
session_start();

$redirect = "index.php"; // default fallback

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        $redirect = "admin_login.php";
    } elseif ($_SESSION['role'] == 'hospital') {
        $redirect = "hospital_login.php";
    } elseif ($_SESSION['role'] == 'donor') {
        $redirect = "donor_login.php"; // if you add donor login
    }
}

// Destroy session after deciding redirect
session_destroy();

header("Location: $redirect");
exit();
?>
