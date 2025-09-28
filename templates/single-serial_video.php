<?php
// Template ini sekarang hanya sebagai kerangka (skeleton).
// Data akan dimuat secara asynchronous oleh main.js melalui REST API.
?>
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
            
            <div id="player-loader" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-black transition-opacity duration-500">
                <div class="loader">
                  <svg viewBox="0 0 80 80">
                    <rect x="8" y="8" width="64" height="64"></rect>
                  </svg>
                </div>
                <p class="text-white mt-4 text-sm">Memuat Data...</p>
            </div>

            <div id="xgplayer" class="xgplayer-container absolute inset-0 w-full h-full bg-black overflow-hidden" aria-label="xgplayer"></div>

            <div id="overlayHint" class="overlay-hint absolute left-4 top-4 z-[3] pointer-events-none transition-opacity duration-300" aria-live="polite">
                <h3 id="ovTitle" class="text-base sm:text-lg font-semibold text-white [text-shadow:_0_2px_6px_rgba(0,0,0,.55)]"></h3>
            </div>

            <div id="playbackIndicator" class="absolute top-5 left-1/2 -translate-x-1/2 z-[3] flex items-center gap-2 text-white font-bold text-sm pointer-events-none opacity-0 transition-opacity duration-200 [text-shadow:_0_1px_4px_rgba(0,0,0,.7)]" aria-hidden="true">
                <i class="ri-speed-fill text-xl"></i>
                <span id="playbackSpeedText"></span>
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
            <div class="sheet-scroll no-scrollbar h-[calc(100%-25px)] overflow-y-auto px-5 pt-4 pb-0 scroll-smooth">
                <section class="detail flex flex-col gap-4 items-stretch">
                    <div class="detail-header flex gap-4 items-center">
                        <img class="poster w-[110px] h-[148px] object-cover rounded-lg bg-zinc-800"
                             src="" 
                             alt=""
                             width="110"
                             height="148"
                             loading="lazy" />
                        <div class="meta">
                            <h1 class="title text-lg sm:text-xl font-bold mb-1.5"></h1>
                            <p class="sub text-sm text-gray-400">Tamat • <span id="totalEps"></span> episode</p>
                        </div>
                    </div>
                    <div class="synopsis-container mt-4 w-full">
                        <button id="synopsisToggle" class="synopsis-toggle flex items-center gap-2 w-full text-left p-0 text-base font-semibold" aria-expanded="false" aria-controls="synopsisContent">
                            <span>Sinopsis</span><i class="ri-arrow-down-s-line text-2xl transition-transform duration-200"></i>
                        </button>
                        <div id="synopsisContent" class="synopsis-content grid grid-rows-[0fr] transition-[grid-template-rows] duration-300 ease-out">
                            <div class="overflow-hidden">
                                <div class="synopsis-text text-sm text-gray-400 leading-relaxed mt-3">
                                </div>
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
