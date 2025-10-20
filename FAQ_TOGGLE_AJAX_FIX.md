# FAQ Toggle Button AJAX Fix

## Problem Description
The FAQ toggle button had an issue where clicking it would cause the page to become empty (but not white screen), requiring a manual page reload to see the changes. The toggle operation worked in the database, but the UI didn't update properly after the POST redirect.

## Solution Implemented
Implemented AJAX functionality for the FAQ toggle button to avoid full page reloads and ensure the UI updates immediately after activation/deactivation.

## Changes Made

### 1. Main Plugin File (`ai-multilingual-chat.php`)
- **Added AJAX action hook**: Registered `wp_ajax_aic_toggle_faq` action
- **Added `ajax_toggle_faq()` method**: New AJAX handler with the following features:
  - Security: Nonce verification using `check_ajax_referer()`
  - Permission check: Validates user has `manage_options` capability
  - Database operations: Toggles the `is_active` field in the FAQ table
  - Error handling: Returns proper error messages for various failure scenarios
  - Success response: Returns the new state of the FAQ item

### 2. FAQ Template (`templates/faq.php`)
- **Replaced form submission with AJAX button**:
  - Changed from `<form method="post">` with submit button to a simple `<button type="button">`
  - Added `aic-faq-toggle` class for JavaScript targeting
  - Added `data-faq-id` and `data-is-active` attributes for state management

- **Added JavaScript AJAX handler**:
  - Uses jQuery AJAX to call `aic_toggle_faq` action
  - Updates UI elements immediately without page reload:
    - Button text changes between "Отключить" and "Включить"
    - Status cell updates with appropriate icon and color
    - Button state (data attribute) updates to reflect new status
  - Shows success notification that auto-dismisses after 3 seconds
  - Comprehensive error handling with user-friendly messages
  - Disables button during request to prevent double-clicks

## Benefits

1. **Better User Experience**: 
   - No page reload required
   - Instant visual feedback
   - Success message confirmation
   - Loading state indication

2. **Reliability**:
   - No empty page issues
   - No redirect problems
   - Consistent UI updates

3. **Security**:
   - Nonce verification
   - Permission checks
   - Sanitized inputs

4. **Error Handling**:
   - Network errors handled gracefully
   - Database errors reported clearly
   - Security token expiration handled

## Testing
Created comprehensive test suite (`tests/test-faq-ajax-toggle.php`) with 10 tests:
1. ✓ Verifies ajax_toggle_faq method exists
2. ✓ Verifies AJAX action is registered
3. ✓ Verifies AJAX toggle button exists in template
4. ✓ Verifies JavaScript AJAX handler exists
5. ✓ Verifies button type is "button" (not form submission)
6. ✓ Verifies nonce check in AJAX handler
7. ✓ Verifies permission check in AJAX handler
8. ✓ Verifies UI updates without page reload
9. ✓ Verifies success message is shown
10. ✓ Verifies error handling is implemented

All tests pass successfully!

## How to Use
1. Navigate to the FAQ admin page
2. Click the "Отключить" (Deactivate) or "Включить" (Activate) button
3. The status updates immediately without page reload
4. A success message appears at the top of the page
5. The button text and status cell update to reflect the new state

## Technical Details

### AJAX Request
```javascript
{
    action: 'aic_toggle_faq',
    nonce: aicAdmin.nonce,
    faq_id: faqId
}
```

### Success Response
```json
{
    "success": true,
    "data": {
        "message": "FAQ status updated",
        "is_active": 1
    }
}
```

### Error Response
```json
{
    "success": false,
    "data": {
        "message": "Error description",
        "code": "error_code"
    }
}
```

## Compatibility
- WordPress 5.0+
- jQuery (included in WordPress)
- Modern browsers (Chrome, Firefox, Safari, Edge)

## Security
- CSRF protection via nonce verification
- User capability checks (manage_options)
- Prepared SQL statements to prevent injection
- Sanitized user inputs
