/**
 * Simple Emoji Picker for AI Multilingual Chat
 */
(function($) {
    'use strict';
    
    const EmojiPicker = {
        emojis: [
            '😀', '😁', '😂', '🤣', '😃', '😄', '😅', '😆', '😉', '😊',
            '😋', '😎', '😍', '😘', '😗', '😙', '😚', '🙂', '🤗', '🤩',
            '🤔', '🤨', '😐', '😑', '😶', '🙄', '😏', '😣', '😥', '😮',
            '🤐', '😯', '😪', '😫', '😴', '😌', '😛', '😜', '😝', '🤤',
            '😒', '😓', '😔', '😕', '🙃', '🤑', '😲', '☹️', '🙁', '😖',
            '😞', '😟', '😤', '😢', '😭', '😦', '😧', '😨', '😩', '🤯',
            '😬', '😰', '😱', '😳', '🤪', '😵', '😡', '😠', '🤬', '😷',
            '🤒', '🤕', '🤢', '🤮', '🤧', '😇', '🤠', '🤡', '🤥', '🤫',
            '👍', '👎', '👌', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉',
            '👆', '👇', '☝️', '✋', '🤚', '🖐', '🖖', '👋', '🤝', '🙏',
            '💪', '🦾', '🤳', '✍️', '👏', '👐', '🙌', '🤲', '🤝', '👊',
            '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔',
            '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '☮️',
            '✨', '💫', '⭐', '🌟', '✅', '❌', '❗', '❓', '💯', '🔥',
            '👋', '🎉', '🎊', '🎈', '🎁', '🏆', '🥇', '🥈', '🥉', '⚽'
        ],
        
        init: function(inputSelector, buttonSelector) {
            this.inputSelector = inputSelector;
            this.buttonSelector = buttonSelector;
            this.createPicker();
            this.bindEvents();
        },
        
        createPicker: function() {
            if ($('#aic-emoji-picker').length) return;
            
            let html = '<div id="aic-emoji-picker" class="aic-emoji-picker" style="display: none;">';
            html += '<div class="aic-emoji-grid">';
            
            this.emojis.forEach(function(emoji) {
                html += `<span class="aic-emoji-item">${emoji}</span>`;
            });
            
            html += '</div>';
            html += '</div>';
            
            $('body').append(html);
        },
        
        bindEvents: function() {
            const self = this;
            
            // Toggle picker
            $(document).on('click', this.buttonSelector, function(e) {
                e.preventDefault();
                e.stopPropagation();
                const $picker = $('#aic-emoji-picker');
                const $button = $(this);
                
                if ($picker.is(':visible')) {
                    $picker.hide();
                } else {
                    const offset = $button.offset();
                    const buttonHeight = $button.outerHeight();
                    
                    $picker.css({
                        top: offset.top - $picker.outerHeight() - 10,
                        left: offset.left
                    }).show();
                }
            });
            
            // Select emoji
            $(document).on('click', '.aic-emoji-item', function() {
                const emoji = $(this).text();
                const $input = $(self.inputSelector);
                const currentValue = $input.val();
                $input.val(currentValue + emoji);
                $input.trigger('input');
                $('#aic-emoji-picker').hide();
            });
            
            // Close picker when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.aic-emoji-picker, ' + self.buttonSelector).length) {
                    $('#aic-emoji-picker').hide();
                }
            });
        }
    };
    
    // Export to global scope
    window.AICEmojiPicker = EmojiPicker;
    
})(jQuery);
