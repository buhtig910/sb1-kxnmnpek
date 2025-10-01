<?php
session_start();
require_once("functions.php");
require_once("vars.inc.php");

// Check if token and ID are provided
if (!isset($_GET['token']) || !isset($_GET['id'])) {
    die("Invalid login link. Please contact your administrator.");
}

$token = $_GET['token'];
$brokerID = (int)$_GET['id'];

// Validate the one-time login token
$query = "SELECT pkBrokerID, fldUsername, fldUserType, fldActive, fldSetupExpiry, fldFirstLogin FROM tblB2TBroker WHERE pkBrokerID = ? AND fldSetupToken = ? AND fldActive = 1";
$stmt = mysqli_prepare($_SESSION['db'], $query);
mysqli_stmt_bind_param($stmt, "is", $brokerID, $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Invalid or expired login link. Please contact your administrator.");
}

$brokerData = mysqli_fetch_assoc($result);

// Check if token has expired
if (strtotime($brokerData['fldSetupExpiry']) < time()) {
    die("This login link has expired. Please contact your administrator for a new link.");
}

// Check if this is still a first-time user
if ($brokerData['fldFirstLogin'] != 1) {
    die("This account has already been set up. Please use the regular login page.");
}

// Auto-login the user
$_SESSION['loggedIn42'] = "true1";
$_SESSION['userID'] = $brokerData['pkBrokerID'];
$_SESSION['userType'] = $brokerData['fldUserType'];
$_SESSION['username'] = $brokerData['fldUsername'];
$_SESSION['firstLogin'] = $brokerData['fldFirstLogin'];

// Clear the setup token for security (one-time use)
$clearTokenQuery = "UPDATE tblB2TBroker SET fldSetupToken = NULL WHERE pkBrokerID = ?";
$clearStmt = mysqli_prepare($_SESSION['db'], $clearTokenQuery);
mysqli_stmt_bind_param($clearStmt, "i", $brokerID);
mysqli_stmt_execute($clearStmt);

// Redirect to password change page
header("Location: change_password.php");
exit;
?>
