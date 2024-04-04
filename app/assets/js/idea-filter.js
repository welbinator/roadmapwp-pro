jQuery(document).ready(function($) {
    function sendAjaxRequest() {
        var searchTerm = $('#roadmap_search_input').val(); // Assuming this is your search input ID
        var filterData = {};

        // Collecting filter data
        $('.rmwp__ideas-filter-taxonomy').each(function() {
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

        // AJAX request with both search term and filters
        $.ajax({
            url: RoadMapWPFilterAjax.ajax_url,
            type: 'POST',
            data: {
                'action': 'filter_ideas',
                'search_term': searchTerm, // Pass the search term
                'filter_data': filterData, // Pass the filter data
                'nonce': RoadMapWPFilterAjax.nonce // Security nonce
            },
            success: function(response) {
                $('.rmwp__ideas-list').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error:', textStatus, errorThrown);
            }
        });
    }

    // Bind the sendAjaxRequest function to both search and filter changes
    $('#roadmap_search_submit').click(function(e) {
        e.preventDefault();
        sendAjaxRequest();
    });

    $('#roadmap_search_input').keypress(function(e) {
        if(e.which == 13) {
            e.preventDefault();
            sendAjaxRequest();
        }
    });

    $('.rmwp__ideas-filter-taxonomy input').change(sendAjaxRequest);
});
