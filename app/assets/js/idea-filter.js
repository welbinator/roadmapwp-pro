jQuery(document).ready(function($) {

    function sendProAjaxRequest() {
        var searchTerm = $('#roadmap_search_input').val(); // Search input value

        // AJAX request with the search term
        $.ajax({
            url: RoadMapWPFilterAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_ideas',
                search_term: searchTerm, // Pass the search term
                nonce: RoadMapWPFilterAjax.nonce // Security nonce
            },
            success: function(response) {
                $('.rmwp__ideas-list').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error:', textStatus, errorThrown);
            }
        });
    }

    // Bind the sendProAjaxRequest function to search-related actions
    $('#roadmap_search_submit').click(function(e) {
        e.preventDefault();
        sendProAjaxRequest();
    });

    $('#roadmap_search_input').keypress(function(e) {
        if(e.which == 13) {
            e.preventDefault();
            sendProAjaxRequest();
        }
    });
});
