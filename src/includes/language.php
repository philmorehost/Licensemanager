<?php
// Core Language System

// Global variable to hold all language strings
$GLOBALS['lang_strings'] = [];

function load_language() {
    // 1. Determine the language to use
    $default_lang = 'en';
    $lang_code = $default_lang;

    // Load language from settings file
    $settings_file = __DIR__ . '/../settings.json';
    if (file_exists($settings_file)) {
        $settings = json_decode(file_get_contents($settings_file), true);
        if (!empty($settings['language'])) {
            $lang_code = $settings['language'];
        }
    }

    // 2. Construct the language file path
    $lang_file = __DIR__ . '/../language/' . basename($lang_code) . '.php';

    // 3. Load the language file if it exists, otherwise fall back to English
    if (file_exists($lang_file)) {
        $GLOBALS['lang_strings'] = require $lang_file;
    } else {
        // Fallback to English if the selected language file doesn't exist
        $eng_lang_file = __DIR__ . '/../language/' . $default_lang . '.php';
        if (file_exists($eng_lang_file)) {
            $GLOBALS['lang_strings'] = require $eng_lang_file;
        }
    }
}

/**
 * Translates a given key into the loaded language.
 *
 * @param string $key The key of the string to translate.
 * @return string The translated string, or the key itself if not found.
 */
function trans($key) {
    if (isset($GLOBALS['lang_strings'][$key])) {
        return $GLOBALS['lang_strings'][$key];
    }
    // Return the key itself as a fallback, so it's clear what's missing.
    return $key;
}

// Load the language strings on script initialization
load_language();
?>
