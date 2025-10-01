<?php
// Simple Cron Display - Basic version for Super Admins
// This version is designed to be reliable and not break existing functionality

// Only show for Super Admins
if (!function_exists('isSuperAdmin') || !isSuperAdmin()) {
    return; // Exit silently if not Super Admin
}

// Include the simple monitor
require_once('simpleCronMonitor.php');

$monitor = new SimpleCronMonitor();
$cronJobs = $monitor->getAllCronStatus();

// Count overdue jobs
$overdueCount = 0;
foreach ($cronJobs as $job) {
    if ($job['overdue']) {
        $overdueCount++;
    }
}
?>

<div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 15px; margin: 15px 0; font-family: Arial, sans-serif;">
    <h3 style="margin: 0 0 8px 0; color: #495057; font-size: 14px;">
        Cron Job Monitor
        <?php if ($overdueCount > 0): ?>
            <span style="background: #dc3545; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px; margin-left: 6px;">
                <?php echo $overdueCount; ?> Overdue
            </span>
        <?php endif; ?>
    </h3>
    
    <div style="display: grid; gap: 10px;">
        <?php foreach ($cronJobs as $key => $job): ?>
            <div style="background: white; border: 1px solid #e9ecef; border-radius: 4px; padding: 10px; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <h4 style="margin: 0; color: #212529; font-size: 13px;">
                        <?php echo htmlspecialchars($job['name']); ?>
                        <?php if ($job['critical']): ?>
                            <span style="background: #dc3545; color: white; padding: 1px 4px; border-radius: 2px; font-size: 9px; margin-left: 6px;">CRITICAL</span>
                        <?php endif; ?>
                    </h4>
                    <span style="
                        background: <?php echo $job['overdue'] ? '#dc3545' : '#28a745'; ?>;
                        color: white;
                        padding: 2px 6px;
                        border-radius: 3px;
                        font-size: 10px;
                        font-weight: bold;
                    ">
                        <?php echo $job['overdue'] ? 'OVERDUE' : 'OK'; ?>
                    </span>
                </div>
                
                <p style="margin: 0 0 8px 0; color: #6c757d; font-size: 11px;">
                    <?php echo htmlspecialchars($job['description']); ?>
                </p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; font-size: 11px;">
                    <div>
                        <strong>Last Run:</strong><br>
                        <span style="color: #495057;"><?php echo htmlspecialchars($job['lastRun']); ?></span>
                    </div>
                    <div>
                        <strong>Next Expected:</strong><br>
                        <span style="color: #495057;"><?php echo htmlspecialchars($job['nextRun']); ?></span>
                    </div>
                    <div style="text-align: right;">
                        <button onclick="forceRunCron('<?php echo $key; ?>')" 
                                style="background: #007bff; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 10px;">
                            Force Update
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #dee2e6; font-size: 10px; color: #6c757d;">
        <strong>Note:</strong> This monitor shows the status of automated background processes. 
        Force updates are useful for quickly syncing newly added companies and traders.
    </div>
</div>

<script>
function forceRunCron(jobName) {
    if (confirm('Are you sure you want to force run the ' + jobName + ' cron job?')) {
        // Create a simple AJAX request
        fetch('util/forceCron.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=force_run&job=' + jobName
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Force run triggered successfully!');
                location.reload(); // Refresh to show updated status
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error triggering force run: ' + error);
        });
    }
}
</script>
