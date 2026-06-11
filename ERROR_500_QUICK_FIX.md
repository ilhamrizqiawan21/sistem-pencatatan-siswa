## Quick Fix Steps for Error 500

### Step 1: Test Basic PHP (No Routing)
Access: `https://ilham.didzacorp.com/mts-alihsan/simple_test.php`

This file doesn't use .htaccess routing, so if this works, the problem is in routing.

### Step 2: Check .htaccess Status

The `.htaccess` file in root has been simplified:
```apache
RewriteEngine On
RewriteBase /mts-alihsan/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/mts-alihsan/index\.php
RewriteRule ^(.*)$ index.php [L,QSA]
```

This should work without conflicts.

### Step 3: Verify Permissions

SSH/Terminal commands:
```bash
# Fix directory permissions
chmod 755 /home/user/public_html/mts-alihsan/

# Fix .htaccess permissions
chmod 644 /home/user/public_html/mts-alihsan/.htaccess

# Fix file permissions
chmod 644 /home/user/public_html/mts-alihsan/*.php
```

### Step 4: Check Apache Error Log

Ask your hosting provider for:
- Apache error log location
- PHP error log location

Common locations:
- `/var/log/apache2/error.log`
- `/var/log/httpd/error_log`
- `/home/user/logs/error.log`

### Step 5: Temporary: Revert to Original

If still getting 500 error, temporarily use the original code:

```bash
# Rename .htaccess to disable routing
mv .htaccess .htaccess.bak

# Then test:
# https://ilham.didzacorp.com/mts-alihsan/index.php
```

If this works, the problem is definitely in .htaccess or mod_rewrite.

### Step 6: Enable .htaccess Debugging

Create a debug `.htaccess`:
```apache
RewriteEngine On
RewriteBase /mts-alihsan/
RewriteLogLevel 3
RewriteLog /tmp/rewrite.log
```

Then check `/tmp/rewrite.log` for errors.

### Common Issues

1. **mod_rewrite not enabled**
   - Contact hosting provider
   - Ask to enable Apache mod_rewrite

2. **RewriteBase incorrect**
   - Should be: `/mts-alihsan/`
   - Check domain structure

3. **Infinite rewrite loop**
   - Add `RewriteCond %{REQUEST_URI} !^/mts-alihsan/index\.php`
   - Already done in our config

4. **File not found**
   - index.php must be in `/mts-alihsan/` root
   - Check file exists

### Alternative: Disable Clean URLs Temporarily

Edit `.htaccess`:
```apache
RewriteEngine Off
```

Then access with full URLs:
- `https://ilham.didzacorp.com/mts-alihsan/index.php`
- `https://ilham.didzacorp.com/mts-alihsan/login.php`

This will help identify if the problem is routing or application.

### Next Steps

1. ✅ Test simple_test.php first
2. ✅ Check file permissions
3. ✅ Contact hosting provider if mod_rewrite needed
4. ✅ Check Apache error logs
5. ✅ Test with original code if needed
