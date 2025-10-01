<?php
@session_start();

require("vars.inc.php");
require("functions.php");

echo "<h2>User Debug Information</h2>";

// Check session data
echo "<h3>Session Data:</h3>";
echo "<p><strong>Username:</strong> " . ($_SESSION["username"] ?? "Not set") . "</p>";
echo "<p><strong>User Type:</strong> " . ($_SESSION["userType"] ?? "Not set") . "</p>";
echo "<p><strong>Broker ID:</strong> " . ($_SESSION["brokerID"] ?? "Not set") . "</p>";

// Check database record
if(isset($_SESSION["username"])) {
    $query = "SELECT pkBrokerID, fldName, fldUsername, fldUserType, fldActive FROM tblB2TBroker WHERE fldUsername='" . $_SESSION["username"] . "'";
    $result = mysqli_query($_SESSION['db'], $query);
    
    if($row = mysqli_fetch_row($result)) {
        echo "<h3>Database Record:</h3>";
        echo "<p><strong>Broker ID:</strong> " . $row[0] . "</p>";
        echo "<p><strong>Name:</strong> " . $row[1] . "</p>";
        echo "<p><strong>Username:</strong> " . $row[2] . "</p>";
        echo "<p><strong>User Type:</strong> " . $row[3] . "</p>";
        echo "<p><strong>Active:</strong> " . ($row[4] ? "Yes" : "No") . "</p>";
        
        // Test the functions
        echo "<h3>Function Tests:</h3>";
        echo "<p><strong>isAdmin():</strong> " . (isAdmin() ? "Yes" : "No") . "</p>";
        echo "<p><strong>isSuperAdmin():</strong> " . (isSuperAdmin() ? "Yes" : "No") . "</p>";
        echo "<p><strong>isSuperAdmin('2'):</strong> " . (isSuperAdmin('2') ? "Yes" : "No") . "</p>";
        echo "<p><strong>isSuperAdmin('" . $row[3] . "'):</strong> " . (isSuperAdmin($row[3]) ? "Yes" : "No") . "</p>";
    } else {
        echo "<p style='color: red;'>No database record found for username: " . $_SESSION["username"] . "</p>";
    }
} else {
    echo "<p style='color: red;'>No username in session</p>";
}

echo "<p><a href='index.php'>Return to Dashboard</a></p>";
?>
