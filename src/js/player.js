
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

export const setEpisode = (n, state, elements, callbacks, postId) => {
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

  // Save progress to localStorage
  if (postId) {
      localStorage.setItem(`shortplyr_progress_${postId}`, state.currentEp);
  }
};

export const handleEpisodeSelection = (n, state, elements, callbacks, postId) => {
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

export const nextEpisode = (state, elements, callbacks, postId) => handleEpisodeSelection(state.currentEp + 1, state, elements, callbacks, postId);
export const prevEpisode = (state, elements, callbacks, postId) => handleEpisodeSelection(state.currentEp - 1, state, elements, callbacks, postId);

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

export function initPlayerGestures(playerFull, playbackIndicator, playbackSpeedText, sheet, openBtn) {
    if (window.innerWidth > 768) return; // Only for mobile

    let pressTimer = null;
    const gesture = {
        startY: 0,
        isSwiping: false,
        startTime: 0,
    };

    const startGesture = (e) => {
        // Ignore clicks on controls
        if (e.target.closest('.xgplayer-controls, .xgplayer-enter, #sideControls')) {
            return;
        }

        gesture.startY = e.clientY;
        gesture.startTime = e.timeStamp;
        gesture.isSwiping = false;

        // Set a timer for long-press
        pressTimer = setTimeout(() => {
            if (xg && !xg.paused && !gesture.isSwiping) {
                const newSpeed = 2;
                xg.playbackRate = newSpeed;
                showIndicator(newSpeed, playbackIndicator, playbackSpeedText);
            }
            pressTimer = null; // Timer has fired
        }, 500); // 500ms for long press
    };

    const moveGesture = (e) => {
        if (gesture.startY === 0) return; // Gesture didn't start on the player

        const deltaY = e.clientY - gesture.startY;
        // If user moves finger more than 10px, consider it a swipe
        if (Math.abs(deltaY) > 10) {
            if (pressTimer) {
                clearTimeout(pressTimer);
                pressTimer = null;
            }
            gesture.isSwiping = true;
        }
    };

    const endGesture = (e) => {
        if (gesture.startY === 0) return; // Gesture didn't start on the player

        // If a press timer was set but didn't fire, clear it.
        if (pressTimer) {
            clearTimeout(pressTimer);
            pressTimer = null;
        }
        // If playback rate was changed, reset it.
        if (xg && xg.playbackRate !== 1) {
            xg.playbackRate = 1;
            hideIndicator(playbackIndicator);
        }

        if (gesture.isSwiping) {
            const deltaY = e.clientY - gesture.startY;
            const deltaTime = e.timeStamp - gesture.startTime;
            const velocity = Math.abs(deltaY / deltaTime);

            // Swipe Up
            if (deltaY < -50 && velocity > 0.4) { // Threshold of 50px up
                openSheetMid(sheet, openBtn);
            }
            // Swipe Down
            if (deltaY > 50 && velocity > 0.4) { // Threshold of 50px down
                closeSheet(sheet, openBtn);
            }
        }
        
        // Reset gesture state
        gesture.isSwiping = false;
        gesture.startY = 0;
        gesture.startTime = 0;
    };

    playerFull.addEventListener('pointerdown', startGesture);
    playerFull.addEventListener('pointermove', moveGesture);
    playerFull.addEventListener('pointerup', endGesture);
    playerFull.addEventListener('pointercancel', endGesture);
}
