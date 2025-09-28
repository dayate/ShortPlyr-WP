<?php
function prefix_theme_templates( $template ) {
    $post_types = array( 'serial_video' );
    if ( is_post_type_archive( $post_types ) && file_exists( get_stylesheet_directory() . '/templates/archive-serial_video.php' ) ) {
        $template = get_stylesheet_directory() . '/templates/archive-serial_video.php';
    }
    if ( is_singular( $post_types ) && file_exists( get_stylesheet_directory() . '/templates/single-serial_video.php' ) ) {
        $template = get_stylesheet_directory() . '/templates/single-serial_video.php';
    }
    return $template;
}
add_filter( 'template_include', 'prefix_theme_templates' );
