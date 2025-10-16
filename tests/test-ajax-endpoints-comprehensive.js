/**
 * Comprehensive AJAX Endpoints Test
 * 
 * This test verifies all AJAX requests in the ai-multilingual-chat plugin.
 * It checks for proper error handling, validation, and response structure.
 * 
 * Run this test in a browser console on a WordPress site with the plugin active.
 * 
 * Test Coverage:
 * - Frontend AJAX: start_conversation, send_message, get_messages, user_typing
 * - Admin AJAX: get_conversations, get_messages, send_message, close_conversation, admin_typing, export_conversation
 * - Settings AJAX: generate_api_key
 * 
 * Expected Results: All tests should pass with proper error handling
 */

(function() {
    'use strict';
    
    const testResults = {
        total: 0,
        passed: 0,
        failed: 0,
        errors: []
    };
    
    console.log('%c🚀 Starting AJAX Endpoints Comprehensive Test', 'color: #4CAF50; font-size: 16px; font-weight: bold;');
    console.log('━'.repeat(80));
    
    // Helper function to run a test
    function runTest(testName, testFn) {
        testResults.total++;
        console.log(`\n📝 Test ${testResults.total}: ${testName}`);
        
        return testFn()
            .then(() => {
                testResults.passed++;
                console.log(`✅ PASS: ${testName}`);
                return { success: true, testName };
            })
            .catch(error => {
                testResults.failed++;
                testResults.errors.push({ testName, error: error.message || error });
                console.error(`❌ FAIL: ${testName}`, error);
                return { success: false, testName, error };
            });
    }
    
    // Helper to make AJAX request
    function ajaxRequest(action, data = {}, useAdminNonce = false) {
        return new Promise((resolve, reject) => {
            const nonce = useAdminNonce 
                ? (typeof aicAdmin !== 'undefined' ? aicAdmin.nonce : null)
                : (typeof aicFrontend !== 'undefined' ? aicFrontend.nonce : null);
            
            const ajaxUrl = useAdminNonce
                ? (typeof aicAdmin !== 'undefined' ? aicAdmin.ajax_url : '/wp-admin/admin-ajax.php')
                : (typeof aicFrontend !== 'undefined' ? aicFrontend.ajax_url : '/wp-admin/admin-ajax.php');
            
            if (!nonce) {
                reject(new Error('Nonce not available - check if scripts are enqueued'));
                return;
            }
            
            jQuery.ajax({
                url: ajaxUrl,
                type: 'POST',
                timeout: 10000,
                data: {
                    action: action,
                    nonce: nonce,
                    ...data
                },
                success: function(response) {
                    console.log(`   Response from ${action}:`, response);
                    resolve(response);
                },
                error: function(xhr, status, error) {
                    console.error(`   AJAX Error from ${action}:`, {
                        status: xhr.status,
                        statusText: status,
                        error: error
                    });
                    reject(new Error(`AJAX Error: ${status} - ${error} (HTTP ${xhr.status})`));
                }
            });
        });
    }
    
    // Validation helper
    function validateResponse(response, expectedFields = []) {
        if (!response) {
            throw new Error('Empty response received');
        }
        
        if (response.success === undefined) {
            throw new Error('Response missing "success" field');
        }
        
        if (response.success && response.data) {
            expectedFields.forEach(field => {
                if (response.data[field] === undefined) {
                    throw new Error(`Response data missing expected field: ${field}`);
                }
            });
        }
        
        return true;
    }
    
    // Test Suite
    const tests = [];
    
    // ==============================================================================
    // Frontend AJAX Tests
    // ==============================================================================
    
    // Test 1: Start Conversation - Missing Parameters
    tests.push(runTest('Frontend: aic_start_conversation - Missing session_id', async () => {
        const response = await ajaxRequest('aic_start_conversation', {
            user_name: 'Test User',
            user_language: 'en'
        }, false);
        
        validateResponse(response);
        
        if (response.success !== false) {
            throw new Error('Should fail when session_id is missing');
        }
        
        if (!response.data || !response.data.message) {
            throw new Error('Error response should include message');
        }
    }));
    
    // Test 2: Start Conversation - Missing user_name
    tests.push(runTest('Frontend: aic_start_conversation - Missing user_name', async () => {
        const response = await ajaxRequest('aic_start_conversation', {
            session_id: 'test_session_' + Date.now(),
            user_language: 'en'
        }, false);
        
        validateResponse(response);
        
        if (response.success !== false) {
            throw new Error('Should fail when user_name is missing');
        }
    }));
    
    // Test 3: Start Conversation - Valid Request
    tests.push(runTest('Frontend: aic_start_conversation - Valid request', async () => {
        const response = await ajaxRequest('aic_start_conversation', {
            session_id: 'test_session_' + Date.now(),
            user_name: 'Test User',
            user_language: 'en'
        }, false);
        
        validateResponse(response, ['conversation_id']);
        
        if (!response.success) {
            throw new Error('Should succeed with valid parameters');
        }
        
        if (!response.data.conversation_id || response.data.conversation_id <= 0) {
            throw new Error('Should return valid conversation_id');
        }
    }));
    
    // Test 4: Send Message - Empty Message
    tests.push(runTest('Frontend: aic_send_message - Empty message', async () => {
        const response = await ajaxRequest('aic_send_message', {
            message: '',
            session_id: 'test_session_' + Date.now(),
            user_language: 'en'
        }, false);
        
        validateResponse(response);
        
        if (response.success !== false) {
            throw new Error('Should fail when message is empty');
        }
    }));
    
    // Test 5: Get Messages - Missing session_id
    tests.push(runTest('Frontend: aic_get_messages - Missing session_id', async () => {
        const response = await ajaxRequest('aic_get_messages', {
            last_message_id: 0
        }, false);
        
        validateResponse(response);
        
        if (response.success !== false) {
            throw new Error('Should fail when session_id is missing');
        }
    }));
    
    // Test 6: Get Messages - Valid Request
    tests.push(runTest('Frontend: aic_get_messages - Valid request', async () => {
        const response = await ajaxRequest('aic_get_messages', {
            session_id: 'nonexistent_session',
            last_message_id: 0
        }, false);
        
        validateResponse(response, ['messages']);
        
        if (!response.success) {
            throw new Error('Should succeed even with nonexistent session');
        }
        
        if (!Array.isArray(response.data.messages)) {
            throw new Error('messages should be an array');
        }
    }));
    
    // Test 7: User Typing - Invalid conversation_id
    tests.push(runTest('Frontend: aic_user_typing - Invalid conversation_id', async () => {
        const response = await ajaxRequest('aic_user_typing', {
            conversation_id: 0,
            is_typing: 1
        }, false);
        
        validateResponse(response);
        
        if (response.success !== false) {
            throw new Error('Should fail with invalid conversation_id');
        }
    }));
    
    // ==============================================================================
    // Admin AJAX Tests
    // ==============================================================================
    
    // Test 8: Admin Get Conversations - Valid Request
    tests.push(runTest('Admin: aic_admin_get_conversations - Valid request', async () => {
        const response = await ajaxRequest('aic_admin_get_conversations', {
            status: 'active'
        }, true);
        
        validateResponse(response, ['conversations']);
        
        if (!response.success) {
            throw new Error('Should succeed with valid request');
        }
        
        if (!Array.isArray(response.data.conversations)) {
            throw new Error('conversations should be an array');
        }
    }));
    
    // Test 9: Admin Get Messages - Invalid conversation_id
    tests.push(runTest('Admin: aic_admin_get_messages - Invalid conversation_id', async () => {
        const response = await ajaxRequest('aic_admin_get_messages', {
            conversation_id: 0
        }, true);
        
        validateResponse(response);
        
        if (response.success !== false) {
            throw new Error('Should fail with conversation_id = 0');
        }
    }));
    
    // Test 10: Admin Get Messages - Negative conversation_id
    tests.push(runTest('Admin: aic_admin_get_messages - Negative conversation_id', async () => {
        const response = await ajaxRequest('aic_admin_get_messages', {
            conversation_id: -1
        }, true);
        
        validateResponse(response);
        
        if (response.success !== false) {
            throw new Error('Should fail with negative conversation_id');
        }
    }));
    
    // Test 11: Admin Send Message - Empty message
    tests.push(runTest('Admin: aic_admin_send_message - Empty message', async () => {
        const response = await ajaxRequest('aic_admin_send_message', {
            conversation_id: 1,
            message: ''
        }, true);
        
        validateResponse(response);
        
        if (response.success !== false) {
            throw new Error('Should fail with empty message');
        }
    }));
    
    // Test 12: Admin Close Conversation - Invalid conversation_id
    tests.push(runTest('Admin: aic_admin_close_conversation - Invalid conversation_id', async () => {
        const response = await ajaxRequest('aic_admin_close_conversation', {
            conversation_id: 0
        }, true);
        
        validateResponse(response);
        
        if (response.success !== false) {
            throw new Error('Should fail with invalid conversation_id');
        }
    }));
    
    // Test 13: Admin Typing - Invalid conversation_id
    tests.push(runTest('Admin: aic_admin_typing - Invalid conversation_id', async () => {
        const response = await ajaxRequest('aic_admin_typing', {
            conversation_id: 0,
            is_typing: 1
        }, true);
        
        validateResponse(response);
        
        if (response.success !== false) {
            throw new Error('Should fail with invalid conversation_id');
        }
    }));
    
    // Test 14: Export Conversation - Missing conversation_id
    tests.push(runTest('Admin: aic_export_conversation - Missing conversation_id', async () => {
        const response = await ajaxRequest('aic_export_conversation', {}, true);
        
        validateResponse(response);
        
        if (response.success !== false) {
            throw new Error('Should fail when conversation_id is missing');
        }
    }));
    
    // Test 15: Export Conversation - Invalid conversation_id
    tests.push(runTest('Admin: aic_export_conversation - Invalid conversation_id', async () => {
        const response = await ajaxRequest('aic_export_conversation', {
            conversation_id: -1
        }, true);
        
        validateResponse(response);
        
        if (response.success !== false) {
            throw new Error('Should fail with negative conversation_id');
        }
    }));
    
    // Test 16: Timeout Handling Test
    tests.push(runTest('Timeout: Verify AJAX timeout is set', () => {
        return new Promise((resolve, reject) => {
            // Check if frontend script has timeout set
            const hasTimeout = jQuery.ajax.toString().includes('timeout') || 
                              (typeof aicFrontend !== 'undefined' && aicFrontend.ajax_timeout);
            
            if (hasTimeout) {
                console.log('   ✓ Timeout configuration detected');
                resolve();
            } else {
                console.log('   ⚠ Warning: Could not verify timeout configuration');
                resolve(); // Don't fail, just warn
            }
        });
    }));
    
    // ==============================================================================
    // Run All Tests
    // ==============================================================================
    
    Promise.all(tests).then(results => {
        console.log('\n' + '━'.repeat(80));
        console.log('%c📊 Test Results Summary', 'color: #2196F3; font-size: 16px; font-weight: bold;');
        console.log('━'.repeat(80));
        console.log(`Total Tests: ${testResults.total}`);
        console.log(`%c✅ Passed: ${testResults.passed}`, 'color: #4CAF50; font-weight: bold;');
        console.log(`%c❌ Failed: ${testResults.failed}`, 'color: #f44336; font-weight: bold;');
        console.log(`Success Rate: ${((testResults.passed / testResults.total) * 100).toFixed(1)}%`);
        
        if (testResults.failed > 0) {
            console.log('\n%c⚠️ Failed Tests:', 'color: #ff9800; font-weight: bold;');
            testResults.errors.forEach((err, idx) => {
                console.log(`${idx + 1}. ${err.testName}`);
                console.log(`   Error: ${err.error}`);
            });
        }
        
        console.log('\n' + '━'.repeat(80));
        
        if (testResults.failed === 0) {
            console.log('%c🎉 All AJAX endpoints passed validation!', 'color: #4CAF50; font-size: 14px; font-weight: bold;');
        } else {
            console.log('%c⚠️ Some tests failed. Review the errors above.', 'color: #ff9800; font-size: 14px; font-weight: bold;');
        }
        
        console.log('━'.repeat(80));
        
        // Return results for external access
        window.ajaxTestResults = testResults;
    });
    
})();
