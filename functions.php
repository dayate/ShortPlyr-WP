<?php
/**
 * ShortPlyr-WP Functions and Definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package ShortPlyr-WP
 */

// Require all the theme's functional parts from the /inc/ directory.
$theme_includes = [
    '/inc/cpt-setup.php',         // Custom Post Type and theme support setup.
    '/inc/metaboxes.php',        // All custom metaboxes for the CPT.
    '/inc/api-handler.php',      // Handles the logic for fetching data from the API.
    '/inc/enqueue-scripts.php',  // Enqueues scripts and styles.
    '/inc/template-routing.php', // Handles template redirection for the CPT.
    '/inc/theme-settings.php',   // Adds the theme settings page for API configuration.
];

foreach ($theme_includes as $file) {
    require get_template_directory() . $file;
}