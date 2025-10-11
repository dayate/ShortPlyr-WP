<?php
/**
 * Template untuk menampilkan arsip serial_video
 * Menyerupai tampilan dari card.html
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php post_type_archive_title(); ?> - ShortPlyr</title>
    <?php wp_head(); ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
    />
    <link
      href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css"
      rel="stylesheet"
    />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              dark: '#0f1419',
              primary: '#f8f9fa',
              secondary: '#9ca3af',
            },
          },
        },
      };
    </script>
    <style>
      .swiper-pagination-bullet {
        background: rgba(255, 255, 255, 0.5);
        width: 8px;
        height: 8px;
        opacity: 1;
      }
      .swiper-pagination-bullet-active {
        background: white;
        width: 24px;
        border-radius: 4px;
      }
    </style>
</head>
<body <?php body_class('min-h-screen bg-dark text-primary'); ?>>

    <?php
    // Buat query untuk mengambil semua post serial_video (tidak hanya dari API)
    $args = array(
        'post_type' => 'serial_video',
        'post_status' => 'publish',
        'posts_per_page' => -1, // Ambil semua post untuk ditampilkan
        'orderby' => 'date',
        'order' => 'DESC'
    );

    $serial_query = new WP_Query($args);
    ?>

    <section class="container mx-auto max-w-[720px] px-4 pt-12">
        <div class="mb-8 text-left">
            <h2 class="text-3xl font-bold">Rekomendasi untuk Anda</h2>
        </div>
        <div class="swiper hero-swiper pb-12">
            <div class="swiper-wrapper">
                <?php
                // Ambil 4 postingan pertama untuk slider hero
                $hero_posts = array_slice($serial_query->posts, 0, 4);
                foreach ($hero_posts as $post) {
                    setup_postdata($post);
                    
                    // Tentukan mode entri data (manual atau API)
                    $data_mode = get_post_meta(get_the_ID(), '_data_source_mode', true) ?: 'api';

                    if ($data_mode === 'manual') {
                        // Mode manual: ambil data dari editor WordPress dan custom field
                        $book_name = get_the_title(); // Judul dari editor WordPress
                        $abstract = get_the_excerpt() ?: wp_trim_words(get_the_content(), 20, '...'); // Deskripsi dari konten post
                        // Ambil poster dari field custom '_serial_video_poster_url' jika sumber poster adalah metabox
                        $poster_source = get_post_meta(get_the_ID(), '_poster_source_choice', true) ?: 'api';

                        if ($poster_source === 'metabox') {
                            $thumb_url = get_post_meta(get_the_ID(), '_serial_video_poster_url', true);
                        } elseif ($poster_source === 'featured') {
                            $thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                        } else {
                            // Jika bukan dari metabox atau featured image, coba dari data API jika tersedia
                            $thumb_url = get_post_meta(get_the_ID(), '_extracted_thumb_url', true);
                        }
                    } else {
                        // Mode API: ambil data dari post meta yang diambil dari API
                        $book_name = get_post_meta(get_the_ID(), '_extracted_book_name', true);
                        $abstract = get_post_meta(get_the_ID(), '_extracted_abstract', true);
                        $thumb_url = get_post_meta(get_the_ID(), '_extracted_thumb_url', true);
                    }
                    

                    ?>
                    <div class="swiper-slide">
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="block">
                            <div
                                class="group relative aspect-[2/3] w-full cursor-pointer overflow-hidden rounded-xl shadow-lg shadow-black/30 transition-transform duration-300 ease-in-out hover:scale-105"
                            >
                                <?php if ($thumb_url) : ?>
                                    <img 
                                        <?php if (preg_match('/\.(heic|heif)$/i', parse_url($thumb_url, PHP_URL_PATH))) : ?>
                                        data-heic-conversion="true" 
                                        data-original-src="<?php echo esc_url($thumb_url); ?>" 
                                        <?php endif; ?>
                                        src="<?php echo esc_url($thumb_url); ?>" 
                                        alt="<?php echo esc_attr($book_name); ?>" 
                                        class="h-full w-full object-cover">
                                <?php else : ?>
                                    <div class="w-full h-full flex items-center justify-center bg-gray-800">
                                        <span class="text-gray-500 text-sm">No Image</span>
                                    </div>
                                <?php endif; ?>
                                <div
                                    class="absolute inset-0 flex items-center justify-center bg-black/0 p-6 text-center transition-colors duration-300 ease-in-out group-hover:bg-black/80"
                                >
                                    <div
                                        class="opacity-0 transition-opacity duration-300 ease-in-out group-hover:opacity-100"
                                    >
                                        <h3 class="mb-3 text-xl font-bold leading-tight text-white">
                                            <?php echo !empty($book_name) ? esc_html($book_name) : get_the_title(); ?>
                                        </h3>
                                        <?php if ($abstract) : ?>
                                            <p class="text-sm leading-relaxed text-gray-200 line-clamp-4">
                                                <?php echo esc_html($abstract); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php } ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>

    <main class="container mx-auto max-w-[720px] px-4 pb-12">
        <h2 class="mb-8 text-3xl font-bold">Jelajahi Koleksi</h2>
        <div
            id="card-grid"
            class="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 md:grid-cols-4"
        >
            <?php
            // Reset post data dan mulai loop dari awal
            wp_reset_postdata();
            $serial_query->rewind_posts();
            
            // Ambil semua postingan kecuali yang sudah ditampilkan di hero
            $hero_post_ids = array_map(function($post) { return $post->ID; }, $hero_posts);
            
            while ($serial_query->have_posts()) : $serial_query->the_post();
                if (in_array(get_the_ID(), $hero_post_ids)) continue; // Lewati postingan yang sudah ditampilkan di hero
            ?>
                <?php
                // Tentukan mode entri data (manual atau API)
                $data_mode = get_post_meta(get_the_ID(), '_data_source_mode', true) ?: 'api';

                if ($data_mode === 'manual') {
                    // Mode manual: ambil data dari editor WordPress dan custom field
                    $book_name = get_the_title(); // Judul dari editor WordPress
                    $abstract = get_the_excerpt() ?: wp_trim_words(get_the_content(), 20, '...'); // Deskripsi dari konten post
                    // Ambil poster dari field custom '_serial_video_poster_url' jika sumber poster adalah metabox
                    $poster_source = get_post_meta(get_the_ID(), '_poster_source_choice', true) ?: 'api';

                    if ($poster_source === 'metabox') {
                        $thumb_url = get_post_meta(get_the_ID(), '_serial_video_poster_url', true);
                    } elseif ($poster_source === 'featured') {
                        $thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                    } else {
                        // Jika bukan dari metabox atau featured image, coba dari data API jika tersedia
                        $thumb_url = get_post_meta(get_the_ID(), '_extracted_thumb_url', true);
                    }
                } else {
                    // Mode API: ambil data dari post meta yang diambil dari API
                    $book_name = get_post_meta(get_the_ID(), '_extracted_book_name', true);
                    $abstract = get_post_meta(get_the_ID(), '_extracted_abstract', true);
                    $thumb_url = get_post_meta(get_the_ID(), '_extracted_thumb_url', true);
                }
                

                ?>
                
                <a href="<?php echo esc_url(get_permalink()); ?>" class="block">
                    <div
                        class="group relative aspect-[2/3] w-full cursor-pointer overflow-hidden rounded-xl shadow-lg shadow-black/30 transition-transform duration-300 ease-in-out hover:scale-105"
                    >
                        <?php if ($thumb_url) : ?>
                            <img 
                                <?php if (preg_match('/\.(heic|heif)$/i', parse_url($thumb_url, PHP_URL_PATH))) : ?>
                                data-heic-conversion="true" 
                                data-original-src="<?php echo esc_url($thumb_url); ?>" 
                                <?php endif; ?>
                                src="<?php echo esc_url($thumb_url); ?>" 
                                alt="<?php echo esc_attr($book_name); ?>" 
                                class="h-full w-full object-cover">
                        <?php else : ?>
                            <div class="w-full h-full flex items-center justify-center bg-gray-800">
                                <span class="text-gray-500 text-sm">No Image</span>
                            </div>
                        <?php endif; ?>
                        <div
                            class="absolute inset-0 flex items-center justify-center bg-black/0 p-4 text-center transition-colors duration-300 ease-in-out group-hover:bg-black/80"
                        >
                            <div
                                class="opacity-0 transition-opacity duration-300 ease-in-out group-hover:opacity-100"
                            >
                                <h3 class="text-base font-bold leading-tight text-white">
                                    <?php echo !empty($book_name) ? esc_html($book_name) : get_the_title(); ?>
                                </h3>
                                <?php if ($abstract) : ?>
                                    <p class="text-sm leading-relaxed text-gray-200 line-clamp-4 mt-2">
                                        <?php echo esc_html($abstract); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
        <div
            id="pagination-controls"
            class="mt-12 flex flex-wrap justify-center items-center gap-2"
        ></div>
    </main>

    <script>
        // Inisialisasi Swiper
        const heroSwiper = new Swiper('.hero-swiper', {
            slidesPerView: 1.5,
            spaceBetween: 16,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                dynamicBullets: true,
            },
            breakpoints: {
                640: { slidesPerView: 2.5, spaceBetween: 20 },
                720: { slidesPerView: 3, spaceBetween: 24 },
            },
        });

        // --- LOGIKA PAGINATION ---
        document.addEventListener('DOMContentLoaded', () => {
            const gridContainer = document.getElementById('card-grid');
            const items = Array.from(gridContainer.querySelectorAll('a.block'));
            const controlsContainer = document.getElementById(
                'pagination-controls',
            );

            if (items.length === 0) return;

            const itemsPerPage = 8; // Menampilkan 8 item per halaman
            let currentPage = 1;
            const totalPages = Math.ceil(items.length / itemsPerPage);

            function showPage(page) {
                currentPage = page;
                const startIndex = (page - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;

                items.forEach((item, index) => {
                    if (index >= startIndex && index < endIndex) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
                renderControls();
            }

            function renderControls() {
                controlsContainer.innerHTML = '';

                // Tombol Sebelumnya dengan Ikon
                const prevButton = document.createElement('button');
                prevButton.innerHTML = `<i class="ri-arrow-left-s-line"></i>`;
                prevButton.disabled = currentPage === 1;
                prevButton.className =
                    'p-2 text-xl leading-none rounded-md border border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed';
                prevButton.onclick = () => showPage(currentPage - 1);
                controlsContainer.appendChild(prevButton);

                // Tombol angka
                for (let i = 1; i <= totalPages; i++) {
                    const pageButton = document.createElement('button');
                    pageButton.innerText = i;
                    pageButton.className = `px-4 py-2 text-sm font-medium rounded-md border ${
                        currentPage === i
                            ? 'bg-primary text-dark border-primary'
                            : 'border-gray-600'
                    }`;
                    pageButton.onclick = () => showPage(i);
                    controlsContainer.appendChild(pageButton);
                }

                // Tombol Berikutnya dengan Ikon
                const nextButton = document.createElement('button');
                nextButton.innerHTML = `<i class="ri-arrow-right-s-line"></i>`;
                nextButton.disabled = currentPage === totalPages;
                nextButton.className =
                    'p-2 text-xl leading-none rounded-md border border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed';
                nextButton.onclick = () => showPage(currentPage + 1);
                controlsContainer.appendChild(nextButton);
            }

            if (totalPages > 1) {
                showPage(1);
            }
        });
    </script>

    <?php wp_footer(); ?>
</body>
</html>