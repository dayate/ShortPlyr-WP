import Player from 'xgplayer';
import { clamp, setPlayingUI, setOverlay } from './ui.js';
import { showError } from './apiClient.js';
import { openSheetMid, closeSheet } from './sheet.js';

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

  // Pastikan elemen DOM tersedia sebelum inisialisasi player
  if (!elements.xgContainer) {
    return;
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
    dblclick: false,
    closeVideoClick: true,
    seek: false, // Menonaktifkan fungsi seek pada progress bar
    closeVideoDblclick: true,
    closeVideoTouch: true, // Menonaktifkan gestur sentuh bawaan (swipe to seek)
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
    if (elements.ovTitle)
      elements.ovTitle.textContent = `Gagal memuat Ep ${state.currentEp}`;
    callbacks.onPlayerError();
  });

  elements.playerFull.addEventListener('mousemove', () =>
    resetSideControlsTimer(elements.sideControls),
  );
  elements.playerFull.addEventListener('touchstart', () =>
    resetSideControlsTimer(elements.sideControls),
  );
  resetSideControlsTimer(elements.sideControls);
};

export const setEpisode = (n, state, elements, callbacks, postId) => {
  if (!state.TOTAL) return;
  state.currentEp = clamp(n, 1, state.TOTAL);
  setOverlay(elements.ovTitle, state.currentEp, state.SERIES_TITLE);
  document
    .querySelectorAll('.ep-btn')
    .forEach((b) =>
      b.setAttribute(
        'aria-current',
        String(Number(b.dataset.ep) === state.currentEp),
      ),
    );
  const episodeData = state.EPISODES[state.currentEp - 1];
  const src = episodeData.original_src || episodeData.src;
  if (!src) {
    showError(
      `Video Ep ${state.currentEp} tidak ditemukan.`,
      elements.titleEl,
      elements.xgContainer,
    );
    return;
  }
  mountXG(src, elements, state, callbacks);
  setPlayingUI(false, elements.overlayHint);
  callbacks.onEpisodeChange();

  // Save progress to localStorage
  if (postId) {
    localStorage.setItem(`shortplyr_progress_${postId}`, state.currentEp);
  }
};

export const handleEpisodeSelection = (
  n,
  state,
  elements,
  callbacks,
  postId,
) => {
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
  setEpisode(targetEp, state, elements, callbacks, postId);
};

export const nextEpisode = (state, elements, callbacks, postId) =>
  handleEpisodeSelection(
    state.currentEp + 1,
    state,
    elements,
    callbacks,
    postId,
  );
export const prevEpisode = (state, elements, callbacks, postId) =>
  handleEpisodeSelection(
    state.currentEp - 1,
    state,
    elements,
    callbacks,
    postId,
  );

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

export function initPlayerGestures(
  playerFull,
  playbackIndicator,
  playbackSpeedText,
  sheet,
  openBtn,
) {
  if (window.innerWidth > 768) return; // Only for mobile

  const gesture = {
    startX: 0,
    startY: 0,
    isSwiping: false,
  };

  let lastTap = { time: 0, side: null };
  let tapTimer = null;
  const DOUBLE_TAP_THRESHOLD = 300; // ms
  const SEEK_TIME = 5; // seconds

  // --- Create Seek Indicators ---
  let seekForwardIndicator, seekBackwardIndicator;
  let indicatorHideTimer = null;

  function createSeekIndicator(side) {
    const indicator = document.createElement('div');
    const container = document.createElement('div');
    const icon = document.createElement('i');
    const text = document.createElement('span');

    const iconClass =
      side === 'forward'
        ? 'ri-arrow-right-double-fill'
        : 'ri-arrow-left-double-fill';

    indicator.className = `absolute top-1/2 -translate-y-1/2 z-[3] flex items-center justify-center text-white pointer-events-none opacity-0 transition-opacity duration-200`;
    if (side === 'forward') {
      indicator.style.right = '25%';
    } else {
      indicator.style.left = '25%';
    }

    container.className = 'flex items-center gap-1';

    icon.className = `${iconClass} text-3xl`;
    text.className = 'font-bold text-sm';

    if (side === 'forward') {
      // For forward: text first, then icon ("+5s" then arrow)
      text.textContent = `+${SEEK_TIME}s`;
      container.appendChild(text);
      container.appendChild(icon);
    } else {
      // For backward: icon first, then text (arrow then "-5s")
      text.textContent = `-${SEEK_TIME}s`;
      container.appendChild(icon);
      container.appendChild(text);
    }

    indicator.appendChild(container);
    playerFull.appendChild(indicator);
    return indicator;
  }

  seekForwardIndicator = createSeekIndicator('forward');
  seekBackwardIndicator = createSeekIndicator('backward');

  function showSeekIndicator(side) {
    clearTimeout(indicatorHideTimer);
    const indicator =
      side === 'forward' ? seekForwardIndicator : seekBackwardIndicator;
    const otherIndicator =
      side === 'forward' ? seekBackwardIndicator : seekForwardIndicator;

    otherIndicator.classList.add('opacity-0');
    indicator.classList.remove('opacity-0');

    indicatorHideTimer = setTimeout(() => {
      indicator.classList.add('opacity-0');
    }, 500);
  }

  // --- Gesture Handlers ---
  const startGesture = (e) => {
    if (
      e.target.closest('.xgplayer-controls, .xgplayer-enter, #sideControls')
    ) {
      return;
    }
    gesture.startX = e.clientX;
    gesture.startY = e.clientY;
    gesture.isSwiping = false;
  };

  const moveGesture = (e) => {
    if (gesture.startX === 0) return;
    const deltaX = e.clientX - gesture.startX;
    const deltaY = e.clientY - gesture.startY;
    if (Math.abs(deltaX) > 10 || Math.abs(deltaY) > 10) {
      gesture.isSwiping = true;
    }
  };

  const endGesture = (e) => {
    if (gesture.startX === 0) return;

    if (!gesture.isSwiping) {
      // It's a tap.
      e.preventDefault(); // Prevent any other unwanted click events.

      const now = performance.now();
      const playerWidth = playerFull.clientWidth;
      const tapX = gesture.startX;

      // Tentukan area trigger di sisi kiri dan kanan (misal: 33% di setiap sisi)
      const sideThreshold = playerWidth / 3;

      const side =
        tapX < sideThreshold
          ? 'left'
          : tapX > playerWidth - sideThreshold
            ? 'right'
            : 'center';

      if (now - lastTap.time < DOUBLE_TAP_THRESHOLD && side === lastTap.side) {
        // --- DOUBLE TAP ---
        // A double tap was detected, so cancel the single tap timer.
        if (tapTimer) {
          clearTimeout(tapTimer);
          tapTimer = null;
        }

        // Lakukan aksi maju/mundur hanya jika tap di sisi kiri atau kanan
        if (side === 'right') {
          xg.currentTime = Math.min(xg.duration, xg.currentTime + SEEK_TIME);
          showSeekIndicator('forward');
        } else if (side === 'left') {
          xg.currentTime = Math.max(0, xg.currentTime - SEEK_TIME);
          showSeekIndicator('backward');
        } else if (side === 'center') {
          if (xg.paused) {
            xg.play();
          } else {
            xg.pause();
          }
        }
        lastTap = { time: 0, side: null }; // Reset double-tap detection.
      } else {
        // --- FIRST TAP ---
        // This is the first tap, so record it.
        lastTap = { time: now, side: side };

        // And set a timer for the single tap action.
        tapTimer = setTimeout(() => {
          // If the timer fires, it means no second tap occurred.
          // Execute the single tap action: play/pause.
          if (xg.paused) {
            xg.play();
          } else {
            xg.pause();
          }
          tapTimer = null;
        }, DOUBLE_TAP_THRESHOLD);
      }
    }

    // Reset gesture state
    gesture.startX = 0;
    gesture.startY = 0;
    gesture.isSwiping = false;
  };

  playerFull.addEventListener('pointerdown', startGesture);
  playerFull.addEventListener('pointermove', moveGesture);
  playerFull.addEventListener('pointerup', endGesture);
  playerFull.addEventListener('pointercancel', endGesture);
}
