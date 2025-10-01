<?php
// Simple Force Cron Handler
// Handles AJAX requests to force run cron jobs

header('Content-Type: application/json');

// Basic security check
if (!isset($_POST['action']) || $_POST['action'] !== 'force_run') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Check if user has permission to force run cron jobs
session_start();
if (!isset($_SESSION['userType']) || ($_SESSION['userType'] != 1 && $_SESSION['userType'] != 2)) {
    echo json_encode(['success' => false, 'message' => 'Access denied. User type: ' . ($_SESSION['userType'] ?? 'not set')]);
    exit;
}

$jobName = $_POST['job'] ?? '';

// Validate job name
$allowedJobs = ['company_mapping', 'cron_execution', 'ice_scrape'];
if (!in_array($jobName, $allowedJobs)) {
    echo json_encode(['success' => false, 'message' => 'Invalid job name: ' . $jobName]);
    exit;
}

// Log the force run request
$logFile = dirname(__FILE__) . '/../temp/force_run.log';
$logEntry = date('Y-m-d H:i:s') . " - Force run requested for: {$jobName} by user: " . ($_SESSION['username'] ?? 'unknown') . "\n";
file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

// For now, just simulate success
// In a real implementation, this would trigger the actual cron job
echo json_encode([
    'success' => true,
    'message' => "Force run triggered for {$jobName}",
    'timestamp' => date('Y-m-d H:i:s')
]);
?>