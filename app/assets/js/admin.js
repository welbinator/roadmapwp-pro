document.addEventListener('DOMContentLoaded', function() {
    const shortcodesToggle = document.getElementById('shortcodes-toggle');
    const shortcodesContent = document.getElementById('shortcodes-content');
    const blocksToggle = document.getElementById('blocks-toggle');
    const blocksContent = document.getElementById('blocks-content');

    shortcodesToggle.addEventListener('click', function() {
        shortcodesContent.classList.toggle('hidden');
        shortcodesToggle.textContent = shortcodesContent.classList.contains('hidden') ? 'expand' : 'collapse';
    });

    blocksToggle.addEventListener('click', function() {
        blocksContent.classList.toggle('hidden');
        blocksToggle.textContent = blocksContent.classList.contains('hidden') ? 'expand' : 'collapse';
    });
});