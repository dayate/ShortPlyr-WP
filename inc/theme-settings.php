<?php
/**
 * Theme settings page for ShortPlyr-WP.
 *
 * @package ShortPlyr-WP
 */

// 1. Add the theme settings page to the admin menu.
function shortplyr_add_admin_menu() {
    add_theme_page(
        'ShortPlyr Theme Settings',
        'ShortPlyr Settings',
        'manage_options',
        'shortplyr-settings',
        'shortplyr_settings_page_html'
    );
}
add_action('admin_menu', 'shortplyr_add_admin_menu');

// 2. Register the settings, sections, and fields.
function shortplyr_settings_init() {
    // Register a setting to store our API URL.
    register_setting('shortplyr_settings_group', 'shortplyr_melolo_api_url');

    // Register a setting to store our API Key.
    register_setting('shortplyr_settings_group', 'shortplyr_melolo_api_key', ['sanitize_callback' => 'shortplyr_sanitize_api_key']);

    // Add a section to the settings page.
    add_settings_section(
        'shortplyr_api_section',
        'API Settings',
        'shortplyr_api_section_callback',
        'shortplyr-settings'
    );

    // Add the field for the API URL.
    add_settings_field(
        'shortplyr_melolo_api_url_field',
        'MeloloAPI URL',
        'shortplyr_melolo_api_url_field_html',
        'shortplyr-settings',
        'shortplyr_api_section'
    );

    // Add the field for the API Key.
    add_settings_field(
        'shortplyr_melolo_api_key_field',
        'MeloloAPI Key',
        'shortplyr_melolo_api_key_field_html',
        'shortplyr-settings',
        'shortplyr_api_section'
    );
}
add_action('admin_init', 'shortplyr_settings_init');

// Sanitization callback for the API key.
function shortplyr_sanitize_api_key($input) {
    // If the input is empty, it means the user doesn't want to change the key.
    // In this case, we return the existing key to prevent accidental deletion.
    if (empty($input)) {
        return get_option('shortplyr_melolo_api_key');
    }

    // If the input is a single space, treat it as an intentional deletion.
    if (trim($input) === '') {
        return '';
    }

    // Otherwise, sanitize and return the new key.
    return sanitize_text_field($input);
}

// 3. Callbacks to render the HTML for the page and fields.
function shortplyr_api_section_callback() {
    echo '<p>Enter the base URL and API Key for the MeloloAPI. This will be used to fetch episode data.</p>';
}

function shortplyr_melolo_api_url_field_html() {
    $api_url = get_option('shortplyr_melolo_api_url', 'http://127.0.0.1:8000/api/search-and-get-details');
    ?>
    <input type="url" name="shortplyr_melolo_api_url" id="shortplyr_melolo_api_url" value="<?php echo esc_attr($api_url); ?>" class="regular-text">
    <p class="description">The default value is the local development endpoint.</p>
    <?php
}

function shortplyr_melolo_api_key_field_html() {
    // Check if the key is hardcoded in wp-config.php.
    if (defined('SHORTPLYR_MELOLO_API_KEY') && !empty(SHORTPLYR_MELOLO_API_KEY)) {
        ?>
        <div class="api-key-wrapper">
            <input type="text" name="shortplyr_melolo_api_key" id="shortplyr_melolo_api_key" value="********************************" disabled class="regular-text">
        </div>
        <p class="description"><strong>The API Key is defined in your <code>wp-config.php</code> file and cannot be edited here.</strong></p>
        <?php
        return;
    }

    $api_key = get_option('shortplyr_melolo_api_key');
    ?>
    <div class="api-key-wrapper">
        <?php if (!empty($api_key)) : ?>
            <input type="password" name="shortplyr_melolo_api_key" id="shortplyr_melolo_api_key" value="<?php echo esc_attr(str_repeat('*', 16)); ?>" data-apikey="<?php echo esc_attr($api_key); ?>" class="regular-text">
            <button type="button" id="toggle-api-key" class="button button-secondary">
                <i class="ri-eye-line"></i>
            </button>
        <?php else : ?>
            <input type="text" name="shortplyr_melolo_api_key" id="shortplyr_melolo_api_key" value="" class="regular-text">
        <?php endif; ?>
    </div>
    <?php

    if (!empty($api_key)) {
        echo '<p class="description">An API key is already saved. To change it, enter a new key above. To delete it, enter a single space and click Save.</p>';
    } else {
        echo '<p class="description">Enter your API key here.</p>';
    }
    echo '<p class="description">For enhanced security, you can define the key in your <code>wp-config.php</code> file by adding: <br><code>define(\'SHORTPLYR_MELOLO_API_KEY\', \'your-api-key\');</code></p>';
}

function shortplyr_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('shortplyr_settings_group');
            do_settings_sections('shortplyr-settings');
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}