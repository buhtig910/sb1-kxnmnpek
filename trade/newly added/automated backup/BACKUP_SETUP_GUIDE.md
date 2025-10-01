# 🗄️ Automated Database Backup Setup Guide

## 🎯 **What This Gives You:**

- ✅ **Automatic daily backups** at 2:00 AM
- ✅ **Compressed backup files** (saves space)
- ✅ **Automatic cleanup** (keeps only 10 most recent backups)
- ✅ **Email notifications** (optional)
- ✅ **Secure storage** outside web root
- ✅ **Complete database backup** with all tables and data

## 🚀 **Setup Steps:**

### **Step 1: Configure Database Credentials**

1. **Edit `backup_config.php`:**
   ```php
   'db_user' => 'your_actual_username',
   'db_pass' => 'your_actual_password',
   'db_name' => 'greenlight_prod',
   ```

2. **Update backup directory** (recommended: outside web root):
   ```php
   'backup_dir' => '../backups/',
   ```

### **Step 2: Test the Backup Script**

1. **Run manually first:**
   ```bash
   php auto_backup.php
   ```

2. **Check output** - should show:
   ```
   Backing up table: tblB2TTransaction
   Backing up table: tblB2TClient
   ...
   Backup completed successfully!
   ```

### **Step 3: Set Up Automated Scheduling**

#### **Option A: Cron Job (Linux/Unix/Mac)**

1. **Open crontab:**
   ```bash
   crontab -e
   ```

2. **Add backup schedule:**
   ```bash
   # Daily backup at 2:00 AM
   0 2 * * * /usr/bin/php /path/to/your/trade/auto_backup.php
   
   # Weekly backup on Sunday at 2:00 AM
   0 2 * * 0 /usr/bin/php /path/to/your/trade/auto_backup.php
   ```

#### **Option B: Windows Task Scheduler**

1. **Open Task Scheduler**
2. **Create Basic Task**
3. **Set trigger** (Daily at 2:00 AM)
4. **Set action** (Start a program)
5. **Program**: `C:\path\to\php.exe`
6. **Arguments**: `C:\path\to\your\trade\auto_backup.php`

#### **Option C: Web-based Cron (Shared Hosting)**

1. **Use your hosting provider's cron service**
2. **Set URL**: `https://yourdomain.com/auto_backup.php`
3. **Set frequency**: Daily at 2:00 AM

## 🔧 **Advanced Configuration:**

### **Email Notifications:**

1. **Enable in config:**
   ```php
   'email_notifications' => true,
   'admin_email' => 'your@email.com',
   ```

2. **Test email functionality**

### **Backup Retention:**

1. **Adjust number of backups kept:**
   ```php
   'max_backups' => 30,  // Keep 30 backups instead of 10
   ```

2. **Change backup frequency**

### **Compression Settings:**

1. **Disable compression** (if space isn't an issue):
   ```php
   'compress' => false,
   ```

## 📁 **Backup File Structure:**

```
../backups/
├── greenlight_prod_backup_2024-12-20_14-30-00.sql.gz
├── greenlight_prod_backup_2024-12-19_14-30-00.sql.gz
├── greenlight_prod_backup_2024-12-18_14-30-00.sql.gz
└── ...
```

## 🧪 **Testing Your Setup:**

### **1. Manual Test:**
```bash
cd /path/to/your/trade
php auto_backup.php
```

### **2. Verify Backup File:**
- Check backup directory
- Verify file size (~7-8 MB)
- Check timestamp in filename

### **3. Test Restore (Optional):**
```sql
-- In phpMyAdmin, create test database
CREATE DATABASE test_restore;

-- Import your backup file
-- Verify all tables and data are present
```

## 🚨 **Security Considerations:**

### **1. File Permissions:**
```bash
chmod 600 auto_backup.php
chmod 700 ../backups/
```

### **2. Database User:**
- Use dedicated backup user with minimal permissions
- Only SELECT and SHOW privileges needed

### **3. Backup Location:**
- Store backups outside web root
- Use secure, encrypted storage if possible

## 📊 **Monitoring & Maintenance:**

### **1. Check Backup Logs:**
- Monitor backup file sizes
- Verify backup timestamps
- Check for failed backups

### **2. Regular Testing:**
- Test restore process monthly
- Verify backup integrity
- Check disk space usage

### **3. Update Credentials:**
- Update config when passwords change
- Test backup after any changes

## 💡 **Pro Tips:**

1. **Start with daily backups** - you can always adjust later
2. **Test on a small database first** if you have one
3. **Monitor disk space** - backups can accumulate quickly
4. **Keep backups in multiple locations** (local + cloud)
5. **Document your backup process** for team members

## 🆘 **Troubleshooting:**

### **Common Issues:**

1. **Permission denied:**
   - Check file permissions
   - Verify backup directory exists

2. **Database connection failed:**
   - Verify credentials in config
   - Check database server status

3. **Backup file too large:**
   - Enable compression
   - Check for large tables
   - Consider excluding log tables

4. **Cron not working:**
   - Check cron service status
   - Verify file paths
   - Check cron logs

## 🎉 **You're All Set!**

With this automated backup system, your Greenlight Commodities database will be:
- ✅ **Automatically backed up daily**
- ✅ **Safely stored outside web root**
- ✅ **Compressed to save space**
- ✅ **Automatically cleaned up**
- ✅ **Monitored and notified**

Your trading data is now protected with professional-grade automated backups! 🗄️✨
