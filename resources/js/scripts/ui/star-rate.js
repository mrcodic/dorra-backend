$(function () {
    'use strict';
    console.log("rytyt");

    function renderStars(container, rating) {
        const fullStars = Math.floor(rating);
        const halfStar = rating % 1 >= 0.25 && rating % 1 < 0.75;
        const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);

        let starsHtml = '';

        for (let i = 0; i < fullStars; i++) {
            starsHtml += '<i data-feather="star" class="text-warning" fill="#F8AB1B"></i>';
        }

        if (halfStar) {
            starsHtml += '<i data-feather="star" class="text-warning opacity-50"></i>';
        }

        for (let i = 0; i < emptyStars; i++) {
            starsHtml += '<i data-feather="star" class="text-muted" fill="#9EA4A3"></i>';
        }

        container.innerHTML = starsHtml;
    }

    // ✅ Render stars immediately
    document.querySelectorAll('.rating-stars').forEach(el => {
        const rating = parseFloat(el.dataset.rating);
        renderStars(el, rating);
    });

    // ✅ Then replace feather icons
    feather.replace();
});
