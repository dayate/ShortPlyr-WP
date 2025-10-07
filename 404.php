<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package ShortPlyr-WP
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-black text-gray-200 font-sans'); ?>>

    <main class="wrap mx-auto min-h-dvh relative overflow-hidden bg-black flex items-center justify-center max-w-[720px] fullscreen:max-w-full">
        <section class="text-center p-4">

            <h1 class="text-8xl md:text-9xl font-bold text-white mb-4">404</h1>

            <p class="text-lg md:text-xl text-gray-300 mb-8">
                Halaman yang Anda cari tidak dapat ditemukan.
            </p>

            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
               class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-300 ease-in-out">
                Kembali ke Halaman Utama
            </a>

        </section>
    </main>

    <?php wp_footer(); ?>
</body>
</html>