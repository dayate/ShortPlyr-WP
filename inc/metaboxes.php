<?php
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