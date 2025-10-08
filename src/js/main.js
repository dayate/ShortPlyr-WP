import { fetchDataAndInitialize, hidePlayerLoader } from './apiClient.js';
import {
  handleEpisodeSelection,
  nextEpisode,
  prevEpisode,
  initFullscreen,
  initPlayerGestures,
} from './player.js';
import { initSheet, closeSheet } from './sheet.js';
import { getDOMElements, populateUI } from './ui.js';

(function () {
  /* =============================== STATE =================================== */
  const state = {
    currentEp: 1,
    EPISODES: [],
    SERIES_TITLE: '',
    TOTAL: 0,
  };

  /* =============================== DOM ===================================== */
  const postId = window.shortplyrData?.post_id;

  /* ============================== Init ===================================== */
  document.addEventListener('DOMContentLoaded', async () => {
    // Ambil elemen setelah DOM sepenuhnya siap
    const elements = getDOMElements();
    
    // Tambahkan pengecekan bahwa elemen player tersedia sebelum inisialisasi
    if (!elements.xgContainer) {
      return;
    }

    // Read saved progress from localStorage
    if (postId) {
        const savedEp = localStorage.getItem(`shortplyr_progress_${postId}`);
        if (savedEp) {
            state.currentEp = parseInt(savedEp, 10);
        }
    }

    initSheet(elements.sheet, elements.openBtn, elements.sheetScroll);
    initFullscreen(elements.fullBtn, elements.wrap);
    initPlayerGestures(
      elements.playerFull,
      elements.playbackIndicator,
      elements.playbackSpeedText,
      elements.sheet,
      elements.openBtn
    );

    // Definisikan callbacks setelah elements tersedia
    const callbacks = {
      onEpisodeChange: () => closeSheet(elements.sheet, elements.openBtn),
      onLoadedMetadata: () => hidePlayerLoader(elements.playerLoader),
      onPlayerError: () => hidePlayerLoader(elements.playerLoader),
      onEnded: () => nextEpisode(state, elements, callbacks, postId),
    };

    elements.prevEpBtn.onclick = () => prevEpisode(state, elements, callbacks, postId);
    elements.nextEpBtn.onclick = () => nextEpisode(state, elements, callbacks, postId);

    elements.synopsisToggle?.addEventListener('click', () => {
      const isExpanded =
        elements.synopsisToggle.getAttribute('aria-expanded') === 'true';
      elements.synopsisToggle.setAttribute('aria-expanded', String(!isExpanded));
      elements.synopsisContent.setAttribute('aria-expanded', String(!isExpanded));
    });

    const wrappedPopulateUI = (seriesData) => {
      // Update state dengan data baru
      state.EPISODES = seriesData.episodes;
      state.SERIES_TITLE = seriesData.title;
      state.TOTAL = seriesData.total;
      
      populateUI(seriesData, elements, state, (n) => handleEpisodeSelection(n, state, elements, callbacks, postId));
    }

    await fetchDataAndInitialize(wrappedPopulateUI);
  });
})();