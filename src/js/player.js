
import Player from 'xgplayer';
import { clamp, setPlayingUI, setOverlay } from './ui.js';
import { showError } from './apiClient.js';

let xg = null;
let sideControlsTimer = null;

const hideSideControls = (sideControls) =>
  sideControls?.classList.add('opacity-0', 'pointer-events-none');
const showSideControls = (sideControls) =>
  sideControls?.classList.remove('opacity-0', 'pointer-events-none');

const resetSideControlsTimer = (sideControls) => {
  showSideControls(sideControls);
  clearTimeout(sideControlsTimer);
  if (xg && !xg.paused) {
    sideControlsTimer = setTimeout(() => hideSideControls(sideControls), 3000);
  }
};

const mountXG = (url, elements, state, callbacks) => {
  if (xg?.destroy) {
    try {
      xg.destroy(false);
    } catch {}
  }

  xg = new Player({
    id: 'xgplayer',
    url,
    autoplay: true,
    width: '100%',
    height: '100%',
    lang: 'id',
    playsinline: true,
    fitVideoSize: 'contain',
    controls: { name: 'play' },
    ignores: [
      'playbackRate',
      'definition',
      'fullscreen',
      'pip',
      'airplay',
      'download',
      'cssFullscreen',
      'screenShot',
      'miniProgress',
      'timePreview',
      'playNext',
    ],
  });

  xg.on('loadedmetadata', callbacks.onLoadedMetadata);
  xg.on('play', () => {
    setPlayingUI(true, elements.overlayHint);
    resetSideControlsTimer(elements.sideControls);
  });
  xg.on('pause', () => {
    setPlayingUI(false, elements.overlayHint);
    clearTimeout(sideControlsTimer);
    showSideControls(elements.sideControls);
  });
  xg.on('ended', callbacks.onEnded);
  xg.on('error', () => {
    if (elements.ovTitle) elements.ovTitle.textContent = `Gagal memuat Ep ${state.currentEp}`;
    callbacks.onPlayerError();
  });

  elements.playerFull.addEventListener('mousemove', () => resetSideControlsTimer(elements.sideControls));
  elements.playerFull.addEventListener('touchstart', () => resetSideControlsTimer(elements.sideControls));
  resetSideControlsTimer(elements.sideControls);
};

export const setEpisode = (n, state, elements, callbacks) => {
  if (!state.TOTAL) return;
  state.currentEp = clamp(n, 1, state.TOTAL);
  setOverlay(elements.ovTitle, state.currentEp, state.SERIES_TITLE);
  document
    .querySelectorAll('.ep-btn')
    .forEach((b) =>
      b.setAttribute(
        'aria-current',
        String(Number(b.dataset.ep) === state.currentEp)
      )
    );
  const episodeData = state.EPISODES[state.currentEp - 1];
  const src = episodeData.original_src || episodeData.src;
  if (!src) {
    showError(`Video Ep ${state.currentEp} tidak ditemukan.`, elements.titleEl, elements.xgContainer);
    return;
  }
  mountXG(src, elements, state, callbacks);
  setPlayingUI(false, elements.overlayHint);
  callbacks.onEpisodeChange();
};

export const handleEpisodeSelection = (n, state, elements, callbacks) => {
  const targetEp = clamp(n, 1, state.TOTAL);
  if (!targetEp) return;
  const episodeData = state.EPISODES[targetEp - 1];
  if (!episodeData) return;
  if (episodeData.is_ad) {
    const storageKey = `clickedAds_${episodeData.id || 'series'}`;
    const clickedAds = JSON.parse(sessionStorage.getItem(storageKey) || '[]');
    if (!clickedAds.includes(targetEp)) {
      window.open(episodeData.ad_src, '_blank');
      clickedAds.push(targetEp);
      sessionStorage.setItem(storageKey, JSON.stringify(clickedAds));
      return;
    }
  }
  setEpisode(targetEp, state, elements, callbacks);
};

export const nextEpisode = (state, elements, callbacks) => handleEpisodeSelection(state.currentEp + 1, state, elements, callbacks);
export const prevEpisode = (state, elements, callbacks) => handleEpisodeSelection(state.currentEp - 1, state, elements, callbacks);

export const initFullscreen = (fullBtn, wrap) => {
  const fs = {
    is: () =>
      !!(document.fullscreenElement || document.webkitFullscreenElement),
    enter: (el) =>
      (el.requestFullscreen || el.webkitRequestFullscreen).call(el),
    exit: () =>
      (document.exitFullscreen || document.webkitExitFullscreen).call(document),
  };
  const onFsChange = () => {
    const on = fs.is();
    fullBtn?.setAttribute('data-fs', on ? 'on' : 'off');
  };

  fullBtn.onclick = () => {
    fs.is() ? fs.exit() : fs.enter(wrap);
  };

  document.addEventListener('fullscreenchange', onFsChange);
  document.addEventListener('webkitfullscreenchange', onFsChange);
  onFsChange();
};

const showIndicator = (speed, playbackIndicator, playbackSpeedText) => {
  if (playbackIndicator && playbackSpeedText) {
    playbackSpeedText.textContent = `${speed}x`;
    playbackIndicator.classList.remove('opacity-0');
    playbackIndicator.setAttribute('aria-hidden', 'false');
  }
};
const hideIndicator = (playbackIndicator) => {
  if (playbackIndicator) {
    playbackIndicator.classList.add('opacity-0');
    playbackIndicator.setAttribute('aria-hidden', 'true');
  }
};

export const initLongPress = (playerFull, playbackIndicator, playbackSpeedText) => {
  if (window.innerWidth > 768) return;
  let pressTimer = null;
  const startPress = (e) => {
    if (
      e.target.closest('.xgplayer-controls, .xgplayer-enter, #sideControls')
    ) {
      return;
    }
    pressTimer = setTimeout(() => {
      if (xg && !xg.paused) {
        const newSpeed = 2;
        xg.playbackRate = newSpeed;
        showIndicator(newSpeed, playbackIndicator, playbackSpeedText);
      }
    }, 700);
  };
  const endPress = () => {
    clearTimeout(pressTimer);
    if (xg && xg.playbackRate !== 1) {
      xg.playbackRate = 1;
      hideIndicator(playbackIndicator);
    }
  };
  playerFull.addEventListener('touchstart', startPress, { passive: true });
  playerFull.addEventListener('touchend', endPress);
  playerFull.addEventListener('touchcancel', endPress);
};
