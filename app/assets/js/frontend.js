document.addEventListener('DOMContentLoaded', function() {
    // Loop over each form and attach a submit event listener
    document.querySelectorAll('.rmwp__idea-status-update-form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission
            
            // Gather form data
            var ideaId = this.getAttribute('data-idea-id');
            var selectedStatuses = Array.from(this.querySelector('.rmwp__status-select').selectedOptions).map(option => option.value);
            
            // Prepare form data for AJAX
            var formData = new FormData();
            formData.append('action', 'update_idea_status');
            formData.append('idea_id', ideaId);
            formData.append('statuses', JSON.stringify(selectedStatuses));
            formData.append('nonce', RoadMapWPAdminFrontendAjax.nonce);
            
            // Log form data for debugging
            formData.forEach((value, key) => {
                console.log(key + ': ' + value);
            });
            console.log('Nonce:', RoadMapWPAdminFrontendAjax.nonce); // Log nonce value
            
            // Check if test field exists for debugging purposes
            if (RoadMapWPAdminFrontendAjax.test) {
                console.log(RoadMapWPAdminFrontendAjax.test); // Log test value
            } else {
                console.log('Test value not found in localized script.');
            }
            
            // Send AJAX request
            fetch(RoadMapWPAdminFrontendAjax.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(response => {
                console.log('Raw response:', response); // Log raw response
                return response.json(); // Parse the JSON response
            })
            .then(data => {
                console.log('Parsed data:', data); // Log parsed data
                
                // Check success flag and show appropriate message
                if (data.success) {
                    alert('Status updated successfully!');
                } else {
                    alert('Error updating status: ' + (data.message || 'Unknown error.'));
                    console.log('Error details:', data); // Log error details if any
                }
            })
            .catch(error => {
                console.error('Error in fetch request:', error); // Log any fetch errors
            });
        });
    });
});
