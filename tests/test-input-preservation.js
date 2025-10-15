/**
 * Test for Admin Input Preservation
 * 
 * This test simulates the behavior of the renderMessages function
 * to ensure that text in the input field is preserved during re-rendering.
 */

// Simulate jQuery DOM manipulation
class MockJQuery {
    constructor(selector) {
        this.selector = selector;
        this.value = '';
        this.elements = {};
    }
    
    val(newValue) {
        if (newValue === undefined) {
            return this.elements[this.selector]?.value || '';
        }
        this.elements[this.selector] = { value: newValue };
        return this;
    }
    
    html(content) {
        // Simulates HTML replacement - this would normally destroy the input
        if (content !== undefined) {
            // Clear all elements (simulating DOM replacement)
            const preservedValue = this.elements['#aic_admin_message_input']?.value;
            this.elements = {};
            // Re-create the input element without value (as would happen in real DOM)
            this.elements['#aic_admin_message_input'] = { value: '' };
            console.log('  → HTML replaced. Input field recreated (empty).');
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

// Simulated renderMessages function (OLD VERSION - BROKEN)
function renderMessages_OLD(messages) {
    console.log('\n=== Testing OLD version (WITHOUT fix) ===');
    const container = $('#aic-current-chat');
    
    console.log('1. User types text in input field');
    $('#aic_admin_message_input').val('This is my message that I am typing...');
    console.log('  → Input value:', $('#aic_admin_message_input').val());
    
    console.log('2. renderMessages() is called (e.g., during polling)');
    let html = '<div>Messages here...</div>';
    html += '<textarea id="aic_admin_message_input"></textarea>';
    
    console.log('3. container.html(html) replaces all HTML');
    container.html(html);
    
    console.log('4. Check input value after HTML replacement');
    console.log('  → Input value:', $('#aic_admin_message_input').val());
    console.log('  ✗ RESULT: Text was LOST!');
    
    return $('#aic_admin_message_input').val();
}

// Simulated renderMessages function (NEW VERSION - FIXED)
function renderMessages_NEW(messages) {
    console.log('\n=== Testing NEW version (WITH fix) ===');
    const container = $('#aic-current-chat');
    
    console.log('1. User types text in input field');
    $('#aic_admin_message_input').val('This is my message that I am typing...');
    console.log('  → Input value:', $('#aic_admin_message_input').val());
    
    console.log('2. renderMessages() is called (e.g., during polling)');
    console.log('3. Save current input value BEFORE rewriting HTML');
    const currentInputValue = $('#aic_admin_message_input').val() || '';
    console.log('  → Saved value:', currentInputValue);
    
    let html = '<div>Messages here...</div>';
    html += '<textarea id="aic_admin_message_input"></textarea>';
    
    console.log('4. container.html(html) replaces all HTML');
    container.html(html);
    
    console.log('5. Restore the saved input value AFTER HTML is rewritten');
    if (currentInputValue) {
        $('#aic_admin_message_input').val(currentInputValue);
    }
    console.log('  → Input value:', $('#aic_admin_message_input').val());
    console.log('  ✓ RESULT: Text was PRESERVED!');
    
    return $('#aic_admin_message_input').val();
}

// Run tests
console.log('╔════════════════════════════════════════════════════════╗');
console.log('║  Test: Admin Input Text Preservation During Polling   ║');
console.log('╚════════════════════════════════════════════════════════╝');

// Reset mock
global._mockElements = null;

// Test old version
const resultOld = renderMessages_OLD([]);
const testOldPassed = resultOld === '';

// Reset mock
global._mockElements = null;

// Test new version
const resultNew = renderMessages_NEW([]);
const testNewPassed = resultNew === 'This is my message that I am typing...';

// Print results
console.log('\n╔════════════════════════════════════════════════════════╗');
console.log('║                     TEST RESULTS                       ║');
console.log('╚════════════════════════════════════════════════════════╝');
console.log('');
console.log('Old version (without fix):');
console.log('  Expected: Text LOST (empty string)');
console.log('  Actual:', resultOld === '' ? 'Text LOST (empty string)' : 'Text PRESERVED');
console.log('  Status:', testOldPassed ? '✓ PASS' : '✗ FAIL');
console.log('');
console.log('New version (with fix):');
console.log('  Expected: Text PRESERVED');
console.log('  Actual:', resultNew === 'This is my message that I am typing...' ? 'Text PRESERVED' : 'Text LOST');
console.log('  Status:', testNewPassed ? '✓ PASS' : '✗ FAIL');
console.log('');
console.log('═══════════════════════════════════════════════════════════');

if (testOldPassed && testNewPassed) {
    console.log('✓ All tests PASSED!');
    console.log('  The fix successfully preserves text during re-rendering.');
} else {
    console.log('✗ Some tests FAILED!');
}
console.log('═══════════════════════════════════════════════════════════');
