<?php
@session_start();

require("../vars.inc.php");
require("../functions.php");

// Only allow Super Admins
if(!isSuperAdmin()) {
    die("Access denied. Super Admin privileges required.");
}

if($_POST["action"] == "reset") {
    $brokerID = $_POST["brokerID"];
    $newPassword = $_POST["newPassword"];
    
    if($brokerID && $newPassword) {
        $query = "UPDATE tblB2TBroker SET fldPassword='" . md5($newPassword) . "' WHERE pkBrokerID=" . $brokerID;
        $result = mysqli_query($_SESSION['db'], $query);
        
        if($result && mysqli_affected_rows($_SESSION['db']) > 0) {
            echo "<p style='color: green;'>Password reset successfully for broker ID: $brokerID</p>";
        } else {
            echo "<p style='color: red;'>Failed to reset password. Check broker ID.</p>";
        }
    } else {
        echo "<p style='color: red;'>Please provide both broker ID and new password.</p>";
    }
}

// Get list of brokers
$query = "SELECT pkBrokerID, fldName, fldUsername, fldEmail FROM tblB2TBroker ORDER BY fldName";
$result = mysqli_query($_SESSION['db'], $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Password Reset</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 10px 0; }
        label { display: inline-block; width: 120px; }
        input, select { padding: 5px; margin: 5px; }
        .button { background: #007cba; color: white; padding: 8px 15px; border: none; cursor: pointer; }
        .button:hover { background: #005a87; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Admin Password Reset Tool</h2>
    <p><strong>Warning:</strong> This tool allows direct password reset. Use with caution.</p>
    
    <form method="post">
        <div class="form-group">
            <label>Broker ID:</label>
            <input type="number" name="brokerID" required />
        </div>
        
        <div class="form-group">
            <label>New Password:</label>
            <input type="password" name="newPassword" required />
        </div>
        
        <div class="form-group">
            <input type="hidden" name="action" value="reset" />
            <input type="submit" value="Reset Password" class="button" />
        </div>
    </form>
    
    <h3>Available Brokers:</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
        </tr>
        <?php while($row = mysqli_fetch_row($result)): ?>
        <tr>
            <td><?php echo $row[0]; ?></td>
            <td><?php echo htmlspecialchars($row[1]); ?></td>
            <td><?php echo htmlspecialchars($row[2]); ?></td>
            <td><?php echo htmlspecialchars($row[3]); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    
    <p><a href="../index.php">Return to Dashboard</a></p>
</body>
</html>
