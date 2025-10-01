<?php
/**
 * Cron Job Monitoring System
 * Provides comprehensive monitoring and force update capabilities for Super Admins
 */

// Prevent direct access
if (!defined('CRON_MONITOR_ACCESS')) {
    die('Direct access not allowed');
}

// Include required files if not already included
if (!function_exists('isSuperAdmin')) {
    require_once(dirname(__FILE__) . '/../functions.php');
}
if (!isset($_SESSION)) {
    require_once(dirname(__FILE__) . '/../vars.inc.php');
}

class CronMonitor {
    private $db;
    private $tempDir;
    
    public function __construct($database) {
        $this->db = $database;
        $this->tempDir = '../temp/';
    }
    
    /**
     * Get all configured cron jobs and their status
     */
    public function getCronJobs() {
        return [
            'company_mapping' => [
                'name' => 'Company & Trader Data Sync',
                'file' => 'update_company_mapping.php',
                'schedule' => 'Every 15 minutes (with trade confirm)',
                'description' => 'Updates company mapping and trader data for faster trade input',
                'logFile' => 'company_mapping.log',
                'critical' => true,
                'maxDelay' => 15,
                'priority' => 'high',
                'benefits' => 'Syncs newly added companies and traders to database for immediate trade input'
            ],
            'trade_confirm' => [
                'name' => 'Trade Confirmation Sweep',
                'file' => 'cron21.php',
                'schedule' => 'Every 15 minutes',
                'description' => 'Processes confirmed trades, sends confirmation emails, and moves trades to live status',
                'logFile' => 'cron_execution.log',
                'critical' => true,
                'maxDelay' => 15,
                'priority' => 'high'
            ],
            'ice_data_scrape' => [
                'name' => 'ICE Data Scraping',
                'file' => 'cron24.php', 
                'schedule' => 'Daily at 2:00 AM',
                'description' => 'Scrapes ICE market data for NA Power and Natural Gas indices',
                'logFile' => 'ice_scrape.log',
                'critical' => false,
                'maxDelay' => 1440,
                'priority' => 'low'
            ]
        ];
    }
    
    /**
     * Get status of a specific cron job
     */
    public function getCronStatus($jobKey) {
        $jobs = $this->getCronJobs();
        if (!isset($jobs[$jobKey])) {
            return null;
        }
        
        $job = $jobs[$jobKey];
        $logFile = $this->tempDir . $job['logFile'];
        
        $status = [
            'job' => $job,
            'lastExecution' => null,
            'nextExecution' => null,
            'status' => 'unknown',
            'statusColor' => '#666',
            'timeSinceLast' => null,
            'isOverdue' => false
        ];
        
        if (file_exists($logFile)) {
            $lastExec = file_get_contents($logFile);
            if ($lastExec) {
                $lastExecTime = intval($lastExec);
                $status['lastExecution'] = $lastExecTime;
                $status['timeSinceLast'] = time() - $lastExecTime;
                
                // Calculate next execution time
                if ($jobKey === 'trade_confirm') {
                    $status['nextExecution'] = $lastExecTime + (15 * 60); // 15 minutes
                } elseif ($jobKey === 'ice_data_scrape') {
                    // Next day at 2 AM
                    $nextDay = strtotime('+1 day', $lastExecTime);
                    $status['nextExecution'] = mktime(2, 0, 0, date('n', $nextDay), date('j', $nextDay), date('Y', $nextDay));
                } else {
                    $status['nextExecution'] = $lastExecTime + (15 * 60);
                }
                
                // Determine status
                $minutesSince = floor($status['timeSinceLast'] / 60);
                $status['isOverdue'] = $minutesSince > $job['maxDelay'];
                
                if ($minutesSince > $job['maxDelay']) {
                    $status['status'] = 'critical';
                    $status['statusColor'] = '#d32f2f';
                } elseif ($minutesSince > ($job['maxDelay'] * 0.75)) {
                    $status['status'] = 'warning';
                    $status['statusColor'] = '#f57c00';
                } else {
                    $status['status'] = 'ok';
                    $status['statusColor'] = '#388e3c';
                }
            }
        }
        
        return $status;
    }
    
    /**
     * Get status of all cron jobs
     */
    public function getAllCronStatus() {
        $jobs = $this->getCronJobs();
        $statuses = [];
        
        foreach ($jobs as $key => $job) {
            $statuses[$key] = $this->getCronStatus($key);
        }
        
        return $statuses;
    }
    
    /**
     * Force execute a cron job
     */
    public function forceExecuteCron($jobKey) {
        $jobs = $this->getCronJobs();
        if (!isset($jobs[$jobKey])) {
            return ['success' => false, 'message' => 'Invalid cron job'];
        }
        
        $job = $jobs[$jobKey];
        $scriptPath = $this->tempDir . '../util/' . $job['file'];
        
        if (!file_exists($scriptPath)) {
            return ['success' => false, 'message' => 'Cron script not found'];
        }
        
        // Log the force execution
        $this->logForceExecution($jobKey);
        
        // Execute the script
        $output = [];
        $returnCode = 0;
        
        if (function_exists('exec')) {
            exec("php " . escapeshellarg($scriptPath) . " 2>&1", $output, $returnCode);
        } else {
            // Fallback: include the script
            ob_start();
            try {
                include $scriptPath;
                $output = ob_get_contents();
            } catch (Exception $e) {
                $output = ['Error: ' . $e->getMessage()];
                $returnCode = 1;
            }
            ob_end_clean();
        }
        
        return [
            'success' => $returnCode === 0,
            'message' => $returnCode === 0 ? 'Cron job executed successfully' : 'Cron job execution failed',
            'output' => $output,
            'returnCode' => $returnCode
        ];
    }
    
    /**
     * Log force execution for audit trail
     */
    private function logForceExecution($jobKey) {
        $logFile = $this->tempDir . 'cron_force_executions.log';
        $logEntry = date('Y-m-d H:i:s') . " - Force executed: $jobKey by user: " . ($_SESSION['brokerID'] ?? 'unknown') . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get recent force execution log
     */
    public function getForceExecutionLog($limit = 10) {
        $logFile = $this->tempDir . 'cron_force_executions.log';
        if (!file_exists($logFile)) {
            return [];
        }
        
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice(array_reverse($lines), 0, $limit);
    }
    
    /**
     * Format time difference for display
     */
    public function formatTimeDiff($seconds) {
        if ($seconds < 60) {
            return $seconds . 's';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . 'm ' . $remainingSeconds . 's';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . 'h ' . $minutes . 'm';
        }
    }
}
?>
