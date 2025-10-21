# Adding New Languages to AI Multilingual Chat Plugin

This guide explains how administrators can add translations for new languages to the AI Multilingual Chat plugin.

## Prerequisites

- Access to your WordPress site files via FTP/SFTP or cPanel File Manager
- A text editor (recommended: Poedit, which is free and specifically designed for translations)
- Basic understanding of your target language

## Option 1: Using Poedit (Recommended)

### Step 1: Download and Install Poedit

1. Download Poedit from https://poedit.net/
2. Install it on your computer

### Step 2: Create a New Translation

1. Open Poedit
2. Click **File** → **New from POT/PO file**
3. Navigate to your WordPress installation:
   ```
   /wp-content/plugins/ai-multilingual-chat/languages/
   ```
4. Select the file `ai-multilingual-chat.pot`
5. Poedit will ask you to select the language you want to translate to (e.g., German - `de_DE`, French - `fr_FR`, Spanish - `es_ES`)

### Step 3: Translate the Strings

1. In Poedit, you'll see a list of all translatable strings
2. Select each string from the list
3. Enter your translation in the bottom text field
4. Click **Save** or press `Ctrl+S` (Windows/Linux) or `Cmd+S` (Mac)

### Step 4: Save Your Translation Files

1. Poedit will automatically generate two files:
   - `.po` file (editable source)
   - `.mo` file (compiled binary for WordPress)
2. Save them with the naming convention: `ai-multilingual-chat-{locale}.po` and `ai-multilingual-chat-{locale}.mo`
   
   Examples:
   - German: `ai-multilingual-chat-de_DE.po` and `ai-multilingual-chat-de_DE.mo`
   - French: `ai-multilingual-chat-fr_FR.po` and `ai-multilingual-chat-fr_FR.mo`
   - Spanish (Spain): `ai-multilingual-chat-es_ES.po` and `ai-multilingual-chat-es_ES.mo`

### Step 5: Upload to Your WordPress Site

1. Upload both `.po` and `.mo` files to:
   ```
   /wp-content/plugins/ai-multilingual-chat/languages/
   ```

### Step 6: Activate the Language in WordPress

1. Go to WordPress Admin → **Settings** → **General**
2. Change **Site Language** to your newly added language
3. Click **Save Changes**
4. The plugin will now automatically display in your selected language!

## Option 2: Manual Translation (Advanced)

If you prefer not to use Poedit, you can edit the `.po` file manually:

### Step 1: Copy the Template

```bash
cd /wp-content/plugins/ai-multilingual-chat/languages/
cp ai-multilingual-chat.pot ai-multilingual-chat-de_DE.po
```

### Step 2: Edit the PO File

Open `ai-multilingual-chat-de_DE.po` in a text editor and update the header:

```po
"Language: de_DE\n"
"Plural-Forms: nplurals=2; plural=(n != 1);\n"
```

### Step 3: Translate Each String

Find entries like this:

```po
msgid "Settings"
msgstr ""
```

Add your translation:

```po
msgid "Settings"
msgstr "Einstellungen"
```

### Step 4: Compile the MO File

Use the `msgfmt` command (requires gettext tools):

```bash
msgfmt -o ai-multilingual-chat-de_DE.mo ai-multilingual-chat-de_DE.po
```

### Step 5: Activate in WordPress

Follow Step 6 from Option 1 above.

## Language Codes Reference

Common WordPress locale codes:

| Language | Code |
|----------|------|
| English (US) | en_US |
| English (UK) | en_GB |
| Russian | ru_RU |
| German | de_DE |
| French | fr_FR |
| Spanish (Spain) | es_ES |
| Spanish (Mexico) | es_MX |
| Italian | it_IT |
| Portuguese (Brazil) | pt_BR |
| Portuguese (Portugal) | pt_PT |
| Ukrainian | uk |
| Polish | pl_PL |
| Dutch | nl_NL |
| Chinese (Simplified) | zh_CN |
| Japanese | ja |

For a complete list, see: https://wpml.org/documentation/support/language-codes/

## Plural Forms

Different languages have different plural rules. Here are some common ones:

**English, German, Dutch, Swedish:**
```po
Plural-Forms: nplurals=2; plural=(n != 1);
```

**French, Portuguese (Brazil):**
```po
Plural-Forms: nplurals=2; plural=(n > 1);
```

**Russian, Ukrainian, Polish:**
```po
Plural-Forms: nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<12 || n%100>14) ? 1 : 2);
```

**Japanese, Korean, Turkish:**
```po
Plural-Forms: nplurals=1; plural=0;
```

## Testing Your Translation

1. Change WordPress site language to your new language
2. Visit the plugin pages in the admin area:
   - **AI Chat** → **Conversation Management**
   - **AI Chat** → **Settings**
   - **AI Chat** → **Statistics**
   - **AI Chat** → **FAQ**
3. Check the front-end chat widget
4. Verify all strings are translated correctly

## Updating Existing Translations

When the plugin is updated with new features:

1. Download the latest `ai-multilingual-chat.pot` file from the plugin
2. Open your existing `.po` file in Poedit
3. Click **Catalog** → **Update from POT file**
4. Select the new `.pot` file
5. Poedit will add new untranslated strings
6. Translate the new strings
7. Save and upload the updated `.mo` and `.po` files

## Contributing Your Translation

If you'd like to share your translation with the community:

1. Test your translation thoroughly
2. Create a GitHub issue or pull request at the plugin repository
3. Attach your `.po` file (the `.mo` can be generated automatically)
4. Include:
   - Language name and code
   - Your name for credit (optional)
   - Any specific notes about the translation

## Troubleshooting

### Translation Not Showing

1. **Check file names**: Must follow the pattern `ai-multilingual-chat-{locale}.mo`
2. **Check file location**: Files must be in `/wp-content/plugins/ai-multilingual-chat/languages/`
3. **Check WordPress language**: Admin → Settings → General → Site Language
4. **Clear cache**: If using a caching plugin, clear the cache
5. **Check permissions**: Files should be readable (644 permissions)

### Partial Translation

- Make sure you translated all strings in the PO file
- Recompile the MO file after making changes
- Clear browser cache and WordPress cache

### Special Characters Not Displaying

- Ensure your PO file header specifies UTF-8 encoding:
  ```po
  "Content-Type: text/plain; charset=UTF-8\n"
  ```

## Need Help?

- Check the plugin documentation
- Visit the support forum
- Contact the plugin developer at https://web-proekt.com

## File Structure

```
/wp-content/plugins/ai-multilingual-chat/
├── languages/
│   ├── ai-multilingual-chat.pot          # Template file
│   ├── ai-multilingual-chat-ru_RU.po     # Russian source
│   ├── ai-multilingual-chat-ru_RU.mo     # Russian compiled
│   ├── ai-multilingual-chat-de_DE.po     # Your German source
│   └── ai-multilingual-chat-de_DE.mo     # Your German compiled
```

## Best Practices

1. **Keep context in mind**: Translate based on where the text appears in the UI
2. **Maintain formatting**: Keep placeholders like `%s`, `%d`, `%1$s` exactly as they are
3. **Be consistent**: Use the same terms throughout the translation
4. **Test thoroughly**: Check all admin pages and the frontend widget
5. **Update regularly**: Check for updates when the plugin is updated
6. **Keep backups**: Save your PO files as they can be easily edited and recompiled

---

**Note**: This plugin uses WordPress's standard localization system. Any tool that works with WordPress translations will work with this plugin.
