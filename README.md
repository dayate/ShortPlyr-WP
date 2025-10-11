# ShortPlyr - Video Player Streaming for Short Drama China

Tema WordPress yang dirancang khusus untuk menampilkan dan memutar video drama pendek Cina dengan fokus pada pengalaman pengguna yang optimal dan kompatibilitas format gambar yang luas.

## Deskripsi

ShortPlyr adalah tema WordPress yang dirancang untuk platform streaming video drama pendek Cina. Tema ini menyediakan pengalaman menonton yang mulus dengan dukungan untuk berbagai format file, termasuk konversi otomatis format HEIC ke format yang didukung browser untuk thumbnail dan poster.

## Fitur Utama

- **Support untuk Format Gambar HEIC**: Konversi otomatis gambar HEIC ke format yang didukung browser (JPEG/WebP) di sisi klien
- **Player Video Modern**: Menggunakan XGPlayer untuk pengalaman pemutaran video yang canggih
- **Tampilan Responsif**: Desain yang indah dan responsif menggunakan Tailwind CSS
- **Dukungan API Eksternal**: Integrasi dengan API MeloloAPI untuk mengambil data drama secara otomatis
- **Mode Entri Data**: Dukungan untuk mode otomatis (API) dan manual untuk input data
- **Pengaturan Poster**: Pilihan antara sumber poster dari API, URL kustom, atau featured image
- **Sistem Iklan**: Fitur penambahan iklan secara acak ke dalam daftar episode
- **Lazy Loading**: Pengoptimalan kinerja dengan lazy loading konten dan gambar

## Teknologi yang Digunakan

- **PHP** - Bahasa pemrograman server-side
- **JavaScript** - Untuk fungsionalitas client-side termasuk konversi HEIC
- **Tailwind CSS** - Framework CSS untuk styling
- **XGPlayer** - Pemutar video modern
- **heic2any** - Library untuk konversi HEIC ke format web
- **Swiper** - Library untuk slider dan carousel
- **ESBuild** - Tool bundling untuk JavaScript
- **PostCSS** - Tool untuk memproses CSS dengan plugin
- **Prettier** - Tool untuk mengformat kode

## Struktur Direktori

```
ShortPlyr-WP/
├── assets/                 # File-file hasil kompilasi (CSS, JS)
├── inc/                    # Fungsi-fungsi pendukung tema
│   ├── api-handler.php     # Handler untuk API eksternal
│   ├── cpt-setup.php       # Setup custom post type
│   ├── enqueue-scripts.php # Enqueue script dan style
│   ├── metaboxes.php       # Setup custom metaboxes
│   ├── theme-settings.php  # Pengaturan tema
│   └── template-routing.php # Routing template
├── src/                    # File-file sumber untuk dikompilasi
│   ├── css/                # File-file CSS sumber
│   │   └── input.css       # File CSS utama
│   └── js/                 # File-file JavaScript sumber
│       ├── utils/
│       │   └── imageProcessor.js # Proses konversi HEIC
│       ├── apiClient.js    # Klien API
│       ├── heicConverter.js # Fungsi konversi HEIC
│       ├── main.js         # File utama JS
│       ├── player.js       # Fungsi pemutar video
│       ├── sheet.js        # Fungsi sheet UI
│       └── ui.js           # Fungsi UI
├── templates/              # Template halaman
│   └── archive-serial_video.php
├── .gitignore              # File-file yang diabaikan oleh Git
├── .prettierrc.json        # Konfigurasi Prettier
├── 404.php                 # Template halaman 404
├── functions.php           # Fungsi-fungsi utama tema
├── index.php               # Template utama
├── package.json            # Dependensi dan skrip proyek
├── package-lock.json       # Versi pasti dependensi
├── postcss.config.js       # Konfigurasi PostCSS
├── style.css               # CSS tema
├── tailwind.config.js      # Konfigurasi Tailwind CSS
└── README.md               # Dokumentasi ini
```

## Instalasi

1. Clone atau download tema ini ke direktori `wp-content/themes/` di instalasi WordPress Anda
2. Aktifkan tema dari dashboard WordPress admin
3. Instal dependensi Node.js:
   ```bash
   npm install
   ```
4. Kompilasi file assets:
   ```bash
   npm run build
   ```
5. Untuk mode pengembangan dengan watch file:
   ```bash
   npm run dev
   ```

## Penggunaan

### Membuat Serial Video Baru

1. Pergi ke menu "Serial Video" → "Tambah Baru" di dashboard admin
2. Pilih mode entri data: 
   - **Otomatis (API)**: Data diambil dari API eksternal
   - **Manual**: Data dimasukkan secara manual di editor WordPress
3. Atur sumber poster:
   - Gunakan Poster dari API
   - Gunakan URL Kustom
   - Gunakan Featured Image
4. Jika menggunakan mode API, isi detail API:
   - Judul Novel (Query)
   - Book ID
   - Book Name
5. Tambahkan episode (jika mode manual) atau biarkan kosong untuk mode API
6. Atur pengaturan iklan (opsional)
7. Publish post

### Konversi HEIC

Tema ini mendukung konversi format HEIC ke format yang didukung browser melalui pendekatan:

1. **Client-side dengan heic2any**:
   - Gambar HEIC dikonversi di sisi klien menggunakan library heic2any
   - Proses konversi terjadi saat gambar dimuat di browser
   - Ini adalah satu-satunya pendekatan yang digunakan untuk kompatibilitas format
   - Tidak ada lagi proses konversi server-side yang dijalankan

## Konfigurasi API

Untuk menggunakan fitur API, Anda perlu mengkonfigurasi endpoint API:

1. Gunakan `SHORTPLYR_MELOLO_API_KEY` konstanta di `wp-config.php` atau
2. Setel di pengaturan tema untuk menyimpan API key di database
3. Atur URL API di pengaturan tema

## Kontribusi

1. Fork repositori ini
2. Buat branch fitur baru (`git checkout -b feature/fitur-anda`)
3. Commit perubahan Anda (`git commit -m 'Tambah fitur hebat'`)
4. Push ke branch (`git push origin feature/fitur-anda`)
5. Buat pull request

## Dependensi Proyek

Dalam file `package.json` tercantum dependensi utama:

- `heic2any`: Library untuk konversi format HEIC
- `xgplayer`: Pemutar video yang digunakan
- `@tailwindcss/cli`, `tailwindcss`: Framework CSS
- `esbuild`: Tool bundling JavaScript
- `eslint`, `prettier`: Tool untuk kualitas kode

## Pengembangan

Untuk pengembangan lokal:

1. Instal dependensi:
   ```bash
   npm install
   ```
2. Jalankan mode pengembangan:
   ```bash
   npm run dev
   ```
3. Kompilasi untuk produksi:
   ```bash
   npm run build
   ```

## Lisensi

Tema ini dirilis di bawah Lisensi ISC.

## Troubleshooting

### Gambar HEIC tidak muncul

- Pastikan file HEIC dapat diakses secara publik
- Periksa konsol browser untuk error konversi
- Pastikan browser mendukung library heic2any

### API tidak merespon

- Periksa endpoint API dan kunci API
- Pastikan server dapat melakukan permintaan eksternal
- Periksa error log WordPress

### Player video tidak berfungsi

- Pastikan URL video valid dan dapat diakses
- Periksa apakah format video didukung browser
- Pastikan XGPlayer dimuat dengan benar

## Catatan Tambahan

Proyek ini dirancang untuk platform streaming drama pendek Cina, dengan fokus pada kemudahan penggunaan dan kompatibilitas format gambar yang luas. Konversi HEIC di sisi klien memberikan pengalaman pengguna yang konsisten tanpa ketergantungan pada konfigurasi server tertentu.