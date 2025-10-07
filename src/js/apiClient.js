import heic2any from 'heic2any';

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
 * Fungsi untuk memproses dan menampilkan poster, termasuk konversi HEIC/HEIF
 */
async function processAndDisplayPoster(url, container, altText) {
  if (!url) {
    container.src = '';
    container.alt = 'Poster tidak tersedia';
    return;
  }

  try {
    // Ambil hanya bagian path dari URL untuk mengabaikan query string (?rk3s=...)
    const pathname = new URL(url).pathname;

    // Cek apakah path tersebut berakhiran .heic atau .heif
    if (pathname.endsWith('.heic') || pathname.endsWith('.heif')) {
      const response = await fetch(url);
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);
      const blob = await response.blob();

      const conversionResult = await heic2any({
        blob: blob,
        toType: 'image/jpeg',
      });

      container.src = URL.createObjectURL(conversionResult);
      container.alt = altText;
    } else {
      container.src = url;
      container.alt = altText;
    }
  } catch (error) {
    console.error('Client-side image processing error:', error);
    container.src = url;
    container.alt = 'Error memproses gambar. Menampilkan gambar asli.';
  }
}

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

  const cachedItem = localStorage.getItem(cacheKey);

  if (cachedItem) {
    try {
      const { timestamp, data } = JSON.parse(cachedItem);
      const now = new Date().getTime();
      const oneHour = 60 * 60 * 1000; // 1 jam dalam milidetik

      // Cek apakah cache masih valid (kurang dari 1 jam)
      if (now - timestamp < oneHour) {
        hidePlayerLoader(document.querySelector('#player-loader'));
        populateUICallback(data);
        return;
      } else {
        // Cache kedaluwarsa, hapus dan lanjutkan untuk mengambil data baru
        localStorage.removeItem(cacheKey);
      }
    } catch (e) {
      // Jika data cache rusak, hapus saja
      console.error('Gagal mem-parsing data cache, cache akan dihapus.', e);
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

    // Buat item cache baru dengan timestamp
    const itemToCache = {
      timestamp: new Date().getTime(),
      data: seriesData,
    };

    try {
      localStorage.setItem(cacheKey, JSON.stringify(itemToCache));
    } catch (e) {
      console.error('Gagal menyimpan ke localStorage:', e);
    }

    populateUICallback(seriesData);
  } catch (error) {
    console.error('Gagal mengambil data serial:', error);
    showError(
      'Gagal mengambil data serial.',
      document.querySelector('.title'),
      document.querySelector('#xgplayer'),
    );
  }
};

export { processAndDisplayPoster, hidePlayerLoader };
