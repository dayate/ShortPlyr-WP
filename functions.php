<?php
/**
 * ShortPlyr functions and definitions
 *
 * @package ShortPlyr
 */

// Theme version.
define( 'SHORTPLYR_VERSION', '1.0.0' );

// Include required files.
require_once get_template_directory() . '/inc/cpt-setup.php';
require_once get_template_directory() . '/inc/enqueue-scripts.php';
require_once get_template_directory() . '/inc/theme-settings.php';
require_once get_template_directory() . '/inc/template-routing.php';
require_once get_template_directory() . '/inc/metaboxes.php';
require_once get_template_directory() . '/inc/api-handler.php';


/**
 * ===================================================================
 * MENGUBAH URL LOGIN WORDPRESS (PERBAIKAN LOGOUT)
 * ===================================================================
 */

// BAGIAN 1: Melindungi halaman login & admin yang asli
if ( ! function_exists('custom_login_init_protect') ) {
    function custom_login_init_protect() {
        $requested_path = rtrim( wp_parse_url($_SERVER['REQUEST_URI'])['path'], '/' );

        // PERBAIKAN: Blokir /login DAN /wp-login.php
        if ( $requested_path === '/login' || in_array( $GLOBALS['pagenow'], array( 'wp-login.php' ) ) ) {
            // Pengecualian untuk logout agar tidak terjadi redirect loop
            if ( ! isset( $_GET['action'] ) || 'logout' !== $_GET['action'] ) {
                wp_redirect( home_url( '/404.php' ) );
                exit;
            }
        }

        // Cek apakah pengguna mencoba akses /wp-admin dan belum login
        if ( is_admin() && ! is_user_logged_in() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            wp_redirect( home_url( '/404.php' ) );
            exit;
        }
    }
    add_action( 'init', 'custom_login_init_protect' );
}


// BAGIAN 2: Menampilkan halaman login kustom di /safe-house
if ( ! function_exists('custom_render_login_page') ) {
    function custom_render_login_page() {
        if ( rtrim( wp_parse_url($_SERVER['REQUEST_URI'])['path'], '/') === '/safe-house' ) {
            if ( is_user_logged_in() ) {
                wp_redirect( admin_url() );
                exit();
            }
            global $user_login, $error;
            $user_login = '';
            $error      = '';
            require_once( ABSPATH . 'wp-login.php' );
            exit();
        }
    }
    add_action( 'wp_loaded', 'custom_render_login_page' );
}

// BAGIAN 3: Mengarahkan pengguna setelah login berhasil
if ( ! function_exists('custom_login_redirect_handler') ) {
    function custom_login_redirect_handler( $redirect_to, $requested_redirect_to, $user ) {
        if ( ! is_wp_error( $user ) && ! empty( $user->ID ) ) {
            if ( user_can( $user, 'manage_options' ) || user_can( $user, 'edit_posts' ) ) {
                return admin_url();
            }
            return home_url();
        }
        return $redirect_to;
    }
    add_filter( 'login_redirect', 'custom_login_redirect_handler', 100, 3 );
}


// BAGIAN 4: Memperbarui semua link login di situs
if ( ! function_exists('custom_fix_login_links') ) {
    function custom_fix_login_links( $url, $path ) {
        // PERBAIKAN: Periksa apakah URL mengandung wp-login.php TAPI BUKAN link logout
        if ( strpos($url, 'wp-login.php') !== false && strpos($url, 'action=logout') === false ) {
            return home_url( '/safe-house' );
        }
        return $url;
    }
    add_filter( 'site_url', 'custom_fix_login_links', 10, 2 );
}


// BAGIAN 5: Mengarahkan pengguna setelah logout
if ( ! function_exists('custom_logout_redirect_handler') ) {
    function custom_logout_redirect_handler() {
        wp_redirect( home_url( '/safe-house?loggedout=true' ) );
        exit();
    }
    add_action( 'wp_logout', 'custom_logout_redirect_handler' );
}