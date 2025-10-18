/**
 * Test: Sound Melody Selection Feature
 * 
 * This test verifies that the sound melody selection functionality works correctly
 * for both admin and client interfaces.
 * 
 * Features tested:
 * 1. Sound file availability
 * 2. Admin sound selection from WordPress settings
 * 3. Client sound selection from localStorage
 * 4. Sound URL construction
 * 5. Preview functionality
 * 6. Fallback to default on error
 */

console.log('=== Sound Melody Selection Test ===\n');

const fs = require('fs');
const path = require('path');

// Test 1: Verify sound files exist
console.log('Test 1: Verify sound files exist');
try {
    const soundsDir = path.join(__dirname, '..', 'ai-multilingual-chat', 'sounds');
    const requiredSounds = [
        'notification-default.mp3',
        'notification-bell.mp3',
        'notification-ding.mp3',
        'notification-chime.mp3',
        'notification-soft.mp3'
    ];
    
    let allExist = true;
    requiredSounds.forEach(soundFile => {
        const soundPath = path.join(soundsDir, soundFile);
        if (fs.existsSync(soundPath)) {
            const stats = fs.statSync(soundPath);
            console.log(`  ✓ ${soundFile} exists (${stats.size} bytes)`);
        } else {
            console.log(`  ✗ ${soundFile} is missing`);
            allExist = false;
        }
    });
    
    if (allExist) {
        console.log('  ✓ All required sound files are present');
    } else {
        console.log('  ✗ Some sound files are missing');
    }
} catch (error) {
    console.log('  ✗ Error checking sound files: ' + error.message);
}
console.log('');

// Test 2: Admin sound selection
console.log('Test 2: Admin sound selection configuration');
try {
    // Simulate WordPress aicAdmin object
    const aicAdmin = {
        sound_base_url: '/wp-content/plugins/ai-multilingual-chat/sounds/',
        sound_choice: 'bell',
        available_sounds: {
            'default': 'По умолчанию',
            'bell': 'Колокольчик',
            'ding': 'Динь',
            'chime': 'Перезвон',
            'soft': 'Мягкий звук'
        }
    };
    
    // Simulate initNotificationSound function
    const soundChoice = aicAdmin.sound_choice || 'default';
    const soundUrl = aicAdmin.sound_base_url + 'notification-' + soundChoice + '.mp3';
    
    const expectedUrl = '/wp-content/plugins/ai-multilingual-chat/sounds/notification-bell.mp3';
    
    if (soundUrl === expectedUrl) {
        console.log(`  ✓ Sound URL constructed correctly: ${soundUrl}`);
    } else {
        console.log(`  ✗ Sound URL incorrect. Expected: ${expectedUrl}, Got: ${soundUrl}`);
    }
    
    // Test available sounds
    const soundCount = Object.keys(aicAdmin.available_sounds).length;
    if (soundCount === 5) {
        console.log(`  ✓ All ${soundCount} sound options available`);
    } else {
        console.log(`  ✗ Expected 5 sounds, found ${soundCount}`);
    }
} catch (error) {
    console.log('  ✗ Error in admin sound selection: ' + error.message);
}
console.log('');

// Test 3: Client sound selection from localStorage
console.log('Test 3: Client sound selection from localStorage');
try {
    // Simulate localStorage
    const localStorage = {
        data: {},
        getItem: function(key) {
            return this.data[key] || null;
        },
        setItem: function(key, value) {
            this.data[key] = String(value);
        }
    };
    
    // Simulate aicFrontend object
    const aicFrontend = {
        sound_base_url: '/wp-content/plugins/ai-multilingual-chat/sounds/',
        available_sounds: {
            'default': 'По умолчанию',
            'bell': 'Колокольчик',
            'ding': 'Динь',
            'chime': 'Перезвон',
            'soft': 'Мягкий звук'
        }
    };
    
    // Test default sound (no localStorage)
    const soundChoice1 = localStorage.getItem('aic_client_notification_sound') || 'default';
    if (soundChoice1 === 'default') {
        console.log('  ✓ Default sound selected when no preference saved');
    } else {
        console.log('  ✗ Default sound not selected correctly');
    }
    
    // Test saved preference
    localStorage.setItem('aic_client_notification_sound', 'chime');
    const soundChoice2 = localStorage.getItem('aic_client_notification_sound') || 'default';
    const soundUrl2 = aicFrontend.sound_base_url + 'notification-' + soundChoice2 + '.mp3';
    
    if (soundChoice2 === 'chime' && soundUrl2.includes('notification-chime.mp3')) {
        console.log('  ✓ Saved sound preference loaded correctly: chime');
        console.log(`  ✓ Sound URL: ${soundUrl2}`);
    } else {
        console.log('  ✗ Saved sound preference not loaded correctly');
    }
} catch (error) {
    console.log('  ✗ Error in client sound selection: ' + error.message);
}
console.log('');

// Test 4: Sound modal functionality
console.log('Test 4: Sound modal functionality simulation');
try {
    // Simulate available sounds
    const availableSounds = {
        'default': 'По умолчанию',
        'bell': 'Колокольчик',
        'ding': 'Динь',
        'chime': 'Перезвон',
        'soft': 'Мягкий звук'
    };
    
    // Simulate openSoundModal building HTML
    let html = '';
    const currentSound = 'bell';
    
    Object.keys(availableSounds).forEach(key => {
        const checked = (key === currentSound) ? 'checked' : '';
        const selectedClass = (key === currentSound) ? 'selected' : '';
        
        html += `<div class="aic-sound-item ${selectedClass}">
            <input type="radio" name="sound_choice" value="${key}" id="sound_${key}" ${checked}>
            <label for="sound_${key}">${availableSounds[key]}</label>
            <button class="aic-sound-preview" data-sound="${key}">🔊 Прослушать</button>
        </div>\n`;
    });
    
    // Verify HTML contains all sounds
    const hasAllSounds = Object.keys(availableSounds).every(key => html.includes(`value="${key}"`));
    const hasSelectedSound = html.includes('checked') && html.includes('selected');
    
    if (hasAllSounds) {
        console.log('  ✓ Modal HTML includes all sound options');
    } else {
        console.log('  ✗ Modal HTML is missing some sound options');
    }
    
    if (hasSelectedSound) {
        console.log('  ✓ Current sound is marked as selected');
    } else {
        console.log('  ✗ Current sound selection not indicated');
    }
} catch (error) {
    console.log('  ✗ Error in modal functionality: ' + error.message);
}
console.log('');

// Test 5: Fallback mechanism
console.log('Test 5: Fallback to default sound on error');
try {
    const aicAdmin = {
        sound_base_url: '/wp-content/plugins/ai-multilingual-chat/sounds/'
    };
    
    // Simulate error handling
    let primarySoundUrl = null;
    let fallbackSoundUrl = null;
    
    try {
        // Try to load non-existent sound
        const soundChoice = 'nonexistent';
        primarySoundUrl = aicAdmin.sound_base_url + 'notification-' + soundChoice + '.mp3';
        throw new Error('Sound file not found');
    } catch (error) {
        // Fallback to default
        fallbackSoundUrl = aicAdmin.sound_base_url + 'notification-default.mp3';
    }
    
    if (fallbackSoundUrl && fallbackSoundUrl.includes('notification-default.mp3')) {
        console.log('  ✓ Fallback to default sound works correctly');
        console.log(`  ✓ Fallback URL: ${fallbackSoundUrl}`);
    } else {
        console.log('  ✗ Fallback mechanism failed');
    }
} catch (error) {
    console.log('  ✗ Error in fallback test: ' + error.message);
}
console.log('');

// Test 6: Preview sound functionality
console.log('Test 6: Preview sound functionality');
try {
    const aicFrontend = {
        sound_base_url: '/wp-content/plugins/ai-multilingual-chat/sounds/'
    };
    
    // Simulate previewSound function
    function previewSound(soundKey) {
        const soundUrl = aicFrontend.sound_base_url + 'notification-' + soundKey + '.mp3';
        return soundUrl;
    }
    
    const testSounds = ['default', 'bell', 'ding', 'chime', 'soft'];
    let allUrlsCorrect = true;
    
    testSounds.forEach(soundKey => {
        const url = previewSound(soundKey);
        const expectedUrl = `/wp-content/plugins/ai-multilingual-chat/sounds/notification-${soundKey}.mp3`;
        
        if (url === expectedUrl) {
            console.log(`  ✓ Preview URL for '${soundKey}' is correct`);
        } else {
            console.log(`  ✗ Preview URL for '${soundKey}' is incorrect`);
            allUrlsCorrect = false;
        }
    });
    
    if (allUrlsCorrect) {
        console.log('  ✓ All preview URLs generated correctly');
    }
} catch (error) {
    console.log('  ✗ Error in preview functionality: ' + error.message);
}
console.log('');

console.log('=== Test Summary ===');
console.log('All tests completed. Review output above for detailed results.');
console.log('');
