
import { processAndDisplayPoster } from './apiClient.js';

export const clamp = (n, min, max) => Math.max(min, Math.min(n, max));

export const getDOMElements = () => ({
  playerLoader: document.querySelector('#player-loader'),
  wrap: document.querySelector('.wrap'),
  playerFull: document.querySelector('.player-full'),
  sheet: document.querySelector('#sheet'),
  openBtn: document.querySelector('#openSheetBtn'),
  fullBtn: document.querySelector('#fullBtn'),
  prevEpBtn: document.querySelector('#prevEpBtn'),
  nextEpBtn: document.querySelector('#nextEpBtn'),
  grid: document.querySelector('#episodeGrid'),
  overlayHint: document.querySelector('#overlayHint'),
  ovTitle: document.querySelector('#ovTitle'),
  sideControls: document.querySelector('#sideControls'),
  sheetScroll: document.querySelector('.sheet-scroll'),
  synopsisToggle: document.querySelector('#synopsisToggle'),
  synopsisContent: document.querySelector('#synopsisContent'),
  playbackIndicator: document.querySelector('#playbackIndicator'),
  playbackSpeedText: document.querySelector('#playbackSpeedText'),
  posterImg: document.querySelector('.poster'),
  titleEl: document.querySelector('.title'),
  totalEpsEl: document.querySelector('#totalEps'),
  synopsisTextEl: document.querySelector('.synopsis-text'),
  xgContainer: document.querySelector('#xgplayer'),
});

export const setPlayingUI = (p, overlayHint) =>
  overlayHint?.classList.toggle('opacity-0', !!p);

export const setOverlay = (ovTitle, currentEp, seriesTitle) => {
  ovTitle.textContent = `Ep ${currentEp} | ${seriesTitle || 'Judul'}`;
};

export const buildGrid = (total, grid, currentEp, handleEpisodeSelection) => {
  if (!total || !grid) return;
  grid.innerHTML = '';
  const f = document.createDocumentFragment();
  for (let i = 1; i <= total; i++) {
    const li = document.createElement('li');
    const btn = document.createElement('button');
    btn.className =
      'ep-btn w-12 h-12 rounded border border-gray-700 bg-gray-800 text-white flex items-center justify-center font-bold text-sm transition-colors hover:bg-gray-700 aria-[current=true]:bg-white aria-[current=true]:text-zinc-900';
    btn.type = 'button';
    btn.textContent = i;
    btn.dataset.ep = i;
    if (i === currentEp) {
      btn.setAttribute('aria-current', 'true');
    }
    btn.onclick = () => handleEpisodeSelection(i);
    li.appendChild(btn);
    f.appendChild(li);
  }
  grid.appendChild(f);
};

export const populateUI = async (seriesData, elements, state, handleEpisodeSelection) => {
  state.SERIES_TITLE = seriesData.title || 'Tanpa Judul';
  state.TOTAL = Number(seriesData.total) || 0;
  state.EPISODES = Array.isArray(seriesData.episodes) ? seriesData.episodes : [];

  const posterUrl = seriesData.poster_url;

  if (posterUrl) {
    await processAndDisplayPoster(
      posterUrl,
      elements.posterImg,
      `Poster ${state.SERIES_TITLE}`
    );
  } else {
    elements.posterImg.src = '';
    elements.posterImg.alt = 'Poster tidak tersedia';
  }

  if (elements.titleEl) elements.titleEl.textContent = state.SERIES_TITLE;
  if (elements.totalEpsEl) elements.totalEpsEl.textContent = state.TOTAL;
  if (elements.synopsisTextEl) elements.synopsisTextEl.innerHTML = seriesData.synopsis;

  setOverlay(elements.ovTitle, state.currentEp, state.SERIES_TITLE);
  buildGrid(state.TOTAL, elements.grid, state.currentEp, handleEpisodeSelection);

  if (state.TOTAL > 0) {
    handleEpisodeSelection(state.currentEp);
  } else {
    showError('Tidak ada episode yang tersedia.', elements.titleEl, elements.xgContainer);
  }
};
