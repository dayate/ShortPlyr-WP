import { processAndDisplayPoster } from './utils/imageProcessor.js';

let isInitialLoad = true; // Flag for initial load

const hidePlayerLoader = (playerLoader) => {
  // Only run this logic once on the initial load
  if (playerLoader && isInitialLoad) {
    playerLoader.classList.add('opacity-0');
    setTimeout(() => {
      playerLoader.style.display = 'none';
    }, 500); // Match transition duration
    isInitialLoad = false; // Flip the flag so it doesn't run again
  }
};

export const showError = (msg, titleEl, playerContainer) => {
  hidePlayerLoader(document.querySelector('#player-loader')); // Also hide loader on error
  if (titleEl) titleEl.textContent = msg;
  if (playerContainer) {
    playerContainer.innerHTML = `<div class="w-full h-full flex items-center justify-center text-red-400 p-4">${msg}</div>`;
  }
};

/**
 * Mengambil data dari REST API dan menginisialisasi aplikasi.
 */
export const fetchDataAndInitialize = async (populateUICallback) => {
  if (
    typeof window.shortplyrData === 'undefined' ||
    !window.shortplyrData.api_url
  ) {
    showError(
      'Konfigurasi data tidak ditemukan.',
      document.querySelector('.title'),
      document.querySelector('#xgplayer'),
    );
    return;
  }
  
  const { post_id, api_url, nonce } = window.shortplyrData;
  const cacheKey = `shortplyr_data_${post_id}`;

  // Cek localStorage untuk data cache
  const cachedItem = localStorage.getItem(cacheKey);

  if (cachedItem) {
    try {
      const { fetch_timestamp, data } = JSON.parse(cachedItem);
      const now = new Date().getTime();
      const fetchTimeSeconds = Math.floor(fetch_timestamp / 1000);
      const safetyMarginExpiryTime = (fetchTimeSeconds + (2.5 * 60 * 60)) * 1000; // 2.5 jam dari waktu pengambilan
      
      if (now < safetyMarginExpiryTime) {
        // Data cache masih valid dalam batas aman
        hidePlayerLoader(document.querySelector('#player-loader'));
        populateUICallback(data);
        return;
      } else {
        // Melewati batas aman (2.5 jam), hapus cache dan ambil data baru
        localStorage.removeItem(cacheKey);
      }
    } catch (e) {
      localStorage.removeItem(cacheKey);
    }
  }

  try {
    const response = await fetch(api_url, {
      headers: {
        'X-WP-Nonce': nonce,
      },
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const seriesData = await response.json();
    
    if (seriesData.code === 'no_data') {
      showError(
        seriesData.message || 'Data tidak dapat diambil.',
        document.querySelector('.title'),
        document.querySelector('#xgplayer'),
      );
      return;
    }

    // Simpan ke localStorage dengan waktu pengambilan data asli dari server
    const itemToCache = {
      fetch_timestamp: seriesData.fetch_timestamp || new Date().getTime(), // Gunakan waktu dari server jika tersedia
      data: seriesData,
    };

    try {
      localStorage.setItem(cacheKey, JSON.stringify(itemToCache));
    } catch (e) {
    }

    populateUICallback(seriesData);
  } catch (error) {
    showError(
      'Gagal mengambil data serial.',
      document.querySelector('.title'),
      document.querySelector('#xgplayer'),
    );
  }
};

export { processAndDisplayPoster, hidePlayerLoader };
