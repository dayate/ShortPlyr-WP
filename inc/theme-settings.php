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
    register_setting('shortplyr_settings_group', 'shortplyr_melolo_api_key');

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
    $api_key = get_option('shortplyr_melolo_api_key', '');
    ?>
    <input type="text" name="shortplyr_melolo_api_key" id="shortplyr_melolo_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text">
    <p class="description">Enter your API key here.</p>
    <?php
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