<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <?php wp_head(); ?>
</head>
<body <?php body_class('bg-black text-gray-200'); ?>>

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

    <main class="wrap mx-auto min-h-dvh relative overflow-hidden bg-[#0f1114] max-w-[720px] fullscreen:max-w-full">
        <section class="player-full relative w-full h-dvh bg-black overflow-hidden" aria-label="Video Player">
            <div id="playerSkeleton" class="absolute inset-0 flex items-end justify-between pointer-events-none bg-gradient-to-t from-black/60 via-black/20 to-white/5" aria-hidden="false">
                <div class="sk-bar h-[10px] w-3/5 m-6 rounded-full"></div>
                <div class="sk-chip h-[34px] w-[90px] m-4 rounded-lg"></div>
            </div>

            <div id="xgplayer" class="xgplayer-container absolute inset-0 w-full h-full bg-black overflow-hidden" aria-label="xgplayer"></div>

            <div id="overlayHint" class="overlay-hint absolute left-4 top-4 z-[3] pointer-events-none transition-opacity duration-300" aria-live="polite">
                <h3 id="ovTitle" class="text-base sm:text-lg font-semibold text-white [text-shadow:_0_2px_6px_rgba(0,0,0,.55)]"></h3>
            </div>

            <div id="playbackIndicator" class="absolute top-5 left-1/2 -translate-x-1/2 z-[3] flex items-center gap-2 text-white font-bold text-sm pointer-events-none opacity-0 transition-opacity duration-200 [text-shadow:_0_1px_4px_rgba(0,0,0,.7)]" aria-hidden="true">
                <i class="ri-speed-fill text-xl"></i>
                <span id="playbackSpeedText">2x</span>
            </div>

            <nav id="sideControls" class="side-controls absolute bottom-[70px] right-3 flex flex-col gap-2.5 z-[4] transition-opacity duration-200" aria-label="Kontrol Samping Player">
                <button id="prevEpBtn" class="side-btn relative flex items-center justify-center text-white p-1 min-w-12 min-h-12" aria-label="Episode sebelumnya" title="Prev"><i class="ri-skip-up-fill text-2xl"></i></button>
                <button id="nextEpBtn" class="side-btn relative flex items-center justify-center text-white p-1 min-w-12 min-h-12" aria-label="Episode berikutnya" title="Next"><i class="ri-skip-down-fill text-2xl"></i></button>
                <button id="openSheetBtn" class="side-btn relative flex items-center justify-center text-white p-1 min-w-12 min-h-12" aria-controls="sheet" aria-expanded="false" aria-label="Buka daftar episode" title="Episodes"><i class="ri-menu-fill text-2xl"></i></button>
                <button id="fullBtn" class="group side-btn relative flex items-center justify-center text-white p-1 min-w-12 min-h-12" aria-label="Masuk layar penuh" title="Fullscreen" data-fs="off">
                    <i class="ri-fullscreen-fill text-2xl block group-data-[fs=on]:hidden"></i>
                    <i class="ri-fullscreen-exit-fill text-2xl hidden group-data-[fs=on]:block"></i>
                </button>
            </nav>
        </section>

        <section id="sheet" class="sheet absolute inset-x-0 bottom-0 h-full bg-[#171a1f] rounded-t-lg shadow-2xl z-10 cursor-grab active:cursor-grabbing" aria-hidden="true">
            <header id="sheetHandle" class="sheet-handle sticky top-0 flex justify-center items-center h-[25px] pt-2.5" title="Tarik untuk memperbesar/menutup" aria-label="Tarik untuk memperbesar/menutup">
                <svg viewBox="0 0 24 24" width="100" height="40" aria-hidden="true"><path d="M4 12h16" stroke="#5b616a" stroke-width="3" stroke-linecap="round" /></svg>
            </header>
            <div class="sheet-scroll h-[calc(100%-25px)] overflow-y-auto px-5 pt-4 pb-0 scroll-smooth">
                <section class="detail flex flex-col gap-4 items-stretch">
                    <div class="detail-header flex gap-4 items-center">
                        <?php
// Dapatkan URL gambar poster
$poster_url = get_the_post_thumbnail_url(get_the_ID(), 'medium'); // Gunakan ukuran yang lebih sesuai, bukan 'full'
?>
<img class="poster w-[110px] h-[148px] object-cover rounded-lg bg-zinc-800"
     src="<?php echo esc_url($poster_url); ?>"
     alt="Poster <?php the_title_attribute(); ?>"
     width="110"
     height="148"
     loading="lazy" />
                        <div class="meta">
                            <h1 class="title text-lg sm:text-xl font-bold mb-1.5"><?php the_title(); ?></h1>
                            <?php $episodes_meta = get_post_meta(get_the_ID(), '_serial_video_episodes', true); ?>
                            <p class="sub text-sm text-gray-400">Tamat • <span id="totalEps"><?php echo !empty($episodes_meta) ? count($episodes_meta) : 0; ?></span> episode</p>
                        </div>
                    </div>
                    <div class="synopsis-container mt-4 w-full">
                        <button id="synopsisToggle" class="synopsis-toggle flex items-center gap-2 w-full text-left p-0 text-base font-semibold" aria-expanded="false" aria-controls="synopsisContent">
                            <span>Sinopsis</span><i class="ri-arrow-down-s-line text-2xl transition-transform duration-200"></i>
                        </button>
                        <div id="synopsisContent" class="synopsis-content grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-out">
                            <div class="overflow-hidden">
                                <div class="synopsis-text text-sm text-gray-400 leading-relaxed mt-3"><?php the_content(); ?></div>
                            </div>
                        </div>
                    </div>
                </section>
                <hr class="divider border-0 border-t border-gray-700/75 my-4" />
                <h2 class="section-title text-base sm:text-lg font-semibold mb-3">Daftar Episode</h2>
                <ul id="episodeGrid" class="episode-grid grid justify-center gap-2.5 p-1" role="list"></ul>
                <div class="safe-bottom h-12"></div>
            </div>
        </section>
    </main>

    <?php endwhile; endif; ?>

    <?php wp_footer(); ?>
</body>
</html>
