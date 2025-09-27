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
// BAGIAN 2: CUSTOM META BOX UNTUK DAFTAR EPISODE
// =============================================================================
function serial_video_add_meta_box() { add_meta_box( 'serial_video_episodes_box', 'Daftar Episode', 'serial_video_meta_box_html', 'serial_video', 'normal', 'high' ); }
add_action('add_meta_boxes', 'serial_video_add_meta_box');

function serial_video_meta_box_html($post) {
    $episodes = get_post_meta($post->ID, '_serial_video_episodes', true);
    wp_nonce_field('serial_video_episodes_nonce_action', 'serial_video_episodes_nonce');
    ?>
    <div id="episode_repeater_wrapper">
        <p>Tambahkan setiap episode untuk serial ini. Anda bisa mendapatkan URL video dengan mengunggahnya ke Media Library terlebih dahulu.</p>
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
// BAGIAN 2.5: META BOX UNTUK PENGATURAN IKLAN ADSTERRA
// =============================================================================
function serial_video_adsterra_meta_box() {
    add_meta_box(
        'serial_video_adsterra_box',
        'Pengaturan Iklan Adsterra (Direct Link)',
        'serial_video_adsterra_meta_box_html',
        'serial_video',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'serial_video_adsterra_meta_box');

function serial_video_adsterra_meta_box_html($post) {
    $adsterra_url = get_post_meta($post->ID, '_adsterra_direct_link_url', true);
    $ad_count = get_post_meta($post->ID, '_adsterra_ad_count', true);
    wp_nonce_field('serial_video_adsterra_nonce_action', 'serial_video_adsterra_nonce');
    ?>
    <p>
        <label for="adsterra_url"><strong>URL Direct Link:</strong></label><br>
        <input type="url" id="adsterra_url" name="adsterra_url" value="<?php echo esc_url($adsterra_url); ?>" class="widefat" placeholder="https://..." />
    </p>
    <p>
        <label for="ad_count"><strong>Jumlah Episode Iklan:</strong></label><br>
        <input type="number" id="ad_count" name="ad_count" value="<?php echo esc_attr($ad_count); ?>" class="widefat" min="0" placeholder="Contoh: 2" />
        <small>Masukkan angka. Episode akan dipilih secara acak. Kosongkan atau isi 0 untuk menonaktifkan.</small>
    </p>
    <?php
}

function serial_video_save_adsterra_meta_box($post_id) {
    if (!isset($_POST['serial_video_adsterra_nonce']) || !wp_verify_nonce($_POST['serial_video_adsterra_nonce'], 'serial_video_adsterra_nonce_action')) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['adsterra_url'])) {
        update_post_meta($post_id, '_adsterra_direct_link_url', esc_url_raw($_POST['adsterra_url']));
    }
    if (isset($_POST['ad_count'])) {
        update_post_meta($post_id, '_adsterra_ad_count', absint($_POST['ad_count']));
    }
}
add_action('save_post_serial_video', 'serial_video_save_adsterra_meta_box');


// =============================================================================
// BAGIAN 3: ENQUEUE SCRIPTS & STYLES (VERSI CDN)
// =============================================================================
function serial_video_admin_scripts($hook) {
    global $post;
    if (($hook == 'post-new.php' || $hook == 'post.php') && $post && 'serial_video' === $post->post_type) {
        wp_enqueue_script('serial-video-admin-js', get_template_directory_uri() . '/assets/js/admin-episodes.js', ['jquery'], '1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'serial_video_admin_scripts');

function sudutcerita_enqueue_assets() {
    if (is_singular('serial_video')) {
        // 1. Memuat CSS
        wp_enqueue_style('sudutcerita-main-style', get_template_directory_uri() . '/assets/css/main.min.css', [], '1.0');

        // 2. Mempersiapkan data untuk JavaScript
        $episodes = get_post_meta(get_the_ID(), '_serial_video_episodes', true);
        $episodes_data = [];
        $adsterra_url = get_post_meta(get_the_ID(), '_adsterra_direct_link_url', true);
        $ad_count = absint(get_post_meta(get_the_ID(), '_adsterra_ad_count', true));

        if (!empty($episodes) && is_array($episodes)) {
            usort($episodes, function($a, $b) { return $a['nomor'] <=> $b['nomor']; });

            $total_episodes = count($episodes);
            $ad_indices = [];

            if (!empty($adsterra_url) && $ad_count > 0 && $total_episodes > 0) {
                $ad_count = min($ad_count, $total_episodes);
                $possible_indices = range(0, $total_episodes - 1);
                shuffle($possible_indices);
                $ad_indices = array_slice($possible_indices, 0, $ad_count);
            }

            foreach($episodes as $index => $episode) {
                if (in_array($index, $ad_indices)) {
                    $episodes_data[] = [
                        'episode'      => $episode['nomor'],
                        'is_ad'        => true,
                        'ad_src'       => $adsterra_url,
                        'original_src' => $episode['url']
                    ];
                } else {
                    $episodes_data[] = [
                        'episode' => $episode['nomor'],
                        'is_ad'   => false,
                        'src'     => $episode['url']
                    ];
                }
            }
        }

        $data_for_js = [
            'id'       => 'series_' . get_the_ID(),
            'title'    => get_the_title(),
            'poster'   => get_the_post_thumbnail_url(get_the_ID(), 'medium'),
            'total'    => count($episodes_data),
            'synopsis' => get_the_content(),
            'episodes' => $episodes_data
        ];

        // 3. Memuat file JS utama dengan versi dinamis untuk cache busting
        $script_ver = filemtime( get_theme_file_path( '/assets/js/main.min.js' ) );
        wp_enqueue_script('sudutcerita-main-script', get_template_directory_uri() . '/assets/js/main.min.js', [], $script_ver, true);

        // 4. Mengirim data ke JavaScript menggunakan wp_localize_script (cara lama namun diperlukan oleh skrip)
        wp_localize_script('sudutcerita-main-script', 'seriesData', $data_for_js);
    }
}
add_action('wp_enqueue_scripts', 'sudutcerita_enqueue_assets');

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
