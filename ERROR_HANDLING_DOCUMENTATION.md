# Error Handling and Debugging Guide

## Overview

Comprehensive error handling has been added to the AI Multilingual Chat plugin to prevent WordPress from automatically deactivating the plugin when errors occur during activation or initialization.

## Key Features

### 1. Comprehensive Try-Catch Blocks

All critical methods now have try-catch blocks:

- **`activate_plugin()`**: Catches activation errors and stores them for admin display
- **`__construct()`**: Catches initialization errors without causing fatal failures
- **`init_hooks()`**: Catches hook registration errors

### 2. Detailed Logging

The plugin now logs all key initialization steps when `WP_DEBUG` is enabled:

#### To Enable Debug Logging:

Add these lines to your `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

#### Log Format:

All log messages follow this format:
```
[AI Chat] [LEVEL] Message
```

Where `LEVEL` can be:
- `INFO`: Normal operation messages
- `WARNING`: Non-critical issues
- `ERROR`: Critical errors that need attention

#### Log Locations:

Logs are written to:
- **Standard location**: `/wp-content/debug.log`
- **Alternative location** (if configured): Custom log path set in `wp-config.php`

### 3. Activation Error Display

If activation fails, the error is displayed in the WordPress admin area:

- Error message
- Timestamp of when the error occurred
- Stack trace (only visible when WP_DEBUG is enabled)

The error notice appears at the top of the admin pages and is dismissible.

### 4. Table Existence Verification

The plugin now verifies that all database tables are created successfully:

1. After each `dbDelta()` call during activation
2. Before querying tables in `admin_notices()`
3. Throws exceptions if tables fail to create

### 5. Database Object Validation

The plugin validates that the WordPress database object (`$wpdb`) is available:

- During activation in `activate_plugin()`
- During initialization in `__construct()`

## What Gets Logged

### During Activation (`activate_plugin()`):

1. Plugin activation started
2. Database object verified
3. Table names configured
4. WordPress upgrade.php loaded with charset
5. Each table creation/verification:
   - Conversations table
   - Messages table
   - Translation cache table
   - FAQ table
6. Default FAQs inserted
7. Default options set (count)
8. Rewrite rules flushed
9. Plugin activated successfully
10. Any errors that occur during activation

### During Initialization (`__construct()`):

1. Plugin constructor started
2. Database object initialized
3. Table names configured
4. Hooks initialized successfully
5. Plugin constructor completed successfully
6. Any errors during initialization

### During Hook Registration (`init_hooks()`):

1. Registering WordPress hooks
2. Core hooks registered
3. Frontend AJAX hooks registered
4. Admin AJAX hooks registered
5. Additional AJAX hooks registered
6. REST API hooks registered
7. All hooks registered successfully

## Troubleshooting

### Plugin Not Staying Active

If the plugin activates but then shows as inactive:

1. **Enable WP_DEBUG** (see instructions above)
2. **Check the debug log** at `/wp-content/debug.log`
3. Look for error messages with `[AI Chat] [ERROR]`
4. Check the admin area for activation error notices

### Common Issues and Solutions

#### Database Tables Not Created

**Symptoms:**
- Warning notice: "Database tables are not set up"
- Empty conversations/messages lists

**Solution:**
1. Check debug log for table creation errors
2. Verify database user has CREATE TABLE privileges
3. Check if `dbDelta()` reported any issues
4. Deactivate and reactivate the plugin

#### Database Object Not Available

**Symptoms:**
- Error: "WordPress database object ($wpdb) is not available"

**Solution:**
1. Check if WordPress core files are intact
2. Verify database connection in `wp-config.php`
3. Check if other plugins are working correctly

#### Hook Registration Errors

**Symptoms:**
- Error: "Error registering hooks"
- Plugin features not working

**Solution:**
1. Check debug log for specific hook causing the issue
2. Verify method names exist in the class
3. Check for PHP version compatibility
4. Look for conflicts with other plugins

## Testing

A comprehensive test suite verifies all error handling:

```bash
php tests/test-error-handling.php
```

This test verifies:
- ✓ Try-catch blocks in all critical methods
- ✓ Proper error logging
- ✓ Activation error storage and display
- ✓ Table existence verification
- ✓ Graceful error handling
- ✓ Error re-throwing to prevent partial activation

## Security Considerations

All error handling follows WordPress security best practices:

- User input is properly escaped using `esc_html()`
- Errors are only displayed to users with `manage_options` capability
- Stack traces are only shown when WP_DEBUG is enabled
- Database queries use WordPress prepared statements
- No sensitive information is exposed in error messages

## Best Practices

### For Developers:

1. **Always enable WP_DEBUG during development**
2. **Check debug logs after activation**
3. **Verify all tables are created properly**
4. **Test with different database configurations**
5. **Test with different WordPress versions**

### For Site Administrators:

1. **Enable WP_DEBUG when troubleshooting**
2. **Check admin notices after activation**
3. **Keep a backup before activating new plugins**
4. **Review debug logs regularly**
5. **Contact support with log excerpts if issues occur**

## Error Recovery

If the plugin encounters an error:

1. **Constructor errors**: Plugin continues to load but shows admin notice
2. **Activation errors**: Activation is prevented, error is stored and displayed
3. **Hook registration errors**: Specific feature may not work, error is logged

To recover from errors:

1. Fix the underlying issue (database permissions, file permissions, etc.)
2. Deactivate the plugin
3. Clear any `aic_activation_error` option from the database (optional)
4. Reactivate the plugin
5. Check logs to verify successful activation

## Additional Information

- **Minimum PHP Version**: 7.0+
- **Minimum WordPress Version**: 5.0+
- **Required Database Privileges**: CREATE, SELECT, INSERT, UPDATE, DELETE
- **Required File Permissions**: Read/write for wp-content/debug.log (if WP_DEBUG_LOG is enabled)

## Support

If you encounter persistent activation issues:

1. Enable WP_DEBUG and collect logs
2. Check for conflicts with other plugins
3. Try activating with only this plugin enabled
4. Check server error logs (usually in `/var/log/apache2/error.log` or similar)
5. Contact support with:
   - WordPress version
   - PHP version
   - Debug log excerpts
   - List of active plugins
   - Server configuration details
