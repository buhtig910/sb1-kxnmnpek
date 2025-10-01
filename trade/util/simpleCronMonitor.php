<?php
// Simple Cron Monitor - Basic version without complex dependencies
// This version is designed to be reliable and not break existing functionality

class SimpleCronMonitor {
    private $tempDir;
    
    public function __construct() {
        $this->tempDir = dirname(__FILE__) . '/../temp/';
    }
    
    // Get cron job status
    public function getCronStatus($jobName) {
        $logFile = $this->tempDir . $jobName . '.log';
        
        if (!file_exists($logFile)) {
            return [
                'status' => 'unknown',
                'lastRun' => 'Never',
                'nextRun' => 'Unknown',
                'overdue' => false
            ];
        }
        
        $lastRun = filemtime($logFile);
        $lastRunFormatted = date('Y-m-d H:i:s', $lastRun);
        
        // Calculate if overdue (basic logic)
        $overdue = false;
        $nextRun = 'Unknown';
        
        switch($jobName) {
            case 'company_mapping':
                $maxDelay = 3600; // 1 hour
                $nextRun = date('Y-m-d H:i:s', $lastRun + 3600);
                $overdue = (time() - $lastRun) > $maxDelay;
                break;
            case 'cron_execution':
                $maxDelay = 900; // 15 minutes
                $nextRun = date('Y-m-d H:i:s', $lastRun + 900);
                $overdue = (time() - $lastRun) > $maxDelay;
                break;
            case 'ice_scrape':
                $maxDelay = 3600; // 1 hour
                $nextRun = date('Y-m-d H:i:s', $lastRun + 3600);
                $overdue = (time() - $lastRun) > $maxDelay;
                break;
        }
        
        return [
            'status' => $overdue ? 'overdue' : 'ok',
            'lastRun' => $lastRunFormatted,
            'nextRun' => $nextRun,
            'overdue' => $overdue
        ];
    }
    
    // Get all cron jobs status
    public function getAllCronStatus() {
        $jobs = [
            'company_mapping' => [
                'name' => 'Company & Trader Data Sync',
                'description' => 'Syncs newly added companies and traders to database',
                'priority' => 'HIGH',
                'critical' => true
            ],
            'cron_execution' => [
                'name' => 'Trade Confirmation Processing',
                'description' => 'Processes trade confirmations every 15 minutes',
                'priority' => 'HIGH',
                'critical' => true
            ],
            'ice_scrape' => [
                'name' => 'ICE Market Data Scraping',
                'description' => 'Scrapes ICE market data for pricing',
                'priority' => 'MEDIUM',
                'critical' => false
            ]
        ];
        
        $results = [];
        foreach ($jobs as $key => $info) {
            $status = $this->getCronStatus($key);
            $results[$key] = array_merge($info, $status);
        }
        
        return $results;
    }
    
    // Force run a cron job (placeholder for now)
    public function forceRunCron($jobName) {
        // This would trigger the actual cron job
        // For now, just return success
        return [
            'success' => true,
            'message' => "Force run triggered for {$jobName}",
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
?>
