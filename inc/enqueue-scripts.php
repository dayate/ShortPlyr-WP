<?php
// =============================================================================
// BAGIAN 3: ENQUEUE SCRIPTS & STYLES
// =============================================================================
function serial_video_admin_scripts($hook) {
    global $post;
    if (($hook == 'post-new.php' || $hook == 'post.php') && $post && 'serial_video' === $post->post_type) {
        wp_enqueue_script('serial-video-admin-js', get_template_directory_uri() . '/assets/js/admin-episodes.js', ['jquery'], '1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'serial_video_admin_scripts');

/**
 * Fungsi ini mengambil semua data yang dibutuhkan (dari API atau manual)
 * dan MENGIRIMKANNYA KE JAVASCRIPT.
 * JavaScript SANGAT MEMBUTUHKAN ini untuk memutar video.
 */
function sudutcerita_enqueue_assets() {
    if (is_singular('serial_video')) {
        $css_ver = filemtime(get_theme_file_path('/assets/css/main.min.css'));
        wp_enqueue_style('sudutcerita-main-style', get_template_directory_uri() . '/assets/css/main.min.css', [], $css_ver);

        // Siapkan data untuk JavaScript: post ID, URL REST, dan nonce.
        $data_for_js = [
            'post_id' => get_the_ID(),
            'api_url' => rest_url('shortplyr/v1/serial/' . get_the_ID()),
            'nonce'   => wp_create_nonce('wp_rest')
        ];

        // Muat file JavaScript utama
        $script_ver = filemtime( get_theme_file_path( '/assets/js/main.min.js' ) );
        wp_enqueue_script('sudutcerita-main-script', get_template_directory_uri() . '/assets/js/main.min.js', [], $script_ver, true);

        // Kirim data esensial ke JavaScript untuk AJAX.
        wp_localize_script('sudutcerita-main-script', 'shortplyrData', $data_for_js);
    }
}
add_action('wp_enqueue_scripts', 'sudutcerita_enqueue_assets');