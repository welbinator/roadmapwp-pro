document.addEventListener('DOMContentLoaded', function() {
    const filtersToggle = document.getElementById('filters-toggle');
    const filtersContent = document.getElementById('filters-content');

    filtersToggle.addEventListener('click', function() {
        filtersContent.classList.toggle('hidden');
        filtersToggle.textContent = filtersContent.classList.contains('hidden') ? 'expand' : 'collapse';
    });
});