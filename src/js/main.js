// 1. Impor pustaka xgplayer dari package
import Player from 'xgplayer';

(function () {
  /* =============================== DOM ===================================== */
  const $ = (s, r = document) => r.querySelector(s);

  const playerLoader = $('#player-loader');
  const wrap = $('.wrap');
  const playerFull = $('.player-full');
  const sheet = $('#sheet');
  const openBtn = $('#openSheetBtn');
  const fullBtn = $('#fullBtn');
  const prevEpBtn = $('#prevEpBtn');
  const nextEpBtn = $('#nextEpBtn');
  const grid = $('#episodeGrid');
  const overlayHint = $('#overlayHint');
  const ovTitle = $('#ovTitle');
  const sideControls = $('#sideControls');
  const sheetScroll = $('.sheet-scroll');
  const synopsisToggle = $('#synopsisToggle');
  const synopsisContent = $('#synopsisContent');
  const playbackIndicator = $('#playbackIndicator');
  const playbackSpeedText = $('#playbackSpeedText');

  const posterImg = $('.poster');
  const titleEl = $('.title');
  const totalEpsEl = $('#totalEps');
  const synopsisTextEl = $('.synopsis-text');

  const XG_CONTAINER_ID = 'xgplayer';

  /* =============================== STATE =================================== */
  let xg = null;
  let currentEp = 1;
  let isOpen = false;
  let currentY = 100;
  let EPISODES = [];
  let sideControlsTimer = null;
  let SERIES_TITLE = '';
  let TOTAL = 0;

  const SHEET_TOP_Y = 5;
  const SHEET_MID_Y = 25;
  const SHEET_CLOSED_Y = 100;

  /* ============================== HELPERS ================================ */
  const clamp = (n, min, max) => Math.max(min, Math.min(n, max));

  const hidePlayerLoader = () => {
    if (playerLoader) {
      playerLoader.classList.add('opacity-0');
      setTimeout(() => {
        playerLoader.style.display = 'none';
      }, 500); // Match transition duration
    }
  };

  const showPlayerLoader = () => {
    if (playerLoader) {
      playerLoader.style.display = 'flex';
      setTimeout(() => playerLoader.classList.remove('opacity-0'), 10);
    }
  };

  const showError = (msg) => {
    hidePlayerLoader();
    if (titleEl) titleEl.textContent = msg;
    const playerContainer = $(`#${XG_CONTAINER_ID}`);
    if (playerContainer) {
      playerContainer.innerHTML = `<div class="w-full h-full flex items-center justify-center text-red-400 p-4">${msg}</div>`;
    }
  };

  /* ============================== SHEET API ================================ */
  const setY = (pct) => {
    currentY = clamp(pct, SHEET_TOP_Y, SHEET_CLOSED_Y);
    document.documentElement.style.setProperty('--sheet-y', `${currentY}%`);
    const isHidden = currentY === SHEET_CLOSED_Y;
    sheet.setAttribute('aria-hidden', String(isHidden));
    openBtn.setAttribute('aria-expanded', String(!isHidden));
  };
  const openSheetMid = () => { isOpen = true; setY(SHEET_MID_Y); };
  const closeSheet = () => { isOpen = false; setY(SHEET_CLOSED_Y); };

  /* ============================= UI STATES ================================= */
  const setPlayingUI = (p) => overlayHint?.classList.toggle('opacity-0', !!p);
  const setOverlay = () => { ovTitle.textContent = `Ep ${currentEp} | ${SERIES_TITLE || 'Judul'}`; };

  const hideSideControls = () => sideControls?.classList.add('opacity-0', 'pointer-events-none');
  const showSideControls = () => sideControls?.classList.remove('opacity-0', 'pointer-events-none');

  const resetSideControlsTimer = () => {
    showSideControls();
    clearTimeout(sideControlsTimer);
    if (xg && !xg.paused) {
      sideControlsTimer = setTimeout(hideSideControls, 3000);
    }
  };

  /* ============================== xgplayer ================================= */
  const mountXG = (url) => {
    if (xg?.destroy) { try { xg.destroy(false); } catch {} }

    showPlayerLoader();

    xg = new Player({ id: XG_CONTAINER_ID, url, autoplay: true, width: '100%', height: '100%', lang: 'id', playsinline: true, fitVideoSize: 'contain', controls: { name: 'play' }, ignores: ['playbackRate', 'definition', 'fullscreen', 'pip', 'airplay', 'download', 'cssFullscreen', 'screenShot', 'miniProgress', 'timePreview', 'playNext'] });
    
    xg.on('loadedmetadata', hidePlayerLoader);
    xg.on('play', () => { setPlayingUI(true); resetSideControlsTimer(); });
    xg.on('pause', () => { setPlayingUI(false); clearTimeout(sideControlsTimer); showSideControls(); });
    xg.on('ended', nextEpisode);
    xg.on('error', () => { 
      if (ovTitle) ovTitle.textContent = `Gagal memuat Ep ${currentEp}`;
      hidePlayerLoader();
    });

    playerFull.addEventListener('mousemove', resetSideControlsTimer);
    playerFull.addEventListener('touchstart', resetSideControlsTimer);
    resetSideControlsTimer();
  };

  /* ============================= Fullscreen ================================= */
  const fs = { is: () => !!(document.fullscreenElement || document.webkitFullscreenElement), enter: (el) => (el.requestFullscreen || el.webkitRequestFullscreen).call(el), exit: () => (document.exitFullscreen || document.webkitExitFullscreen).call(document) };
  const onFsChange = () => { const on = fs.is(); fullBtn?.setAttribute('data-fs', on ? 'on' : 'off'); };

  const setEpisode = (n) => {
    if (!TOTAL) return;
    currentEp = clamp(n, 1, TOTAL);
    setOverlay();
    document.querySelectorAll('.ep-btn').forEach((b) => b.setAttribute('aria-current', String(Number(b.dataset.ep) === currentEp)));
    const episodeData = EPISODES[currentEp - 1];
    const src = episodeData.original_src || episodeData.src;
    if (!src) { showError(`Video Ep ${currentEp} tidak ditemukan.`); return; }
    mountXG(src);
    setPlayingUI(false);
    closeSheet();
  };

  const handleEpisodeSelection = (n) => {
    const targetEp = clamp(n, 1, TOTAL);
    if (!targetEp) return;
    const episodeData = EPISODES[targetEp - 1];
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
    setEpisode(targetEp);
  };

  const nextEpisode = () => handleEpisodeSelection(currentEp + 1);
  const prevEpisode = () => handleEpisodeSelection(currentEp - 1);

  const buildGrid = () => {
    if (!TOTAL || !grid) return;
    grid.innerHTML = '';
    const f = document.createDocumentFragment();
    for (let i = 1; i <= TOTAL; i++) {
      const li = document.createElement('li');
      const btn = document.createElement('button');
      const episodeData = EPISODES[i - 1];
      btn.className = 'ep-btn w-12 h-12 rounded border border-gray-700 bg-gray-800 text-white flex items-center justify-center font-bold text-sm transition-colors hover:bg-gray-700 aria-[current=true]:bg-white aria-[current=true]:text-zinc-900';
      btn.type = 'button';
      btn.textContent = i;
      btn.dataset.ep = i;
      if (episodeData && !episodeData.is_ad && i === currentEp) { btn.setAttribute('aria-current', 'true'); }
      btn.onclick = () => handleEpisodeSelection(i);
      li.appendChild(btn);
      f.appendChild(li);
    }
    grid.appendChild(f);
  };
  
  // --- DRAG LOGIC (Unchanged) ---
  const drag = { active: false, startY: 0, lastY: 0, startSheetY: SHEET_CLOSED_Y, lastT: 0, velocity: 0, lockedByScroll: false, pointerId: null };
  function dragStart(y, pointerId) { drag.active = true; drag.startY = y; drag.lastY = y; drag.startSheetY = currentY; drag.lastT = performance.now(); drag.velocity = 0; drag.pointerId = pointerId ?? null; sheet.style.transition = 'none'; }
  function dragMove(y) { if (!drag.active) return; const now = performance.now(); const dy = y - drag.lastY; const dt = Math.max(1, now - drag.lastT); drag.velocity = dy / dt; const h = window.innerHeight; const delta = ((y - drag.startY) / h) * 100; const target = clamp(drag.startSheetY + delta, SHEET_TOP_Y, SHEET_CLOSED_Y); setY(target); drag.lastY = y; drag.lastT = now; }
  function dragEnd() { if (!drag.active) return; sheet.style.transition = ''; const deltaPx = drag.lastY - drag.startY; const v = drag.velocity; let target = SHEET_MID_Y; if (v <= -VELOCITY_TRIGGER || deltaPx <= -SWIPE_THRESHOLD_PX) { target = SHEET_TOP_Y; } else if (v >= VELOCITY_TRIGGER || deltaPx >= SWIPE_THRESHOLD_PX) { target = SHEET_CLOSED_Y; } else { target = Math.abs(currentY - SHEET_TOP_Y) < Math.abs(currentY - SHEET_MID_Y) ? SHEET_TOP_Y : SHEET_MID_Y; } setY(target); isOpen = target !== SHEET_CLOSED_Y; drag.active = false; drag.lockedByScroll = false; drag.pointerId = null; }
  sheet.addEventListener('pointerdown', (e) => { if (e.target.closest('button')) return; const y = e.clientY; if (isOpen && currentY === SHEET_TOP_Y && sheetScroll && sheetScroll.scrollTop > 0) { drag.lockedByScroll = true; drag.startY = y; drag.pointerId = e.pointerId; return; } e.preventDefault(); sheet.setPointerCapture(e.pointerId); dragStart(y, e.pointerId); });
  sheet.addEventListener('pointermove', (e) => { if (drag.lockedByScroll) { const dy = e.clientY - drag.startY; if (dy > SWIPE_THRESHOLD_PX && sheetScroll && sheetScroll.scrollTop <= 0) { drag.lockedByScroll = false; try { sheet.setPointerCapture(drag.pointerId ?? e.pointerId); } catch {} e.preventDefault(); dragStart(e.clientY, e.pointerId); } return; } dragMove(e.clientY); });
  sheet.addEventListener('pointerup', dragEnd);
  sheet.addEventListener('pointercancel', dragEnd);
  // --- END DRAG LOGIC ---

  const showIndicator = (speed) => { if (playbackIndicator && playbackSpeedText) { playbackSpeedText.textContent = `${speed}x`; playbackIndicator.classList.remove('opacity-0'); playbackIndicator.setAttribute('aria-hidden', 'false'); } };
  const hideIndicator = () => { if (playbackIndicator) { playbackIndicator.classList.add('opacity-0'); playbackIndicator.setAttribute('aria-hidden', 'true'); } };
  const initLongPress = () => { if (window.innerWidth > 768) return; let pressTimer = null; const startPress = (e) => { if (e.target.closest('.xgplayer-controls, .xgplayer-enter, #sideControls')) { return; } pressTimer = setTimeout(() => { if (xg && !xg.paused) { const newSpeed = 2; xg.playbackRate = newSpeed; showIndicator(newSpeed); } }, 700); }; const endPress = () => { clearTimeout(pressTimer); if (xg && xg.playbackRate !== 1) { xg.playbackRate = 1; hideIndicator(); } }; playerFull.addEventListener('touchstart', startPress, { passive: true }); playerFull.addEventListener('touchend', endPress); playerFull.addEventListener('touchcancel', endPress); };

  /**
   * Mengisi kerangka UI dengan data dari API.
   */
  const populateUI = (seriesData) => {
    SERIES_TITLE = seriesData.title || 'Tanpa Judul';
    TOTAL = Number(seriesData.total) || 0;
    EPISODES = Array.isArray(seriesData.episodes) ? seriesData.episodes : [];

    if (posterImg) { posterImg.src = seriesData.poster; posterImg.alt = `Poster ${SERIES_TITLE}`; }
    if (titleEl) titleEl.textContent = SERIES_TITLE;
    if (totalEpsEl) totalEpsEl.textContent = TOTAL;
    if (synopsisTextEl) synopsisTextEl.innerHTML = seriesData.synopsis;

    setOverlay();
    buildGrid();
    if (TOTAL > 0) { 
      handleEpisodeSelection(1); 
    } else { 
      showError('Tidak ada episode yang tersedia.'); 
    }
  };

  /**
   * Mengambil data dari REST API dan menginisialisasi aplikasi.
   */
  const fetchDataAndInitialize = async () => {
    if (typeof window.shortplyrData === 'undefined' || !window.shortplyrData.api_url) {
      showError('Konfigurasi data tidak ditemukan.');
      return;
    }
    const { api_url, nonce } = window.shortplyrData;

    try {
      const response = await fetch(api_url, { headers: { 'X-WP-Nonce': nonce } });
      if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); }
      
      const seriesData = await response.json();
      if (seriesData.code === 'no_data') { showError(seriesData.message || 'Data tidak dapat diambil.'); return; }

      populateUI(seriesData);

    } catch (error) {
      console.error('Gagal mengambil data serial:', error);
      showError('Gagal mengambil data serial.');
    }
  };

  /* ============================== Init ===================================== */
  document.addEventListener('DOMContentLoaded', () => {
    setY(SHEET_CLOSED_Y);
    openBtn.onclick = () => { isOpen ? closeSheet() : openSheetMid(); };
    prevEpBtn.onclick = prevEpisode;
    nextEpBtn.onclick = nextEpisode;
    fullBtn.onclick = () => { fs.is() ? fs.exit() : fs.enter(wrap); };
    synopsisToggle?.addEventListener('click', () => {
      const isExpanded = synopsisToggle.getAttribute('aria-expanded') === 'true';
      synopsisToggle.setAttribute('aria-expanded', String(!isExpanded));
      synopsisContent.setAttribute('aria-expanded', String(!isExpanded));
    });
    document.addEventListener('fullscreenchange', onFsChange);
    document.addEventListener('webkitfullscreenchange', onFsChange);
    onFsChange();
    initLongPress();

    fetchDataAndInitialize();
  });
})();