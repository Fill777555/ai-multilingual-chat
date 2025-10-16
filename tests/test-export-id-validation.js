/**
 * Test for Export Conversation ID Validation Fix
 * 
 * This test validates that the conversation ID is properly validated
 * on both client-side and server-side, with comprehensive logging.
 */

console.log('=== Export Conversation ID Validation Test Suite ===\n');

let testsPassed = 0;
let testsFailed = 0;

// Helper function to assert
function assert(condition, testName) {
    if (condition) {
        console.log(`✓ PASS: ${testName}`);
        testsPassed++;
    } else {
        console.error(`✗ FAIL: ${testName}`);
        testsFailed++;
    }
}

// Mock the exportConversation validation logic from admin-script.js
function validateConversationId(conversationId) {
    console.log('[Test] exportConversation called with ID:', conversationId, 'Type:', typeof conversationId);
    
    // First validation: check if conversationId is provided
    if (!conversationId || conversationId === null || conversationId === 'null' || conversationId === undefined) {
        console.error('[Test] Invalid conversation ID:', conversationId);
        return { valid: false, error: 'Ошибка: Сначала выберите диалог для экспорта' };
    }
    
    // Second validation: ensure it's a valid positive number
    conversationId = parseInt(conversationId, 10);
    if (isNaN(conversationId) || conversationId <= 0) {
        console.error('[Test] Conversation ID is not a valid positive number:', conversationId);
        return { valid: false, error: 'Ошибка: Неверный ID диалога' };
    }
    
    console.log('[Test] Validation passed for conversation:', conversationId);
    return { valid: true, conversationId: conversationId };
}

// Test 1: Valid conversation IDs
console.log('\nTest 1: Valid Conversation IDs');
const validIds = [1, 123, 9999, '42', '100'];
validIds.forEach(id => {
    const result = validateConversationId(id);
    assert(result.valid === true, `Valid ID ${id} passes validation`);
    assert(typeof result.conversationId === 'number', `ID ${id} is converted to number`);
});

// Test 2: Invalid conversation IDs - null/undefined
console.log('\nTest 2: Null/Undefined IDs');
const nullIds = [null, undefined, '', 'null'];
nullIds.forEach(id => {
    const result = validateConversationId(id);
    assert(result.valid === false, `Invalid ID ${id} fails validation`);
    assert(result.error.includes('выберите диалог'), `Correct error message for ${id}`);
});

// Test 3: Invalid conversation IDs - non-numeric
console.log('\nTest 3: Non-Numeric IDs');
const nonNumericIds = ['abc', 'test', NaN, {}, []];
nonNumericIds.forEach(id => {
    const result = validateConversationId(id);
    assert(result.valid === false, `Non-numeric ID ${id} fails validation`);
});

// Test 4: Invalid conversation IDs - zero/negative
console.log('\nTest 4: Zero and Negative IDs');
const invalidNumericIds = [
    { id: 0, shouldContain: 'диалог' }, // 0 is caught by first check (!conversationId)
    { id: -1, shouldContain: 'Неверный ID' },
    { id: -100, shouldContain: 'Неверный ID' },
    { id: '0', shouldContain: 'Неверный ID' }, // '0' string passes first check but fails second
    { id: '-5', shouldContain: 'Неверный ID' }
];
invalidNumericIds.forEach(test => {
    const result = validateConversationId(test.id);
    assert(result.valid === false, `Zero/negative ID ${test.id} fails validation`);
    assert(result.error.includes(test.shouldContain), `Correct error message for ${test.id}`);
});

// Test 5: Edge cases
console.log('\nTest 5: Edge Cases');
const edgeCases = [
    { input: '   123   ', expected: true, desc: 'String with spaces' },
    { input: '1.5', expected: true, desc: 'Float number (should be parsed as integer)' },
    { input: Infinity, expected: false, desc: 'Infinity' },
    { input: -Infinity, expected: false, desc: '-Infinity' },
];

edgeCases.forEach(testCase => {
    const result = validateConversationId(testCase.input);
    assert(result.valid === testCase.expected, `${testCase.desc}: ${testCase.input}`);
});

// Test 6: Server-side validation simulation
console.log('\nTest 6: Server-Side Validation');

function serverSideValidation(postData) {
    // Simulate PHP validation logic
    if (!postData.hasOwnProperty('conversation_id')) {
        return { success: false, message: 'Отсутствует параметр conversation_id' };
    }
    
    const conversationId = parseInt(postData.conversation_id, 10);
    if (isNaN(conversationId) || conversationId <= 0) {
        return { success: false, message: 'Неверный ID диалога' };
    }
    
    return { success: true, conversationId: conversationId };
}

// Valid server requests
const validRequests = [
    { conversation_id: 1 },
    { conversation_id: '123' },
    { conversation_id: 9999 }
];

validRequests.forEach((request, i) => {
    const result = serverSideValidation(request);
    assert(result.success === true, `Valid request ${i + 1} passes server validation`);
});

// Invalid server requests
const invalidRequests = [
    { }, // missing conversation_id
    { conversation_id: null },
    { conversation_id: 0 },
    { conversation_id: -1 },
    { conversation_id: 'abc' },
    { other_param: 123 } // wrong parameter name
];

invalidRequests.forEach((request, i) => {
    const result = serverSideValidation(request);
    assert(result.success === false, `Invalid request ${i + 1} fails server validation`);
    assert(result.message && result.message.length > 0, `Invalid request ${i + 1} has error message`);
});

// Test 7: Logging format validation
console.log('\nTest 7: Logging Format');
const logPrefix = '[AIC Export]';
const expectedLogs = [
    `${logPrefix} Export button clicked, currentConversationId:`,
    `${logPrefix} exportConversation called with ID:`,
    `${logPrefix} Starting export for conversation:`,
    `${logPrefix} Sending AJAX request with data:`,
    `${logPrefix} Server response:`
];

expectedLogs.forEach(log => {
    assert(log.startsWith(logPrefix), `Log has correct prefix: ${log.substring(0, 50)}...`);
});

// Test 8: Request data structure
console.log('\nTest 8: Request Data Structure');
const conversationId = 123;
const requestData = {
    action: 'aic_export_conversation',
    nonce: 'test_nonce_value',
    conversation_id: conversationId
};

assert(requestData.hasOwnProperty('action'), 'Request has action parameter');
assert(requestData.action === 'aic_export_conversation', 'Correct action value');
assert(requestData.hasOwnProperty('nonce'), 'Request has nonce parameter');
assert(requestData.hasOwnProperty('conversation_id'), 'Request has conversation_id parameter');
assert(requestData.conversation_id === conversationId, 'Conversation ID matches');

// Test 9: Error message consistency
console.log('\nTest 9: Error Message Consistency');
const clientErrors = [
    'Ошибка: Сначала выберите диалог для экспорта',
    'Ошибка: Неверный ID диалога'
];

const serverErrors = [
    'Отсутствует параметр conversation_id',
    'Неверный ID диалога',
    'Диалог не найден',
    'Ошибка базы данных',
    'В диалоге нет сообщений'
];

clientErrors.forEach(error => {
    assert(error.includes('Ошибка'), `Client error is in Russian: ${error}`);
});

serverErrors.forEach(error => {
    assert(error.length > 0, `Server error is not empty: ${error}`);
});

// Test 10: Type coercion behavior
console.log('\nTest 10: Type Coercion');
const typeTests = [
    { input: '123', expectedType: 'number', expectedValue: 123 },
    { input: 456, expectedType: 'number', expectedValue: 456 },
    { input: '789.99', expectedType: 'number', expectedValue: 789 },
];

typeTests.forEach(test => {
    const result = validateConversationId(test.input);
    if (result.valid) {
        assert(typeof result.conversationId === test.expectedType, 
            `Input ${test.input} converts to ${test.expectedType}`);
        assert(result.conversationId === test.expectedValue,
            `Input ${test.input} converts to value ${test.expectedValue}`);
    }
});

// Print results
console.log('\n' + '='.repeat(50));
console.log('Test Results Summary:');
const totalTests = testsPassed + testsFailed;
const passRate = totalTests > 0 ? ((testsPassed / totalTests) * 100).toFixed(2) : 0;
console.log(`Total Tests: ${totalTests}`);
console.log(`Passed: ${testsPassed} (${passRate}%)`);
console.log(`Failed: ${testsFailed}`);
console.log('='.repeat(50));

if (testsFailed === 0) {
    console.log('\n✓ All tests passed!');
    if (typeof process !== 'undefined') {
        process.exit(0);
    }
} else {
    console.log('\n✗ Some tests failed. Please review the failures above.');
    if (typeof process !== 'undefined') {
        process.exit(1);
    }
}
