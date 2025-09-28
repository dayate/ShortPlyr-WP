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