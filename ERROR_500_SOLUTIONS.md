# ERROR 500 - Solutions & Testing Guide

## The Issue

You're getting Apache error 500 when accessing the website. This could be:
1. **mod_rewrite problem** (most common)
2. **PHP syntax error** (less likely)  
3. **File permission issue**
4. **Apache configuration** (AllowOverride not set)

## Quick Diagnosis - Test These URLs

### Test 1: Simple PHP (No Routing)
```
https://ilham.didzacorp.com/mts-alihsan/simple_test.php
```
- **Works:** Problem is in .htaccess routing
- **Fails:** Problem is in PHP/configuration

### Test 2: Dashboard Without Routing
```
https://ilham.didzacorp.com/mts-alihsan/index_noroute.php
```
- Test login without URL rewriting
- **Works:** Confirms routing is the issue
- **Fails:** There's a PHP problem

### Test 3: Login Page Direct
```
https://ilham.didzacorp.com/mts-alihsan/login.php
```
- Old-style direct access
- **Works:** Routing needed but misconfigured
- **Fails:** PHP problem

## Files Created for Debugging

| File | Purpose |
|------|---------|
| `simple_test.php` | Test basic PHP without routing |
| `index_noroute.php` | Dashboard without routing |
| `.htaccess` | Clean routing config (current) |
| `.htaccess.minimal` | Minimal routing version |
| `.htaccess.disabled` | No routing (disable rewriting) |
| `.htaccess.debug` | Debug version with logging |
| `TROUBLESHOOT_ERROR_500.txt` | Detailed troubleshooting guide |

## Recommended Actions (In Order)

### Step 1: Test Simple PHP
Visit: `simple_test.php`

Shows:
- ✓ PHP is working
- ✓ Files exist
- ✓ Config loaded
- ✓ Database connected

### Step 2: If that works...
The problem is definitely .htaccess/routing.

Try without routing:
```bash
mv .htaccess .htaccess.bak
```

Then test: `index.php` (will show 404, that's OK)

### Step 3: If no routing works...
Check Apache error log - THIS IS KEY!

Ask your hosting provider:
1. Apache error log location
2. Is mod_rewrite enabled?
3. Is AllowOverride set to All?

### Step 4: Get Apache Error Log
```bash
# Most common locations:
tail -100 /var/log/apache2/error.log
tail -100 /var/log/httpd/error_log
tail -100 /home/username/logs/error.log
```

The error log will show EXACTLY what's wrong.

## Quick Solutions

### If mod_rewrite is not enabled
Contact hosting provider to enable it.

### If AllowOverride not set
Ask provider to add this to Apache config:
```apache
<Directory /path/to/mts-alihsan>
    AllowOverride All
</Directory>
```

### If .htaccess syntax wrong
Try `.htaccess.minimal` instead:
```bash
cp .htaccess.minimal .htaccess
```

### If still failing
Use `.htaccess.disabled` to test without routing:
```bash
cp .htaccess.disabled .htaccess
```

## Debug Logging

Enable detailed logging:
```bash
cp .htaccess.debug .htaccess
mkdir -p /tmp/mts-alihsan-logs
chmod 777 /tmp/mts-alihsan-logs
```

Then check: `tail -50 /tmp/mts-alihsan-logs/rewrite.log`

## Important Notes

1. **simple_test.php is your key diagnostic tool**
   - If it works: routing problem
   - If it fails: PHP problem

2. **Apache error log tells you everything**
   - Check it BEFORE contacting support
   - Copy the exact error message

3. **mod_rewrite must be enabled**
   - This is a server requirement
   - Your hosting must support it

4. **AllowOverride All must be set**
   - Required for .htaccess to work
   - Check with hosting provider

5. **File permissions matter**
   - .htaccess should be 644
   - PHP files should be 644
   - Directories should be 755

## When to Contact Support

Contact hosting/provider with:
1. These test URLs you tried
2. Which ones worked/failed
3. Apache error log excerpt
4. Request to enable mod_rewrite or check AllowOverride

This will help them help you faster.

## Fallback: Original Code

If routing won't work, you can use original code with .php extensions:
- `index.php` instead of `/`
- `login.php` instead of `/login`
- `logout.php` instead of `/logout`
- etc.

This will definitely work with the original code.

---

**Start with simple_test.php - it will tell you where the problem is!**
