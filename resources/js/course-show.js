import StarRating from 'star-rating.js';
import 'star-rating.js/css';

function initializeRating() {
    const select = document.querySelector('.course-rating-input');
    if (select && !select.widget) {
        new StarRating(select, { maxStars: 5, clearable: false, tooltip: false });
    }
}

document.addEventListener('DOMContentLoaded', initializeRating);
document.addEventListener('livewire:navigated', initializeRating);
document.addEventListener('livewire:init', () => {
    Livewire.on('course-access-ready', () => {
        requestAnimationFrame(() => document.querySelector('.cp-player-panel')?.scrollIntoView({ behavior: 'smooth' }));
    });
});
