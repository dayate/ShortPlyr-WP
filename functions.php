<?php
// =============================================================================
// BAGIAN 1: SETUP CPT & THEME SUPPORT
// =============================================================================
function create_serial_video_cpt() {
    $labels = [ 'name' => 'Serial Video', 'singular_name' => 'Serial Video', 'menu_name' => 'Serial Video', 'archives' => 'Arsip Serial Video', 'all_items' => 'Semua Serial', 'add_new_item' => 'Tambah Serial Baru', 'add_new' => 'Tambah Baru', 'new_item' => 'Serial Baru', 'edit_item' => 'Edit Serial', 'update_item' => 'Perbarui Serial', 'view_item' => 'Lihat Serial', 'search_items' => 'Cari Serial', 'not_found' => 'Tidak ditemukan', 'featured_image' => 'Gambar Poster', 'set_featured_image' => 'Atur gambar poster', 'remove_featured_image' => 'Hapus gambar poster', 'use_featured_image' => 'Gunakan sebagai gambar poster' ];
    $args = [ 'label' => 'Serial Video', 'description' => 'Postingan untuk Serial Video', 'labels' => $labels, 'supports' => [ 'title', 'editor', 'thumbnail' ], 'hierarchical' => false, 'public' => true, 'show_ui' => true, 'show_in_menu' => true, 'menu_position' => 5, 'menu_icon' => 'dashicons-video-alt3', 'has_archive' => true, 'capability_type' => 'post', 'rewrite' => ['slug' => 'shorts'] ];
    register_post_type( 'serial_video', $args );
}
add_action( 'init', 'create_serial_video_cpt', 0 );

function sudutcerita_theme_support() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'sudutcerita_theme_support');

// =============================================================================
// BAGIAN 2: META BOX UNTUK INPUT MANUAL (FALLBACK)
// =============================================================================
function serial_video_add_meta_box() { add_meta_box( 'serial_video_episodes_box', 'Daftar Episode Manual', 'serial_video_meta_box_html', 'serial_video', 'normal', 'low' ); }
add_action('add_meta_boxes', 'serial_video_add_meta_box');

function serial_video_meta_box_html($post) {
    $episodes = get_post_meta($post->ID, '_serial_video_episodes', true);
    wp_nonce_field('serial_video_episodes_nonce_action', 'serial_video_episodes_nonce');
    ?>
    <div id="episode_repeater_wrapper">
        <p><strong>PENTING:</strong> Bagian ini hanya digunakan jika Pengaturan MeloloAPI di atas <strong>dikosongkan</strong>.</p>
        <table id="episode_repeater_table" class="wp-list-table widefat fixed striped"><thead><tr><th style="width: 15%;">Nomor Episode</th><th>URL Video</th><th style="width: 10%;">Aksi</th></tr></thead><tbody id="episode_repeater_tbody">
        <?php if (!empty($episodes) && is_array($episodes)) { foreach ($episodes as $index => $episode) { ?>
            <tr class="episode-row">
                <td><input type="number" name="serial_episodes[<?php echo $index; ?>][nomor]" value="<?php echo esc_attr($episode['nomor']); ?>" class="widefat" min="1" /></td>
                <td><input type="url" name="serial_episodes[<?php echo $index; ?>][url]" value="<?php echo esc_url($episode['url']); ?>" class="widefat" placeholder="https://.../video.mp4" /></td>
                <td><a href="#" class="button remove-episode-row">Hapus</a></td>
            </tr>
        <?php } } ?>
        </tbody></table>
        <table style="display:none;"><tr id="episode_repeater_template" class="episode-row"><td><input type="number" name="serial_episodes[__INDEX__][nomor]" class="widefat" min="1" /></td><td><input type="url" name="serial_episodes[__INDEX__][url]" class="widefat" placeholder="https://.../video.mp4" /></td><td><a href="#" class="button remove-episode-row">Hapus</a></td></tr></table>
        <p style="padding-top:10px;"><a href="#" id="add_episode_row_button" class="button button-primary">Tambah Episode</a></p>
    </div>
    <?php
}

function serial_video_save_meta_box($post_id) {
    if (!isset($_POST['serial_video_episodes_nonce']) || !wp_verify_nonce($_POST['serial_video_episodes_nonce'], 'serial_video_episodes_nonce_action')) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (isset($_POST['serial_episodes']) && is_array($_POST['serial_episodes'])) {
        $sanitized_episodes = [];
        foreach ($_POST['serial_episodes'] as $episode) {
            if (!empty($episode['nomor']) && !empty($episode['url'])) {
                $sanitized_episodes[] = [ 'nomor' => absint($episode['nomor']), 'url' => esc_url_raw($episode['url']) ];
            }
        }
        update_post_meta($post_id, '_serial_video_episodes', $sanitized_episodes);
    } else { delete_post_meta($post_id, '_serial_video_episodes'); }
}
add_action('save_post_serial_video', 'serial_video_save_meta_box');

// =============================================================================
// BAGIAN 2.5: META BOX UNTUK IKLAN
// =============================================================================
function serial_video_adsterra_meta_box() { add_meta_box('serial_video_adsterra_box', 'Pengaturan Iklan (Direct Link)', 'serial_video_adsterra_meta_box_html', 'serial_video', 'side', 'default'); }
add_action('add_meta_boxes', 'serial_video_adsterra_meta_box');

function serial_video_adsterra_meta_box_html($post) {
    $adsterra_url = get_post_meta($post->ID, '_adsterra_direct_link_url', true);
    $ad_count = get_post_meta($post->ID, '_adsterra_ad_count', true);
    wp_nonce_field('serial_video_adsterra_nonce_action', 'serial_video_adsterra_nonce');
    ?>
    <p><label for="adsterra_url"><strong>URL Direct Link:</strong></label><br><input type="url" id="adsterra_url" name="adsterra_url" value="<?php echo esc_url($adsterra_url); ?>" class="widefat" placeholder="https://..." /></p>
    <p><label for="ad_count"><strong>Jumlah Episode Iklan:</strong></label><br><input type="number" id="ad_count" name="ad_count" value="<?php echo esc_attr($ad_count); ?>" class="widefat" min="0" placeholder="Contoh: 2" /><small>Masukkan angka. Episode akan dipilih acak. Isi 0 untuk nonaktif.</small></p>
    <?php
}

function serial_video_save_adsterra_meta_box($post_id) {
    if (!isset($_POST['serial_video_adsterra_nonce']) || !wp_verify_nonce($_POST['serial_video_adsterra_nonce'], 'serial_video_adsterra_nonce_action')) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['adsterra_url'])) { update_post_meta($post_id, '_adsterra_direct_link_url', esc_url_raw($_POST['adsterra_url'])); }
    if (isset($_POST['ad_count'])) { update_post_meta($post_id, '_adsterra_ad_count', absint($_POST['ad_count'])); }
}
add_action('save_post_serial_video', 'serial_video_save_adsterra_meta_box');

// =============================================================================
// BAGIAN 2.6: META BOX UNTUK POSTER KUSTOM
// =============================================================================
function serial_video_poster_url_meta_box() { add_meta_box('serial_video_poster_url_box', 'URL Poster Kustom', 'serial_video_poster_url_meta_box_html', 'serial_video', 'side', 'default'); }
add_action('add_meta_boxes', 'serial_video_poster_url_meta_box');

function serial_video_poster_url_meta_box_html($post) {
    $poster_url = get_post_meta($post->ID, '_serial_video_poster_url', true);
    wp_nonce_field('serial_video_poster_url_nonce_action', 'serial_video_poster_url_nonce');
    ?>
    <p><label for="poster_url"><strong>URL Gambar Poster:</strong></label><br><input type="url" id="poster_url" name="poster_url" value="<?php echo esc_url($poster_url); ?>" class="widefat" placeholder="https://.../poster.jpg" /><small>Jika kosong, akan pakai "Gambar Poster" (Featured Image).</small></p>
    <?php
}

function serial_video_save_poster_url_meta_box($post_id) {
    if (!isset($_POST['serial_video_poster_url_nonce']) || !wp_verify_nonce($_POST['serial_video_poster_url_nonce'], 'serial_video_poster_url_nonce_action')) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['poster_url'])) { update_post_meta($post_id, '_serial_video_poster_url', esc_url_raw($_POST['poster_url'])); }
}
add_action('save_post_serial_video', 'serial_video_save_poster_url_meta_box');

// =============================================================================
// BAGIAN 2.7: META BOX UNTUK INPUT API (UTAMA)
// =============================================================================
function serial_video_api_meta_box() { add_meta_box('serial_video_api_box', 'Pengaturan MeloloAPI', 'serial_video_api_meta_box_html', 'serial_video', 'normal', 'high'); }
add_action('add_meta_boxes', 'serial_video_api_meta_box');

function serial_video_api_meta_box_html($post) {
    wp_nonce_field('serial_video_api_nonce_action', 'serial_video_api_nonce');
    $api_query = get_post_meta($post->ID, '_api_query', true);
    $api_book_id = get_post_meta($post->ID, '_api_book_id', true);
    $api_book_name = get_post_meta($post->ID, '_api_book_name', true);
    ?>
    <p>Isi detail berikut untuk mengambil data video otomatis dari MeloloAPI. Jika ini diisi, daftar episode manual di bawah akan diabaikan.</p>
    <table class="form-table">
        <tbody>
            <tr><th><label for="api_query">Judul Novel (Query)</label></th><td><input type="text" id="api_query" name="api_query" value="<?php echo esc_attr($api_query); ?>" class="widefat" /></td></tr>
            <tr><th><label for="api_book_id">Book ID</label></th><td><input type="text" id="api_book_id" name="api_book_id" value="<?php echo esc_attr($api_book_id); ?>" class="widefat" /></td></tr>
            <tr><th><label for="api_book_name">Book Name</label></th><td><input type="text" id="api_book_name" name="api_book_name" value="<?php echo esc_attr($api_book_name); ?>" class="widefat" /></td></tr>
        </tbody>
    </table>
    <?php
}

function serial_video_save_api_meta_box($post_id) {
    if (!isset($_POST['serial_video_api_nonce']) || !wp_verify_nonce($_POST['serial_video_api_nonce'], 'serial_video_api_nonce_action')) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['api_query'])) { update_post_meta($post_id, '_api_query', sanitize_text_field($_POST['api_query'])); }
    if (isset($_POST['api_book_id'])) { update_post_meta($post_id, '_api_book_id', sanitize_text_field($_POST['api_book_id'])); }
    if (isset($_POST['api_book_name'])) { update_post_meta($post_id, '_api_book_name', sanitize_text_field($_POST['api_book_name'])); }
}
add_action('save_post_serial_video', 'serial_video_save_api_meta_box');

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
        wp_enqueue_style('sudutcerita-main-style', get_template_directory_uri() . '/assets/css/main.min.css', [], '1.0');

        // Panggil fungsi helper untuk mendapatkan semua data yang telah diproses
        $data_for_js = get_processed_serial_data(get_the_ID());

        // Muat file JavaScript utama
        $script_ver = filemtime( get_theme_file_path( '/assets/js/main.min.js' ) );
        wp_enqueue_script('sudutcerita-main-script', get_template_directory_uri() . '/assets/js/main.min.js', [], $script_ver, true);

        // Kirim semua data ke JavaScript. Ini WAJIB agar video player bisa berfungsi.
        wp_localize_script('sudutcerita-main-script', 'seriesData', $data_for_js);
    }
}
add_action('wp_enqueue_scripts', 'sudutcerita_enqueue_assets');


/**
 * Fungsi Helper Baru.
 * Tugasnya adalah mengambil semua data dari API (atau manual sebagai fallback)
 * dan menyiapkannya dalam satu array. Fungsi ini bisa dipanggil di mana saja.
 */
function get_processed_serial_data($post_id) {
    $episodes_data = [];
    $book_details = [];

    $api_query = get_post_meta($post_id, '_api_query', true);
    $api_book_id = get_post_meta($post_id, '_api_book_id', true);
    $api_book_name = get_post_meta($post_id, '_api_book_name', true);

    if (!empty($api_query) && !empty($api_book_id) && !empty($api_book_name)) {
        // GANTI URL API ANDA DI SINI
        $api_url = 'http://127.0.0.1:8000/api/search-and-get-details';

        $full_api_url = add_query_arg(['query' => urlencode($api_query), 'book_id' => urlencode($api_book_id), 'book_name' => urlencode($api_book_name)], $api_url);
        $response = wp_remote_get($full_api_url, ['timeout' => 20]);

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $api_body = json_decode(wp_remote_retrieve_body($response), true);
            if (isset($api_body['data']['video_list'])) {
                $book_details = $api_body['data']['book_details'];
                $raw_episodes = $api_body['data']['video_list'];
                usort($raw_episodes, function($a, $b) { return $a['vid_index'] <=> $b['vid_index']; });
                foreach ($raw_episodes as $episode) {
                    $video_url = $episode['urls']['main_url'] ?? $episode['urls']['backup_url'] ?? '';
                    if (!empty($video_url)) {
                       $episodes_data[] = ['episode' => $episode['vid_index'], 'is_ad' => false, 'src' => $video_url, 'original_src' => $video_url];
                    }
                }
            }
        }
    } else {
        $episodes = get_post_meta($post_id, '_serial_video_episodes', true);
        if (!empty($episodes) && is_array($episodes)) {
             usort($episodes, function($a, $b) { return $a['nomor'] <=> $b['nomor']; });
             foreach($episodes as $episode) {
                 $episodes_data[] = ['episode' => $episode['nomor'], 'is_ad' => false, 'src' => $episode['url'], 'original_src' => $episode['url']];
             }
        }
    }

    $adsterra_url = get_post_meta($post_id, '_adsterra_direct_link_url', true);
    $ad_count = absint(get_post_meta($post_id, '_adsterra_ad_count', true));
    $total_episodes = count($episodes_data);

    if (!empty($adsterra_url) && $ad_count > 0 && $total_episodes > 0) {
        $ad_count = min($ad_count, $total_episodes);
        $possible_indices = range(0, $total_episodes - 1);
        shuffle($possible_indices);
        $ad_indices = array_slice($possible_indices, 0, $ad_count);
        foreach ($ad_indices as $index) {
            if (isset($episodes_data[$index])) {
                $episodes_data[$index]['is_ad'] = true;
                $episodes_data[$index]['ad_src'] = $adsterra_url;
                unset($episodes_data[$index]['src']);
            }
        }
    }

    // ================== PERUBAHAN DI SINI ==================
    // Logika baru untuk poster: prioritaskan input WordPress.
    $custom_poster_url = get_post_meta($post_id, '_serial_video_poster_url', true);
    $poster_url = !empty($custom_poster_url) ? $custom_poster_url : get_the_post_thumbnail_url($post_id, 'medium');
    // ========================================================

    $final_title = !empty($book_details['book_name']) ? $book_details['book_name'] : get_the_title();
    $final_synopsis = !empty($book_details['abstract']) ? nl2br(esc_html($book_details['abstract'])) : get_the_content();

    // Kembalikan semua data dalam satu array yang rapi
    return [
        'id'       => 'series_' . $post_id,
        'title'    => $final_title,
        'poster'   => $poster_url,
        'total'    => $total_episodes,
        'synopsis' => $final_synopsis,
        'episodes' => array_values($episodes_data)
    ];
}


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
