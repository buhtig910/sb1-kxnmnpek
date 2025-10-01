<?php
@session_start();

require("../vars.inc.php");
require("../functions.php");

echo "<h2>Current User Permissions Check</h2>";
echo "<p><strong>Username:</strong> " . $_SESSION["username"] . "</p>";
echo "<p><strong>User Type:</strong> " . $_SESSION["userType"] . "</p>";
echo "<p><strong>Is Admin:</strong> " . (isAdmin() ? "Yes" : "No") . "</p>";
echo "<p><strong>Is Super Admin:</strong> " . (isSuperAdmin() ? "Yes" : "No") . "</p>";

// Show what user types mean
echo "<h3>User Type Reference:</h3>";
echo "<ul>";
echo "<li>0 = General User</li>";
echo "<li>1 = Administrator</li>";
echo "<li>2 = Super Administrator</li>";
echo "</ul>";

// Show current broker info
$query = "SELECT pkBrokerID, fldName, fldUsername, fldUserType, fldActive FROM tblB2TBroker WHERE fldUsername='" . $_SESSION["username"] . "'";
$result = mysqli_query($_SESSION['db'], $query);
if($row = mysqli_fetch_row($result)) {
    echo "<h3>Your Broker Record:</h3>";
    echo "<p><strong>Broker ID:</strong> " . $row[0] . "</p>";
    echo "<p><strong>Name:</strong> " . $row[1] . "</p>";
    echo "<p><strong>Username:</strong> " . $row[2] . "</p>";
    echo "<p><strong>User Type:</strong> " . $row[3] . "</p>";
    echo "<p><strong>Active:</strong> " . ($row[4] ? "Yes" : "No") . "</p>";
}

echo "<p><a href='../index.php'>Return to Dashboard</a></p>";
?>
