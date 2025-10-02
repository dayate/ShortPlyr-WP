import { fetchDataAndInitialize, hidePlayerLoader } from './apiClient.js';
import {
  handleEpisodeSelection,
  nextEpisode,
  prevEpisode,
  initFullscreen,
  initLongPress,
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
  const elements = getDOMElements();

  /* ============================== CALLBACKS ================================ */
  const onEpisodeChange = () => {
    closeSheet(elements.sheet, elements.openBtn);
  };

  const onLoadedMetadata = () => {
    hidePlayerLoader(elements.playerLoader);
  };

  const onPlayerError = () => {
    hidePlayerLoader(elements.playerLoader);
  };

  const callbacks = {
    onEpisodeChange,
    onLoadedMetadata,
    onPlayerError,
    onEnded: () => nextEpisode(state, elements, callbacks),
  };

  /* ============================== Init ===================================== */
  document.addEventListener('DOMContentLoaded', () => {
    initSheet(elements.sheet, elements.openBtn, elements.sheetScroll);
    initFullscreen(elements.fullBtn, elements.wrap);
    initLongPress(
      elements.playerFull,
      elements.playbackIndicator,
      elements.playbackSpeedText
    );

    elements.prevEpBtn.onclick = () => prevEpisode(state, elements, callbacks);
    elements.nextEpBtn.onclick = () => nextEpisode(state, elements, callbacks);

    elements.synopsisToggle?.addEventListener('click', () => {
      const isExpanded =
        elements.synopsisToggle.getAttribute('aria-expanded') === 'true';
      elements.synopsisToggle.setAttribute('aria-expanded', String(!isExpanded));
      elements.synopsisContent.setAttribute('aria-expanded', String(!isExpanded));
    });

    const wrappedPopulateUI = (seriesData) => {
        populateUI(seriesData, elements, state, (n) => handleEpisodeSelection(n, state, elements, callbacks));
    }

    fetchDataAndInitialize(wrappedPopulateUI);
  });
})();