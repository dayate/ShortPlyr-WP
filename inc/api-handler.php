<?php
/**
 * Fungsi Helper untuk mengambil dan memproses semua data serial.
 * Menggunakan Transients API untuk caching.
 *
 * @param int $post_id ID dari post serial.
 * @return array Data yang sudah diproses untuk digunakan oleh JavaScript.
 */
function get_processed_serial_data($post_id) {
    $api_query = get_post_meta($post_id, '_api_query', true);
    $api_book_id = get_post_meta($post_id, '_api_book_id', true);
    $api_book_name = get_post_meta($post_id, '_api_book_name', true);

    // Buat kunci transient yang unik berdasarkan ID post.
    $transient_key = 'shortplyr_api_data_' . $post_id;

    // Coba ambil data dari cache terlebih dahulu.
    $cached_data = get_transient($transient_key);
    if (false !== $cached_data) {
        return $cached_data;
    }

    // Jika tidak ada di cache, lanjutkan untuk mengambil data baru.
    $episodes_data = [];
    $book_details = [];

    if (!empty($api_query) && !empty($api_book_id) && !empty($api_book_name)) {
        // Ambil URL API dari theme settings, dengan fallback ke URL lokal.
        $api_url = get_option('shortplyr_melolo_api_url', 'http://127.0.0.1:8000/api/search-and-get-details');

        $full_api_url = add_query_arg([
            'query' => urlencode($api_query),
            'book_id' => urlencode($api_book_id),
            'book_name' => urlencode($api_book_name)
        ], $api_url);

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
        } else {
            // Catat error jika API gagal.
            error_log('ShortPlyr API Error: ' . (is_wp_error($response) ? $response->get_error_message() : 'HTTP Code: ' . wp_remote_retrieve_response_code($response)));
        }
    }

    // Jika pengambilan data dari API gagal atau tidak ada info API, gunakan data manual.
    if (empty($episodes_data)) {
        $manual_episodes = get_post_meta($post_id, '_serial_video_episodes', true);
        if (!empty($manual_episodes) && is_array($manual_episodes)) {
             usort($manual_episodes, function($a, $b) { return $a['nomor'] <=> $b['nomor']; });
             foreach($manual_episodes as $episode) {
                 $episodes_data[] = ['episode' => $episode['nomor'], 'is_ad' => false, 'src' => $episode['url'], 'original_src' => $episode['url']];
             }
        }
    }

    // Logika untuk menambahkan iklan.
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

    // Logika untuk poster: prioritaskan URL kustom, lalu featured image.
    $custom_poster_url = get_post_meta($post_id, '_serial_video_poster_url', true);
    $poster_url = !empty($custom_poster_url) ? $custom_poster_url : get_the_post_thumbnail_url($post_id, 'medium');

    // Finalisasi judul dan sinopsis.
    $final_title = !empty($book_details['book_name']) ? $book_details['book_name'] : get_the_title();
    $final_synopsis = !empty($book_details['abstract']) ? nl2br(esc_html($book_details['abstract'])) : get_the_content();

    // Susun data final.
    $final_data = [
        'id'       => 'series_' . $post_id,
        'title'    => $final_title,
        'poster'   => $poster_url,
        'total'    => $total_episodes,
        'synopsis' => $final_synopsis,
        'episodes' => array_values($episodes_data)
    ];

    // Simpan data final ke dalam cache (transient) selama 1 jam.
    set_transient($transient_key, $final_data, 1 * HOUR_IN_SECONDS);

    return $final_data;
}

// Hapus transient saat post disimpan untuk memastikan data selalu segar setelah update.
function shortplyr_delete_api_cache_on_save($post_id) {
    if (get_post_type($post_id) === 'serial_video') {
        delete_transient('shortplyr_api_data_' . $post_id);
    }
}
add_action('save_post', 'shortplyr_delete_api_cache_on_save');

// =============================================================================
// BAGIAN 4: ENDPOINT REST API UNTUK AJAX LOADING
// =============================================================================

/**
 * Fungsi untuk memeriksa izin akses ke REST API endpoint.
 * Hanya permintaan dengan nonce yang valid yang diizinkan.
 *
 * @param WP_REST_Request $request Request object.
 * @return bool|WP_Error True jika diizinkan, WP_Error jika ditolak.
 */
function shortplyr_rest_permission_check(WP_REST_Request $request) {
    $nonce = $request->get_header('x-wp-nonce');
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('rest_forbidden', 'Nonce tidak valid.', ['status' => 401]);
    }
    return true;
}

/**
 * Mendaftarkan endpoint REST API kustom.
 * Endpoint: /wp-json/shortplyr/v1/serial/{id}
 */
function shortplyr_register_rest_endpoint() {
    register_rest_route('shortplyr/v1', '/serial/(?P<id>\d+)', [
        'methods'             => 'GET',
        'callback'            => 'shortplyr_get_serial_data_for_rest',
        'permission_callback' => 'shortplyr_rest_permission_check', // Menggunakan fungsi permission check
        'args'                => [
            'id' => [
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param);
                }
            ],
        ],
    ]);
}
add_action('rest_api_init', 'shortplyr_register_rest_endpoint');

/**
 * Callback function untuk endpoint REST API.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response|WP_Error Response object atau error.
 */
function shortplyr_get_serial_data_for_rest(WP_REST_Request $request) {
    $post_id = absint($request['id']);

    // Verifikasi apakah post ada dan merupakan tipe yang benar
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'serial_video') {
        return new WP_Error('not_found', 'Serial video tidak ditemukan.', ['status' => 404]);
    }

    // Panggil fungsi yang sudah ada untuk mengambil dan memproses data
    $data = get_processed_serial_data($post_id);

    if (empty($data) || empty($data['episodes'])) {
        return new WP_Error('no_data', 'Data untuk serial ini tidak dapat diambil.', ['status' => 500]);
    }

    return new WP_REST_Response($data, 200);
}