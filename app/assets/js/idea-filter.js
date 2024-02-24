jQuery(document).ready(function($) {
    // Listen for changes on checkboxes and radio buttons in the filter
    $('.wp-roadmap-ideas-filter-taxonomy input[type=checkbox], .wp-roadmap-ideas-filter-taxonomy input[type=radio]').change(function() {
        updateIdeasList();
    });

    // Function to update ideas list based on filters or search term
    function updateIdeasList(searchTerm = '') {
        var filterData = {};
        $('.wp-roadmap-ideas-filter-taxonomy').each(function() {
            var taxonomy = $(this).data('taxonomy');
            var matchType = $('input[name="match_type_' + taxonomy + '"]:checked').val();
            filterData[taxonomy] = {
                'terms': [],
                'matchType': matchType
            };
            $(this).find('input[type=checkbox]:checked').each(function() {
                filterData[taxonomy]['terms'].push($(this).val());
            });
        });

        var data = {
            'action': 'filter_ideas',
            'filter_data': filterData,
            'nonce': RoadMapWPFilterAjax.nonce // Include the nonce for security
        };

        // If a search term is provided, include it in the AJAX request
        if (searchTerm) {
            data['search_term'] = searchTerm;
        }

        $.ajax({
            url: RoadMapWPFilterAjax.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                console.log('AJAX request successful. Response:', response);
                $('.wp-roadmap-ideas-list').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error:', textStatus, errorThrown);
            }
        });
    }

    // Listen for the Enter key press on the search input field
    $('#roadmap_search_input').keypress(function(e) {
        if (e.which == 13) { // 13 is the keycode for the Enter key
            e.preventDefault(); // Prevent the default form submit action
            var searchTerm = $(this).val();
            updateIdeasList(searchTerm);
        }
    });

    // Listen for click event on the search button
    $('#roadmap_search_submit').click(function(e) {
        e.preventDefault(); // Prevent the default click action
        var searchTerm = $('#roadmap_search_input').val();
        updateIdeasList(searchTerm);
    });
});
