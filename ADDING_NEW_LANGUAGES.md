# Adding New Languages to AI Multilingual Chat Plugin

This guide explains how to add new language translations to the AI Multilingual Chat WordPress plugin.

## Overview

The plugin uses WordPress's built-in internationalization (i18n) system for managing translations. All user-facing text is wrapped in translation functions like `__()`, `_e()`, `esc_html__()`, etc.

## Files Structure

```
ai-multilingual-chat/
└── languages/
    ├── ai-multilingual-chat.pot          # Template file with all translatable strings
    ├── ai-multilingual-chat-ru_RU.po     # Russian translation source
    ├── ai-multilingual-chat-ru_RU.mo     # Russian compiled translation
    ├── ai-multilingual-chat-{locale}.po  # Your language source file
    └── ai-multilingual-chat-{locale}.mo  # Your language compiled file
```

## Step-by-Step Guide to Add a New Language

### Method 1: Using Poedit (Recommended for Non-Developers)

1. **Install Poedit**
   - Download from https://poedit.net/
   - Available for Windows, macOS, and Linux

2. **Create New Translation**
   - Open Poedit
   - Click "Create new translation"
   - Select the `ai-multilingual-chat.pot` file from the `languages/` directory
   - Choose your target language (e.g., Spanish, German, French)
   - Save the file with the proper locale code:
     - Spanish (Spain): `ai-multilingual-chat-es_ES.po`
     - German: `ai-multilingual-chat-de_DE.po`
     - French: `ai-multilingual-chat-fr_FR.po`
     - Italian: `ai-multilingual-chat-it_IT.po`

3. **Translate Strings**
   - Go through each string in Poedit
   - Enter your translation in the bottom text area
   - Poedit will automatically compile the `.mo` file when you save

4. **Upload Files**
   - Upload both `.po` and `.mo` files to the `wp-content/plugins/ai-multilingual-chat/languages/` directory
   - The plugin will automatically detect and use the translation based on your WordPress language setting

### Method 2: Using Command Line (For Developers)

1. **Generate PO file from POT template**
   ```bash
   cd wp-content/plugins/ai-multilingual-chat/languages/
   msginit -i ai-multilingual-chat.pot -o ai-multilingual-chat-fr_FR.po -l fr_FR
   ```

2. **Edit the PO file**
   - Open the `.po` file in your favorite text editor
   - Translate each `msgstr ""` line with your translation
   ```
   msgid "Settings"
   msgstr "Paramètres"
   ```

3. **Compile to MO file**
   ```bash
   msgfmt -o ai-multilingual-chat-fr_FR.mo ai-multilingual-chat-fr_FR.po
   ```

4. **Test the translation**
   - Upload the files to the languages directory
   - Change WordPress language in Settings → General
   - Verify translations appear correctly

### Method 3: Using Loco Translate Plugin (WordPress Plugin)

1. **Install Loco Translate**
   - Go to Plugins → Add New in WordPress admin
   - Search for "Loco Translate"
   - Install and activate

2. **Create Translation**
   - Go to Loco Translate → Plugins
   - Find "AI Multilingual Chat"
   - Click "New language"
   - Select your language
   - Choose "WordPress location"
   - Click "Start translating"

3. **Translate in Browser**
   - Translate each string directly in your browser
   - Click "Save" regularly
   - Loco Translate automatically compiles the MO file

## Locale Codes Reference

Common WordPress locale codes:

| Language | Code | File Name Pattern |
|----------|------|-------------------|
| English (US) | en_US | ai-multilingual-chat-en_US.po |
| Russian | ru_RU | ai-multilingual-chat-ru_RU.po |
| Spanish (Spain) | es_ES | ai-multilingual-chat-es_ES.po |
| French (France) | fr_FR | ai-multilingual-chat-fr_FR.po |
| German | de_DE | ai-multilingual-chat-de_DE.po |
| Italian | it_IT | ai-multilingual-chat-it_IT.po |
| Portuguese (Brazil) | pt_BR | ai-multilingual-chat-pt_BR.po |
| Portuguese (Portugal) | pt_PT | ai-multilingual-chat-pt_PT.po |
| Chinese (Simplified) | zh_CN | ai-multilingual-chat-zh_CN.po |
| Japanese | ja | ai-multilingual-chat-ja.po |
| Korean | ko_KR | ai-multilingual-chat-ko_KR.po |
| Ukrainian | uk | ai-multilingual-chat-uk.po |
| Polish | pl_PL | ai-multilingual-chat-pl_PL.po |
| Dutch | nl_NL | ai-multilingual-chat-nl_NL.po |
| Turkish | tr_TR | ai-multilingual-chat-tr_TR.po |
| Arabic | ar | ai-multilingual-chat-ar.po |

Full list: https://make.wordpress.org/polyglots/teams/

## Important Notes

1. **Character Encoding**: All PO/MO files must be UTF-8 encoded
2. **Special Characters**: Escape quotes in translations: `\"` instead of `"`
3. **Placeholders**: Keep placeholders like `%s`, `%d`, `%1$s` in their original positions
4. **HTML**: Keep HTML tags like `<strong>`, `<br>` in translations
5. **Context**: Read the context comments in PO files to understand where text is used

## Frontend Widget Localization

The chat widget uses `i18n.js` for frontend translations. To add a new language to the widget:

1. Open `ai-multilingual-chat/i18n.js`
2. Add your language code and translations to the `translations` object:

```javascript
translations: {
    'fr': {
        'welcome': 'Bienvenue!',
        'introduce_yourself': 'Veuillez vous présenter pour commencer le chat',
        'your_name': 'Votre nom',
        'start_chat': 'Démarrer le chat',
        // ... more translations
    }
}
```

## Testing Your Translation

1. **Activate Language**
   - Go to WordPress Admin → Settings → General
   - Change "Site Language" to your new language
   - Save changes

2. **Clear Cache**
   - Clear WordPress cache if using a caching plugin
   - Clear browser cache
   - Reload the page

3. **Verify Translation**
   - Check all admin pages (Settings, FAQ, Statistics)
   - Check the frontend chat widget
   - Test form submissions and error messages
   - Verify email notifications

## Updating Translations

When the plugin is updated with new features:

1. **Update POT file** (Developer task - done when adding new features)
   ```bash
   wp i18n make-pot . languages/ai-multilingual-chat.pot --domain=ai-multilingual-chat
   ```

2. **Update your PO file**
   - Open your language `.po` file in Poedit
   - Click "Update from POT file"
   - Select the new `ai-multilingual-chat.pot`
   - Translate new strings
   - Save (MO file is automatically compiled)

## Contributing Translations

If you've created a translation for a new language:

1. Fork the plugin repository on GitHub
2. Add your `.po` and `.mo` files to the `languages/` directory
3. Create a pull request with your translation
4. Include information about your language and locale code

## Need Help?

- **Documentation**: https://developer.wordpress.org/plugins/internationalization/
- **Poedit Documentation**: https://poedit.net/trac/wiki/Doc
- **Plugin Support**: https://web-proekt.com
- **GitHub Issues**: https://github.com/Fill777555/ai-multilingual-chat/issues

## Translation Statistics

Current plugin translation coverage:
- **Total strings**: 174
- **Russian (ru_RU)**: 100% complete
- **Other languages**: Community contributions welcome!

---

**Thank you for helping make AI Multilingual Chat available in your language!** 🌍
