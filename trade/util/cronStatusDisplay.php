<?php
/**
 * Cron Status Display Component
 * Reusable component for displaying cron job status across multiple pages
 */

// Prevent direct access
if (!defined('CRON_STATUS_ACCESS')) {
    die('Direct access not allowed');
}

// Include required files
require_once(dirname(__FILE__) . '/../functions.php');
require_once(dirname(__FILE__) . '/../vars.inc.php');
require_once('cronMonitor.php');

// Debug: Show that we're loading the cron status display
echo '<!-- Debug: Cron Status Display component loaded -->';

// Only show for Super Admins
if (!isSuperAdmin()) {
    // Debug: Show why Super Admin check failed
    echo '<!-- Debug: isSuperAdmin() returned false. User type: ' . ($_SESSION['userType'] ?? 'not set') . ' -->';
    return;
}

// Debug: Check if we can create the cron monitor
try {
    $cronMonitor = new CronMonitor($_SESSION['db']);
    $allStatuses = $cronMonitor->getAllCronStatus();
} catch (Exception $e) {
    echo '<div style="background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 4px;">';
    echo '<strong>Error loading cron monitor:</strong> ' . htmlspecialchars($e->getMessage());
    echo '</div>';
    return;
}
?>

<div id="cronStatusContainer" style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="margin: 0; color: #495057; font-size: 16px;">
            <i class="fas fa-clock" style="margin-right: 8px; color: #6c757d;"></i>
            Cron Job Monitor
        </h3>
        <div style="font-size: 12px; color: #6c757d;">
            Last updated: <span id="cronLastUpdate"><?php echo date('H:i:s'); ?></span>
        </div>
    </div>
    
    <div id="cronJobsList">
        <?php foreach ($allStatuses as $jobKey => $status): ?>
            <?php if ($status): ?>
                <div class="cronJobItem" style="background: white; border: 1px solid #e9ecef; border-radius: 6px; padding: 12px; margin-bottom: 10px; position: relative;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                <div style="width: 12px; height: 12px; border-radius: 50%; background: <?php echo $status['statusColor']; ?>; margin-right: 10px;"></div>
                                <strong style="color: #495057;"><?php echo $status['job']['name']; ?></strong>
                                <?php if ($status['job']['critical']): ?>
                                    <span style="background: #dc3545; color: white; font-size: 10px; padding: 2px 6px; border-radius: 3px; margin-left: 8px;">CRITICAL</span>
                                <?php endif; ?>
                                <?php if (isset($status['job']['priority']) && $status['job']['priority'] === 'high'): ?>
                                    <span style="background: #007bff; color: white; font-size: 10px; padding: 2px 6px; border-radius: 3px; margin-left: 8px;">HIGH PRIORITY</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 12px; color: #6c757d; margin-bottom: 8px;">
                                <?php echo $status['job']['description']; ?>
                            </div>
                            <div style="font-size: 11px; color: #868e96;">
                                <strong>Schedule:</strong> <?php echo $status['job']['schedule']; ?>
                                <?php if ($status['lastExecution']): ?>
                                    | <strong>Last run:</strong> <?php echo date('M j, H:i:s', $status['lastExecution']); ?>
                                    | <strong>Time since:</strong> <span style="color: <?php echo $status['statusColor']; ?>; font-weight: bold;">
                                        <?php echo $cronMonitor->formatTimeDiff($status['timeSinceLast']); ?>
                                    </span>
                                <?php else: ?>
                                    | <span style="color: #dc3545; font-weight: bold;">Never executed</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="text-align: right; margin-left: 15px;">
                            <?php if ($status['job']['critical'] || $status['isOverdue']): ?>
                                <button onclick="forceExecuteCron('<?php echo $jobKey; ?>')" 
                                        style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 11px; cursor: pointer; font-weight: bold;">
                                    <i class="fas fa-play" style="margin-right: 4px;"></i>Force Update
                                </button>
                            <?php else: ?>
                                <button onclick="forceExecuteCron('<?php echo $jobKey; ?>')" 
                                        style="background: #6c757d; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 11px; cursor: pointer;">
                                    <i class="fas fa-play" style="margin-right: 4px;"></i>Force Update
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($status['isOverdue']): ?>
                        <div style="background: #f8d7da; color: #721c24; padding: 8px; border-radius: 4px; margin-top: 8px; font-size: 12px; border-left: 4px solid #dc3545;">
                            <i class="fas fa-exclamation-triangle" style="margin-right: 5px;"></i>
                            <strong>WARNING:</strong> This cron job is overdue by <?php echo $cronMonitor->formatTimeDiff($status['timeSinceLast'] - ($status['job']['maxDelay'] * 60)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    
    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 11px; color: #6c757d;">
                <i class="fas fa-info-circle" style="margin-right: 5px;"></i>
                Force updates are logged for audit purposes
            </div>
            <button onclick="refreshCronStatus()" style="background: #007bff; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 11px; cursor: pointer;">
                <i class="fas fa-sync-alt" style="margin-right: 4px;"></i>Refresh
            </button>
        </div>
    </div>
</div>

<script>
// Force execute a cron job
function forceExecuteCron(jobKey) {
    if (!confirm('Are you sure you want to force execute this cron job? This should only be done in emergency situations.')) {
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 4px;"></i>Executing...';
    button.disabled = true;
    
    // Make AJAX request
    fetch('util/forceCron.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'jobKey=' + encodeURIComponent(jobKey)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cron job executed successfully!\n\nOutput:\n' + (data.output ? data.output.join('\n') : 'No output'));
            // Refresh the status
            refreshCronStatus();
        } else {
            alert('Error executing cron job:\n' + data.message);
        }
    })
    .catch(error => {
        alert('Error executing cron job: ' + error);
    })
    .finally(() => {
        // Restore button state
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Refresh cron status
function refreshCronStatus() {
    const container = document.getElementById('cronStatusContainer');
    const refreshButton = container.querySelector('button[onclick="refreshCronStatus()"]');
    const originalText = refreshButton.innerHTML;
    
    refreshButton.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 4px;"></i>Refreshing...';
    refreshButton.disabled = true;
    
    // Reload the page to get fresh status
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

// Auto-refresh every 30 seconds
setInterval(() => {
    const lastUpdate = document.getElementById('cronLastUpdate');
    if (lastUpdate) {
        const now = new Date();
        lastUpdate.textContent = now.toLocaleTimeString();
    }
}, 30000);
</script>

<style>
.cronJobItem:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: box-shadow 0.2s ease;
}

.cronJobItem button:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.cronJobItem button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
</style>
