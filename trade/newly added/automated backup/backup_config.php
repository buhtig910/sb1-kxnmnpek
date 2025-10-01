<?php
/**
 * Backup Configuration File
 * 
 * Update these values with your actual database credentials and preferences
 */

return array(
    // Database connection
    'db_host' => 'localhost',
    'db_user' => 'your_username',        // Replace with your database username
    'db_pass' => 'your_password',        // Replace with your database password
    'db_name' => 'greenlight_prod',      // Your database name
    
    // Backup settings
    'backup_dir' => '../backups/',       // Directory to store backups (outside web root)
    'max_backups' => 10,                 // Keep only this many backups
    'compress' => true,                  // Compress backup files (saves space)
    'include_timestamp' => true,         // Add timestamp to filename
    
    // Email notification (optional)
    'email_notifications' => false,      // Set to true to enable email notifications
    'admin_email' => 'admin@yourdomain.com', // Your email address
    
    // Backup schedule (if using cron)
    'schedule' => array(
        'daily' => '0 2 * * *',          // Daily at 2:00 AM
        'weekly' => '0 2 * * 0',         // Weekly on Sunday at 2:00 AM
        'monthly' => '0 2 1 * *'         // Monthly on 1st at 2:00 AM
    )
);
?>
