jQuery(document).ready(function($) {
    // Function to toggle page selection visibility
    function togglePageSelection() {
        const templateValue = $('#wp_roadmap_single_idea_template').val();
        if (templateValue === 'page') {
            $('#single_idea_page_setting').show();
        } else {
            $('#single_idea_page_setting').hide();
        }
    }

    // Run on page load
    togglePageSelection();

    // Run on change
    $('#wp_roadmap_single_idea_template').on('change', togglePageSelection);
});