<?php
/**
 * Automated Database Backup Script for Greenlight Commodities
 * 
 * This script creates a complete backup of your database and saves it to a secure location.
 * 
 * Usage:
 * 1. Run manually: php auto_backup.php
 * 2. Schedule with cron: 0 2 * * * /usr/bin/php /path/to/auto_backup.php
 * 3. Run from browser: yourdomain.com/auto_backup.php
 */

// Configuration - UPDATE THESE VALUES FOR YOUR SERVER
$config = array(
    'db_host' => 'localhost',
    'db_user' => 'your_username',
    'db_pass' => 'your_password',
    'db_name' => 'greenlight_prod',
    
    // Backup settings
    'backup_dir' => '../backups/', // Directory to store backups (outside web root)
    'max_backups' => 10,           // Keep only this many backups
    'compress' => true,            // Compress backup files
    'include_timestamp' => true,   // Add timestamp to filename
    
    // Email notification (optional)
    'email_notifications' => false,
    'admin_email' => 'admin@yourdomain.com'
);

// Create backup directory if it doesn't exist
if (!is_dir($config['backup_dir'])) {
    mkdir($config['backup_dir'], 0755, true);
}

// Generate backup filename
$timestamp = date('Y-m-d_H-i-s');
$backup_filename = $config['db_name'] . '_backup_' . $timestamp . '.sql';
$backup_path = $config['backup_dir'] . $backup_filename;

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8",
        $config['db_user'],
        $config['db_pass'],
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        )
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get list of tables
$tables = array();
$stmt = $pdo->query("SHOW TABLES");
while ($row = $stmt->fetch()) {
    $tables[] = $row[array_keys($row)[0]];
}

// Start backup file
$backup_content = "-- Greenlight Commodities Database Backup\n";
$backup_content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$backup_content .= "-- Database: {$config['db_name']}\n\n";

// Add database creation and use statements
$backup_content .= "CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;\n";
$backup_content .= "USE `{$config['db_name']}`;\n\n";

// Backup each table
foreach ($tables as $table) {
    echo "Backing up table: $table\n";
    
    // Get table structure
    $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
    $row = $stmt->fetch();
    $backup_content .= "\n-- Table structure for table `$table`\n";
    $backup_content .= "DROP TABLE IF EXISTS `$table`;\n";
    $backup_content .= $row['Create Table'] . ";\n\n";
    
    // Get table data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
    $count = $stmt->fetch()['count'];
    
    if ($count > 0) {
        $backup_content .= "-- Data for table `$table`\n";
        
        // Process data in chunks to avoid memory issues
        $chunk_size = 1000;
        $offset = 0;
        
        while ($offset < $count) {
            $stmt = $pdo->prepare("SELECT * FROM `$table` LIMIT $chunk_size OFFSET $offset");
            $stmt->execute();
            
            while ($row = $stmt->fetch()) {
                $columns = array_keys($row);
                $values = array_values($row);
                
                // Escape values
                $values = array_map(function($value) use ($pdo) {
                    if ($value === null) return 'NULL';
                    return $pdo->quote($value);
                }, $values);
                
                $backup_content .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
            }
            
            $offset += $chunk_size;
        }
        $backup_content .= "\n";
    }
}

// Write backup to file
if (file_put_contents($backup_path, $backup_content)) {
    echo "Backup completed successfully!\n";
    echo "File: $backup_path\n";
    echo "Size: " . number_format(filesize($backup_path) / 1024 / 1024, 2) . " MB\n";
    
    // Compress if enabled
    if ($config['compress'] && function_exists('gzopen')) {
        $compressed_path = $backup_path . '.gz';
        $compressed_content = gzencode($backup_content, 9);
        if (file_put_contents($compressed_path, $compressed_content)) {
            echo "Compressed backup: $compressed_path\n";
            echo "Compressed size: " . number_format(filesize($compressed_path) / 1024 / 1024, 2) . " MB\n";
            unlink($backup_path); // Remove uncompressed version
        }
    }
    
    // Clean up old backups
    cleanupOldBackups($config['backup_dir'], $config['max_backups']);
    
    // Email notification if enabled
    if ($config['email_notifications']) {
        sendBackupNotification($config['admin_email'], $backup_path, true);
    }
    
} else {
    echo "Backup failed: Could not write to file\n";
    if ($config['email_notifications']) {
        sendBackupNotification($config['admin_email'], $backup_path, false);
    }
}

/**
 * Clean up old backup files
 */
function cleanupOldBackups($backup_dir, $max_backups) {
    $files = glob($backup_dir . '*.sql*');
    
    if (count($files) > $max_backups) {
        // Sort by modification time (oldest first)
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        // Remove oldest files
        $files_to_remove = array_slice($files, 0, count($files) - $max_backups);
        foreach ($files_to_remove as $file) {
            unlink($file);
            echo "Removed old backup: " . basename($file) . "\n";
        }
    }
}

/**
 * Send email notification
 */
function sendBackupNotification($email, $backup_path, $success) {
    $subject = $success ? 'Database Backup Successful' : 'Database Backup Failed';
    $message = $success 
        ? "Database backup completed successfully.\nFile: " . basename($backup_path)
        : "Database backup failed. Please check the backup script.";
    
    mail($email, $subject, $message);
}

echo "\nBackup process completed at " . date('Y-m-d H:i:s') . "\n";
?>
