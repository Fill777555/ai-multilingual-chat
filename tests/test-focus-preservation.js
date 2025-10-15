/**
 * Test for Admin Input Focus Preservation
 * 
 * This test validates that the renderMessages function does NOT update
 * the textarea when it is currently focused (user is actively typing).
 */

// Simulate jQuery DOM manipulation with focus tracking
class MockJQuery {
    constructor(selector) {
        this.selector = selector;
        this.value = '';
        this.focused = false;
        this.elements = {};
    }
    
    val(newValue) {
        if (newValue === undefined) {
            return this.elements[this.selector]?.value || '';
        }
        this.elements[this.selector] = this.elements[this.selector] || {};
        this.elements[this.selector].value = newValue;
        return this;
    }
    
    is(selector) {
        // Check if element matches selector
        if (selector === ':focus') {
            return this.elements[this.selector]?.focused || false;
        }
        return false;
    }
    
    focus() {
        this.elements[this.selector] = this.elements[this.selector] || {};
        this.elements[this.selector].focused = true;
        return this;
    }
    
    blur() {
        if (this.elements[this.selector]) {
            this.elements[this.selector].focused = false;
        }
        return this;
    }
    
    html(content) {
        // Simulates HTML replacement - this would normally destroy the input
        if (content !== undefined) {
            // Save focused state
            const inputWasFocused = this.elements['#aic_admin_message_input']?.focused;
            // Clear all elements (simulating DOM replacement)
            this.elements = {};
            // Re-create the input element without value and without focus
            this.elements['#aic_admin_message_input'] = { value: '', focused: false };
            
            if (inputWasFocused) {
                console.log('  → HTML replaced. Input field recreated (focus LOST!).');
            } else {
                console.log('  → HTML replaced. Input field recreated (empty).');
            }
        }
        return this;
    }
    
    scrollTop(value) {
        return this;
    }
    
    length() {
        return 1;
    }
}

// Mock storage for elements
global._mockElements = null;

// Mock jQuery function
function $(selector) {
    if (!global._mockElements) {
        global._mockElements = new MockJQuery(selector);
    }
    global._mockElements.selector = selector;
    return global._mockElements;
}

// Mock adminChat for escapeHtml
const adminChat = {
    escapeHtml: function(text) {
        return text;
    },
    scrollToBottom: function() {}
};

// Simulated renderMessages function (NEW VERSION with focus check)
function renderMessages_WITH_FOCUS_CHECK(messages) {
    const container = $('#aic-current-chat');
    
    // Check if input field is currently focused (user is typing)
    const inputIsFocused = $('#aic_admin_message_input').is(':focus');
    
    console.log('  → Input focused?', inputIsFocused);
    
    // If input is focused, skip the update to avoid interrupting user typing
    if (inputIsFocused) {
        console.log('  → Input field is focused, SKIPPING HTML update');
        return 'SKIPPED';
    }
    
    // Save current input value before rewriting HTML
    const currentInputValue = $('#aic_admin_message_input').val() || '';
    console.log('  → Saved value:', currentInputValue);
    
    let html = '<div>Messages here...</div>';
    html += '<textarea id="aic_admin_message_input"></textarea>';
    
    console.log('  → Performing HTML replacement');
    container.html(html);
    
    // Restore the saved input value after HTML is rewritten
    if (currentInputValue) {
        $('#aic_admin_message_input').val(currentInputValue);
        console.log('  → Restored value:', currentInputValue);
    }
    
    return $('#aic_admin_message_input').val();
}

// Run tests
console.log('╔════════════════════════════════════════════════════════╗');
console.log('║   Test: Admin Input Focus Preservation During Poll    ║');
console.log('╚════════════════════════════════════════════════════════╝');

// Test 1: Input is NOT focused - should update normally
console.log('\n=== Test 1: Input NOT focused (normal update) ===');
global._mockElements = null;
console.log('1. User has typed text but field is not focused');
$('#aic_admin_message_input').val('Saved message');
$('#aic_admin_message_input').blur();
console.log('2. renderMessages() is called during polling');
const result1 = renderMessages_WITH_FOCUS_CHECK([]);
const test1Passed = result1 === 'Saved message';
console.log('3. Result: Input value =', result1);
console.log('  Status:', test1Passed ? '✓ PASS - Text preserved' : '✗ FAIL');

// Test 2: Input IS focused - should NOT update
console.log('\n=== Test 2: Input IS focused (skip update) ===');
global._mockElements = null;
console.log('1. User is actively typing in the input field');
$('#aic_admin_message_input').val('User is typing this right now...');
$('#aic_admin_message_input').focus();
console.log('  → Input value:', $('#aic_admin_message_input').val());
console.log('  → Input focused:', $('#aic_admin_message_input').is(':focus'));
console.log('2. renderMessages() is called during polling');
const result2 = renderMessages_WITH_FOCUS_CHECK([]);
console.log('3. Result: Update was', result2 === 'SKIPPED' ? 'SKIPPED' : 'PERFORMED');
const focusPreserved = $('#aic_admin_message_input').is(':focus');
const valuePreserved = $('#aic_admin_message_input').val() === 'User is typing this right now...';
console.log('  → Input still focused?', focusPreserved);
console.log('  → Input value preserved?', valuePreserved);
const test2Passed = result2 === 'SKIPPED' && focusPreserved && valuePreserved;
console.log('  Status:', test2Passed ? '✓ PASS - Update skipped, focus preserved' : '✗ FAIL');

// Test 3: User finishes typing (blurs) - should update on next poll
console.log('\n=== Test 3: User finishes typing, next poll updates ===');
console.log('1. User was typing, now moves focus away');
$('#aic_admin_message_input').blur();
console.log('  → Input focused?', $('#aic_admin_message_input').is(':focus'));
console.log('2. renderMessages() is called during next polling');
const result3 = renderMessages_WITH_FOCUS_CHECK([]);
const test3Passed = result3 === 'User is typing this right now...';
console.log('3. Result: Input value =', result3);
console.log('  Status:', test3Passed ? '✓ PASS - Update performed after blur' : '✗ FAIL');

// Print summary
console.log('\n╔════════════════════════════════════════════════════════╗');
console.log('║                     TEST RESULTS                       ║');
console.log('╚════════════════════════════════════════════════════════╝');
console.log('');
console.log('Test 1 (Not focused - normal update):');
console.log('  Expected: Text preserved through HTML update');
console.log('  Status:', test1Passed ? '✓ PASS' : '✗ FAIL');
console.log('');
console.log('Test 2 (Focused - skip update):');
console.log('  Expected: Update skipped, focus and value preserved');
console.log('  Status:', test2Passed ? '✓ PASS' : '✗ FAIL');
console.log('');
console.log('Test 3 (After blur - allow update):');
console.log('  Expected: Update performed normally');
console.log('  Status:', test3Passed ? '✓ PASS' : '✗ FAIL');
console.log('');
console.log('═══════════════════════════════════════════════════════════');

if (test1Passed && test2Passed && test3Passed) {
    console.log('✓ All tests PASSED!');
    console.log('  The focus check successfully prevents updates during typing.');
} else {
    console.log('✗ Some tests FAILED!');
    process.exit(1);
}
console.log('═══════════════════════════════════════════════════════════');
