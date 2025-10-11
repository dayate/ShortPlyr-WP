import heic2any from 'heic2any';

/**
 * Fungsi umum untuk memproses dan menampilkan poster, termasuk konversi HEIC/HEIF
 * @param {string} url - URL gambar
 * @param {HTMLElement} container - Elemen img yang akan ditampilkan
 * @param {string} altText - Teks alternatif untuk gambar
 * @param {Object} options - Opsi tambahan untuk fungsi
 * @param {boolean} options.addClass - Tambahkan kelas converted-from-heic (default: false)
 */
export async function processAndDisplayPoster(url, container, altText, options = {}) {
  const { addClass = false } = options;
  
  if (!url) {
    if (container) {
      container.src = '';
      container.alt = 'Poster tidak tersedia';
    }
    return;
  }

  // Simpan referensi ke URL objek sebelumnya agar bisa dibersihkan
  let previousObjectURL = null;
  
  try {
    // Ambil hanya bagian path dari URL untuk mengabaikan query string (?rk3s=...)
    const pathname = new URL(url).pathname;

    // Cek apakah path tersebut berakhiran .heic atau .heif
    if (pathname.endsWith('.heic') || pathname.endsWith('.heif')) {
      const response = await fetch(url);
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);
      const blob = await response.blob();

      // Tambahkan timeout untuk menghindari proses yang terlalu lama
      const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Konversi HEIC terlalu lama')), 30000); // 30 detik timeout
      });
      
      const conversionPromise = heic2any({
        blob: blob,
        toType: 'image/jpeg',
        quality: 0.8 // Kualitas gambar setelah konversi
      });
      
      // Gunakan Promise.race untuk menangani timeout
      const conversionResult = await Promise.race([conversionPromise, timeoutPromise]);

      if (container) {
        // Bersihkan URL objek sebelumnya jika ada
        if (container.src && container.src.startsWith('blob:')) {
          previousObjectURL = container.src;
        }
        
        container.src = URL.createObjectURL(conversionResult);
        container.alt = altText;
        
        // Hapus kelas processing dan tambahkan kelas converted
        if (addClass) {
          // Tambahkan kelas untuk menandai bahwa gambar telah diproses
          container.classList.add('converted-from-heic');
        }
      }
    } else {
      if (container) {
        // Jika sebelumnya ada URL objek, bersihkan terlebih dahulu
        if (container.src && container.src.startsWith('blob:')) {
          previousObjectURL = container.src;
        }
        
        container.src = url;
        container.alt = altText;
      }
    }
  } catch (error) {
    console.error('Client-side image processing error:', error);
    if (container) {
      // Jika terjadi error, tetap bersihkan URL objek sebelumnya jika ada
      if (container.src && container.src.startsWith('blob:')) {
        previousObjectURL = container.src;
      }
      
      // Tampilkan gambar asli sebagai fallback
      container.src = url;
      container.alt = altText;
      container.classList.add('heic-conversion-failed');
    }
  } finally {
    // Selalu bersihkan URL objek sebelumnya untuk mencegah kebocoran memori
    if (previousObjectURL) {
      URL.revokeObjectURL(previousObjectURL);
    }
  }
}

/**
 * Fungsi untuk menginisialisasi konversi pada halaman arsip
 * @param {string} selector - Selector untuk elemen gambar yang akan diproses
 */
export function initHeicConversionForArchive(selector = 'img[data-heic-conversion="true"]') {
  // Cari semua gambar poster di halaman arsip yang mungkin dalam format HEIC
  const posterImages = document.querySelectorAll(selector);
  
  // Gunakan Intersection Observer untuk lazy loading dan memproses ketika elemen masuk ke viewport
  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        const originalSrc = img.getAttribute('data-original-src') || img.src;
        const altText = img.alt;
        
        // Hapus observer untuk elemen ini setelah mulai diproses
        observer.unobserve(img);
        
        // Panggil fungsi konversi dengan opsi untuk menambahkan kelas
        processAndDisplayPoster(originalSrc, img, altText, { addClass: true });
      }
    });
  }, {
    // Opsi untuk observer: rootMargin untuk mulai proses sebelum gambar benar-benar terlihat
    rootMargin: '100px' // Mulai proses ketika gambar dalam jarak 100px dari viewport
  });
  
  // Amati setiap gambar
  posterImages.forEach(img => {
    // Untuk gambar yang bukan HEIC, kita tetap ingin menangani spinner dengan benar
    // Jadi kita cek dulu format gambarnya sebelum menambahkan ke observer
    observer.observe(img);
  });
}