# AI Multilingual Chat - Translation Tests

This directory contains comprehensive tests for the AI translation functionality in the AI Multilingual Chat plugin.

## Files

### JavaScript Tests (Admin Interface)

#### `test-input-preservation.js`
Tests the basic input preservation functionality during HTML updates.

**Features:**
- Tests save/restore mechanism for textarea value
- Validates that text is not lost during HTML replacement
- Compares old vs new implementation

**Usage:**
```bash
node test-input-preservation.js
```

**Test Scenarios:**
- Old version (without fix): Text is lost ❌
- New version (with fix): Text is preserved ✅

#### `test-focus-preservation.js` ⭐ NEW
Tests the enhanced focus-aware update mechanism.

**Features:**
- Tests that updates are skipped when textarea is focused
- Validates cursor position and focus preservation
- Tests normal updates when field is not focused

**Usage:**
```bash
node test-focus-preservation.js
```

**Test Scenarios:**
1. Input NOT focused → Normal update with text preservation ✅
2. Input IS focused → Update skipped, focus preserved ✅
3. After blur → Update performed on next poll ✅

### PHP Tests (Translation and API)

#### 1. `test-translation.php`
Main test script that performs actual API calls to test translation functionality.

**Features:**
- Tests 15 different translation scenarios
- Supports all three AI providers (OpenAI, Anthropic, Google)
- Measures translation performance and accuracy
- Generates detailed test reports

**Usage:**
```bash
# Make sure WordPress environment is loaded
php test-translation.php
```

**Requirements:**
- WordPress installation
- AI Multilingual Chat plugin activated
- Valid API key configured in plugin settings

#### 2. `test-runner-demo.php`
Demonstration script that shows all test scenarios without making actual API calls.

**Features:**
- Displays all 15 test scenarios
- Shows expected translations
- Generates test matrix CSV file
- No API key required

**Usage:**
```bash
php test-runner-demo.php
```

#### 3. `test-api-key-filtering.php`
Tests API key detection and filtering to prevent sensitive data leakage.

**Features:**
- Tests detection of various API key formats (OpenAI, Google, etc.)
- Validates that API keys are not sent to translation services
- Tests normal text is not blocked

**Usage:**
```bash
php test-api-key-filtering.php
```

**Test Results:**
- 7 API key detection tests ✅
- 6 normal text tests ✅
- 100% success rate

### 4. `TRANSLATION_TESTING_REPORT.md`
Comprehensive testing documentation including:
- Test plan and methodology
- Code analysis
- Identified issues and recommendations
- Example translations
- Improvement suggestions

## Test Coverage

### Language Pairs Tested
- English → Russian (8 tests)
- Russian → English (2 tests)
- English → Ukrainian (1 test)
- Russian → Ukrainian (1 test)
- English → German (1 test)
- English → French (1 test)
- English → Spanish (1 test)

### Test Categories
1. **Simple greetings** - Basic conversational phrases
2. **E-commerce** - Shopping and order-related text
3. **Customer support** - Support inquiries and responses
4. **Technical support** - Technical terminology and issues
5. **Complex inquiries** - Long, multi-clause sentences
6. **Technical terminology** - API, software terms
7. **Emotional expressions** - Punctuation, emoticons
8. **Numbers and measurements** - Numeric data preservation
9. **Multi-sentence texts** - Paragraph-level translation

## Quick Start

### Running Demo (No API Required)
```bash
cd /path/to/wordpress/wp-content/plugins/ai-multilingual-chat/tests
php test-runner-demo.php
```

### Running Full Tests (API Required)
1. Configure API key in WordPress admin
2. Navigate to plugin directory:
   ```bash
   cd /path/to/wordpress/wp-content/plugins/ai-multilingual-chat
   ```
3. Run tests:
   ```bash
   php tests/test-translation.php
   ```

## Test Results Format

### Console Output
The test script provides real-time console output showing:
- Test case number and category
- Original text and translation direction
- Translated text
- Translation duration in milliseconds
- Pass/fail status with visual indicators (✅/❌/⚠️)

### Generated Files

#### `translation-test-report.md`
Markdown report containing:
- Test metadata (provider, date, total cases)
- Results table with all translations
- Issues and errors section
- Recommendations
- Conclusion

#### `test-matrix.csv`
CSV file with all test scenarios for easy import into spreadsheets.

## Interpreting Results

### Status Indicators
- ✅ **PASSED** - Translation contains expected keywords and appears correct
- ❌ **FAILED** - Translation failed to generate or threw an exception
- ⚠️ **WARNING** - Translation generated but may have issues (identical to original, missing keywords)

### Success Criteria
- **Excellent**: >90% pass rate
- **Good**: 70-90% pass rate
- **Needs Improvement**: <70% pass rate

## Common Issues

### Issue: "API key is not configured"
**Solution:** Configure API key in WordPress Admin → Чат → Настройки

### Issue: "OpenAI API error: Rate limit exceeded"
**Solution:** Wait a few minutes and try again, or upgrade API plan

### Issue: "Invalid OpenAI response"
**Solution:** Check API key validity and internet connection

### Issue: "Timeout error"
**Solution:** Increase timeout value in plugin code or check network connectivity

## Adding New Tests

To add new test cases, edit `test-translation.php` and add to the `init_test_cases()` method:

```php
array(
    'from_lang' => 'en',
    'to_lang' => 'ru',
    'text' => 'Your test text here',
    'expected_keywords' => array('keyword1', 'keyword2'),
    'category' => 'Your Category'
)
```

## Performance Benchmarks

Expected translation times:
- **Simple phrases** (1-10 words): 500-1500ms
- **Medium sentences** (10-30 words): 1000-2500ms
- **Complex paragraphs** (30+ words): 2000-4000ms

Note: Times vary based on provider, API load, and network conditions.

## Provider Comparison

### OpenAI (GPT-3.5-turbo)
- **Languages**: 10 (most comprehensive)
- **Quality**: Excellent
- **Speed**: Fast
- **Cost**: Moderate

### Anthropic (Claude-3-haiku)
- **Languages**: 6
- **Quality**: Excellent
- **Speed**: Fast
- **Cost**: Competitive

### Google AI (Gemini Pro)
- **Languages**: 6
- **Quality**: Very Good
- **Speed**: Fast
- **Cost**: Lower

## Continuous Testing

For CI/CD integration, consider:
1. Mocking API responses for unit tests
2. Running integration tests on staging only
3. Implementing cost limits for test runs
4. Caching test results

## Support

For issues or questions:
1. Review `TRANSLATION_TESTING_REPORT.md` for detailed analysis
2. Check plugin logs for error messages
3. Verify API key and provider configuration

## License

This testing suite is part of the AI Multilingual Chat plugin and follows the same GPL-3.0 license.
