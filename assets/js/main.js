// Inisialisasi Swiper
document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi Swiper jika elemen ada di halaman
    if (document.querySelector('.hero-swiper')) {
        const heroSwiper = new Swiper('.hero-swiper', {
            slidesPerView: 1.5,
            spaceBetween: 16,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                dynamicBullets: true,
            },
            breakpoints: {
                640: { slidesPerView: 2.5, spaceBetween: 20 },
                720: { slidesPerView: 3, spaceBetween: 24 },
            },
        });
    }

    // --- LOGIKA PAGINATION ---
    const gridContainer = document.getElementById('card-grid');
    const controlsContainer = document.getElementById('pagination-controls');

    if (gridContainer && controlsContainer) {
        const items = Array.from(gridContainer.querySelectorAll('a.block'));

        if (items.length > 0) {
            const itemsPerPage = 8; // Menampilkan 8 item per halaman
            let currentPage = 1;
            const totalPages = Math.ceil(items.length / itemsPerPage);

            function showPage(page) {
                currentPage = page;
                const startIndex = (page - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;

                items.forEach((item, index) => {
                    if (index >= startIndex && index < endIndex) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
                renderControls();
            }

            function renderControls() {
                controlsContainer.innerHTML = '';

                // Tombol Sebelumnya dengan Ikon
                const prevButton = document.createElement('button');
                prevButton.innerHTML = '<i class="ri-arrow-left-s-line"></i>';
                prevButton.disabled = currentPage === 1;
                prevButton.className =
                    'p-2 text-xl leading-none rounded-md border border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed';
                prevButton.onclick = () => showPage(currentPage - 1);
                controlsContainer.appendChild(prevButton);

                // Tombol angka
                for (let i = 1; i <= totalPages; i++) {
                    const pageButton = document.createElement('button');
                    pageButton.innerText = i;
                    pageButton.className = `px-4 py-2 text-sm font-medium rounded-md border ${
                        currentPage === i
                            ? 'bg-primary text-dark border-primary'
                            : 'border-gray-600'
                    }`;
                    pageButton.onclick = () => showPage(i);
                    controlsContainer.appendChild(pageButton);
                }

                // Tombol Berikutnya dengan Ikon
                const nextButton = document.createElement('button');
                nextButton.innerHTML = '<i class="ri-arrow-right-s-line"></i>';
                nextButton.disabled = currentPage === totalPages;
                nextButton.className =
                    'p-2 text-xl leading-none rounded-md border border-gray-600 disabled:opacity-50 disabled:cursor-not-allowed';
                nextButton.onclick = () => showPage(currentPage + 1);
                controlsContainer.appendChild(nextButton);
            }

            if (totalPages > 1) {
                showPage(1);
            }
        }
    }
});