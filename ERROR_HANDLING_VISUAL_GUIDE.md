# Error Handling Visual Guide

## What You'll See When Errors Occur

### 1. Activation Error Display

When the plugin fails to activate, you'll see an error notice at the top of your WordPress admin:

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ⚠️ AI Chat Activation Error:                                      [✕]   │
│                                                                           │
│ WordPress database object is not available during activation             │
│                                                                           │
│ Error occurred at: 2025-10-21 19:59:07                                  │
│                                                                           │
│ ▼ Stack Trace (visible only when WP_DEBUG is enabled)                   │
│   #0 /wp-content/plugins/ai-multilingual-chat/...                       │
│   #1 /wp-includes/class-wp-hook.php(308): ...                          │
│   ...                                                                    │
└─────────────────────────────────────────────────────────────────────────┘
```

### 2. Database Tables Not Set Up Warning

If tables are not created during activation:

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ⚠️ AI Chat:                                                       [✕]   │
│                                                                           │
│ Database tables are not set up. Please deactivate and reactivate the    │
│ plugin.                                                                  │
└─────────────────────────────────────────────────────────────────────────┘
```

### 3. Constructor Error Notice

If an error occurs during plugin initialization:

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ❌ AI Multilingual Chat Error:                                           │
│                                                                           │
│ WordPress database object ($wpdb) is not available                       │
└─────────────────────────────────────────────────────────────────────────┘
```

## Debug Log Examples

### Successful Activation Log

When everything works correctly, your `debug.log` will show:

```
[21-Oct-2025 19:59:07 UTC] [AI Chat] [INFO] Plugin activation started
[21-Oct-2025 19:59:07 UTC] [AI Chat] [INFO] Database object verified
[21-Oct-2025 19:59:07 UTC] [AI Chat] [INFO] Table names configured: wp_ai_chat_conversations, wp_ai_chat_messages, wp_ai_chat_translation_cache, wp_ai_chat_faq
[21-Oct-2025 19:59:07 UTC] [AI Chat] [INFO] WordPress upgrade.php loaded, charset_collate: utf8mb4_unicode_ci
[21-Oct-2025 19:59:07 UTC] [AI Chat] [INFO] Conversations table created/updated. dbDelta result: Array
[21-Oct-2025 19:59:07 UTC] [AI Chat] [INFO] Conversations table verified: wp_ai_chat_conversations
[21-Oct-2025 19:59:07 UTC] [AI Chat] [INFO] Messages table created/updated. dbDelta result: Array
[21-Oct-2025 19:59:07 UTC] [AI Chat] [INFO] Messages table verified: wp_ai_chat_messages
[21-Oct-2025 19:59:08 UTC] [AI Chat] [INFO] Translation cache table created/updated. dbDelta result: Array
[21-Oct-2025 19:59:08 UTC] [AI Chat] [INFO] Translation cache table verified: wp_ai_chat_translation_cache
[21-Oct-2025 19:59:08 UTC] [AI Chat] [INFO] FAQ table created/updated. dbDelta result: Array
[21-Oct-2025 19:59:08 UTC] [AI Chat] [INFO] FAQ table verified: wp_ai_chat_faq
[21-Oct-2025 19:59:08 UTC] [AI Chat] [INFO] FAQ count: 0
[21-Oct-2025 19:59:08 UTC] [AI Chat] [INFO] Default FAQs inserted
[21-Oct-2025 19:59:08 UTC] [AI Chat] [INFO] Setting default options
[21-Oct-2025 19:59:08 UTC] [AI Chat] [INFO] Default options set: 29 out of 29
[21-Oct-2025 19:59:08 UTC] [AI Chat] [INFO] Rewrite rules flushed
[21-Oct-2025 19:59:08 UTC] [AI Chat] [INFO] Plugin activated successfully
```

### Successful Initialization Log

When the plugin loads correctly:

```
[21-Oct-2025 19:59:09 UTC] [AI Chat] [INFO] Plugin constructor started
[21-Oct-2025 19:59:09 UTC] [AI Chat] [INFO] Database object initialized
[21-Oct-2025 19:59:09 UTC] [AI Chat] [INFO] Table names configured: wp_ai_chat_conversations, wp_ai_chat_messages
[21-Oct-2025 19:59:09 UTC] [AI Chat] [INFO] Registering WordPress hooks
[21-Oct-2025 19:59:09 UTC] [AI Chat] [INFO] Core hooks registered
[21-Oct-2025 19:59:09 UTC] [AI Chat] [INFO] Frontend AJAX hooks registered
[21-Oct-2025 19:59:09 UTC] [AI Chat] [INFO] Admin AJAX hooks registered
[21-Oct-2025 19:59:09 UTC] [AI Chat] [INFO] Additional AJAX hooks registered
[21-Oct-2025 19:59:09 UTC] [AI Chat] [INFO] REST API hooks registered
[21-Oct-2025 19:59:09 UTC] [AI Chat] [INFO] All hooks registered successfully
[21-Oct-2025 19:59:09 UTC] [AI Chat] [INFO] Hooks initialized successfully
[21-Oct-2025 19:59:09 UTC] [AI Chat] [INFO] Plugin constructor completed successfully
```

### Error Log Example

When an error occurs during activation:

```
[21-Oct-2025 19:59:07 UTC] [AI Chat] [INFO] Plugin activation started
[21-Oct-2025 19:59:07 UTC] [AI Chat] [INFO] Database object verified
[21-Oct-2025 19:59:07 UTC] [AI Chat] [INFO] Table names configured: wp_ai_chat_conversations, wp_ai_chat_messages, wp_ai_chat_translation_cache, wp_ai_chat_faq
[21-Oct-2025 19:59:07 UTC] [AI Chat] [ERROR] Plugin activation failed: WordPress upgrade.php file not found at: /var/www/html/wp-admin/includes/upgrade.php
[21-Oct-2025 19:59:07 UTC] [AI Chat] [ERROR] Stack trace: #0 /wp-content/plugins/ai-multilingual-chat/ai-multilingual-chat.php(150): AI_Multilingual_Chat::activate_plugin()
#1 /wp-includes/class-wp-hook.php(308): ...
```

### Constructor Error Log Example

When an error occurs during initialization:

```
[21-Oct-2025 19:59:09 UTC] [AI Chat] [INFO] Plugin constructor started
[21-Oct-2025 19:59:09 UTC] [AI Chat] [ERROR] Constructor error: WordPress database object ($wpdb) is not available
[21-Oct-2025 19:59:09 UTC] [AI Chat] [ERROR] Stack trace: #0 /wp-content/plugins/ai-multilingual-chat/ai-multilingual-chat.php(32): AI_Multilingual_Chat->__construct()
```

## WordPress Plugins Page

### Before Activation
```
┌─────────────────────────────────────────────────────────────────┐
│ AI Multilingual Chat                                            │
│ v2.0.8                                                          │
│                                                                 │
│ Многоязычный чат с автопереводом через AI                      │
│                                                                 │
│ By Oleg Filin                                                  │
│                                                                 │
│ [Activate]  [Delete]                                           │
└─────────────────────────────────────────────────────────────────┘
```

### After Successful Activation
```
┌─────────────────────────────────────────────────────────────────┐
│ ✅ Plugin activated.                                             │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ AI Multilingual Chat                                    [Active]│
│ v2.0.8                                                          │
│                                                                 │
│ Многоязычный чат с автопереводом через AI                      │
│                                                                 │
│ By Oleg Filin                                                  │
│                                                                 │
│ [Deactivate]  [Settings]                                       │
└─────────────────────────────────────────────────────────────────┘
```

### After Failed Activation (with auto-deactivation)
```
┌─────────────────────────────────────────────────────────────────┐
│ ❌ Plugin could not be activated because it triggered a fatal    │
│    error.                                                        │
│                                                                 │
│ ⚠️ AI Chat Activation Error:                                    │
│                                                                 │
│ Failed to create conversations table: wp_ai_chat_conversations  │
│                                                                 │
│ Error occurred at: 2025-10-21 19:59:07                         │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ AI Multilingual Chat                                            │
│ v2.0.8                                                          │
│                                                                 │
│ Многоязычный чат с автопереводом через AI                      │
│                                                                 │
│ By Oleg Filin                                                  │
│                                                                 │
│ [Activate]  [Delete]                                           │
└─────────────────────────────────────────────────────────────────┘
```

## How to Access Debug Logs

### Via FTP/SFTP:
1. Connect to your server
2. Navigate to `/wp-content/`
3. Download `debug.log` file
4. Open in text editor

### Via WordPress File Manager Plugin:
1. Install "File Manager" plugin
2. Navigate to wp-content
3. View or download debug.log

### Via cPanel File Manager:
1. Login to cPanel
2. Open File Manager
3. Navigate to public_html/wp-content
4. Right-click debug.log > View

### Via SSH:
```bash
ssh user@yourserver.com
cd /path/to/wordpress/wp-content
tail -f debug.log
```

To see only AI Chat logs:
```bash
grep "AI Chat" debug.log
```

To see only errors:
```bash
grep "AI Chat.*ERROR" debug.log
```

## What Each Log Level Means

### [INFO]
✅ **Normal operation** - Everything is working as expected
- Use for: Successful operations, status updates, progress indicators
- Action: No action needed, informational only

### [WARNING]
⚠️ **Non-critical issue** - Something unexpected but not breaking
- Use for: Recoverable errors, deprecated features, missing optional data
- Action: Review and fix when convenient

### [ERROR]
❌ **Critical issue** - Something failed that prevents normal operation
- Use for: Fatal errors, database failures, missing required resources
- Action: Immediate attention required

## Troubleshooting Flowchart

```
Plugin Activation Failed?
         │
         ├─ Yes → Check Admin Notice
         │              │
         │              ├─ Shows Error? → Read Error Message
         │              │                        │
         │              │                        └─ Enable WP_DEBUG → Check debug.log
         │              │
         │              └─ No Error Notice? → Enable WP_DEBUG → Check debug.log
         │
         └─ No → Plugin Shows Inactive After Activation?
                         │
                         └─ Yes → Enable WP_DEBUG → Check debug.log
                                         │
                                         ├─ Constructor Error? → Fix Database/File Issues
                                         │
                                         └─ Hook Error? → Check for Plugin Conflicts
```

## Support Checklist

When contacting support, provide:

- [ ] WordPress version
- [ ] PHP version
- [ ] Plugin version
- [ ] Error message from admin notice
- [ ] Relevant lines from debug.log
- [ ] List of active plugins
- [ ] Hosting provider/server type
- [ ] Steps to reproduce the issue

## Quick Reference

| Issue | Where to Look | What to Check |
|-------|--------------|---------------|
| Activation fails | Admin notice + debug.log | Database permissions, table creation |
| Plugin deactivates | debug.log | Constructor errors, hook registration |
| Tables not created | debug.log | dbDelta errors, CREATE TABLE privileges |
| Features not working | debug.log | Hook registration, method existence |
| Database errors | debug.log + admin notice | Connection, permissions, charset |
