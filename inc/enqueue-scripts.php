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

function shortplyr_settings_page_scripts($hook_suffix) {
    // The hook for a theme page is 'appearance_page_{menu_slug}'
    if ($hook_suffix === 'appearance_page_shortplyr-settings') {
        // Enqueue the admin settings script
        wp_enqueue_script('shortplyr-admin-settings-js', get_template_directory_uri() . '/assets/js/admin-settings.js', [], '1.0', true);

        // Enqueue Remixicon CSS for the eye icon
        wp_enqueue_style('remixicon-css', get_template_directory_uri() . '/assets/css/remixicon/remixicon.css', [], '4.3.0');

        // Enqueue the admin settings styles
        wp_enqueue_style('shortplyr-admin-settings-css', get_template_directory_uri() . '/assets/css/admin-settings.css', [], '1.0');
    }
}
add_action('admin_enqueue_scripts', 'shortplyr_settings_page_scripts');

/**
 * Fungsi ini mengambil semua data yang dibutuhkan (dari API atau manual)
 * dan MENGIRIMKANNYA KE JAVASCRIPT.
 * JavaScript SANGAT MEMBUTUHKAN ini untuk memutar video.
 */
function sudutcerita_enqueue_assets() {
    // Muat CSS utama di SEMUA halaman (termasuk 404)
    $css_ver = filemtime(get_theme_file_path('/assets/css/main.min.css'));
    wp_enqueue_style('sudutcerita-main-style', get_template_directory_uri() . '/assets/css/main.min.css', [], $css_ver);

    // Muat JavaScript HANYA jika ini halaman single video
    if (is_singular('serial_video')) {
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
    
    // Muat script HEIC converter di halaman arsip serial_video dan halaman utama (karena sekarang menggunakan template arsip)
    if (is_post_type_archive('serial_video') || is_home() || is_front_page()) {
        // Muat file JavaScript HEIC converter
        wp_enqueue_script('sudutcerita-heic-converter', get_template_directory_uri() . '/assets/js/heic-converter.min.js', [], SHORTPLYR_VERSION, true);
    }
    
    // Muat script dan style tambahan untuk halaman depan (index.php)
    if (is_front_page() || is_home()) {
        // Enqueue Swiper CSS & JS
        wp_enqueue_style('swiper-css', get_template_directory_uri() . '/assets/css/swiper-bundle.min.css', [], '11.0.5');
        wp_enqueue_script('swiper-js', get_template_directory_uri() . '/assets/js/swiper-bundle.min.js', [], '11.0.5', true);
        
        // Enqueue Remixicons dari lokal
        wp_enqueue_style('remixicons', get_template_directory_uri() . '/assets/css/remixicon/remixicon.css', [], '4.2.0');
        
        // Enqueue custom Swiper pagination styles
        wp_enqueue_style('swiper-pagination-css', get_template_directory_uri() . '/assets/css/swiper-pagination.css', [], '1.0');
        
        // Enqueue main script untuk halaman depan
        $main_js_ver = filemtime(get_theme_file_path('/assets/js/main.js'));
        wp_enqueue_script('shortplyr-main-js', get_template_directory_uri() . '/assets/js/main.js', ['swiper-js'], $main_js_ver, true);
    }
}
add_action('wp_enqueue_scripts', 'sudutcerita_enqueue_assets');