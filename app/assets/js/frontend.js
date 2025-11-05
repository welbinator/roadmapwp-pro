document.addEventListener('DOMContentLoaded', function() {
    if (!window.RoadMapWPAdminFrontendAjax || !RoadMapWPAdminFrontendAjax.ajax_url) {
        console.error('RoadMapWP Error: Frontend AJAX configuration missing');
        return;
    }

    document.querySelectorAll('.rmwp__idea-status-update-form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            
            if (!RoadMapWPAdminFrontendAjax.nonce) {
                console.error('RoadMapWP Error: Security nonce missing');
                alert('Error: Security validation failed. Please refresh the page and try again.');
                return;
            }

            var ideaId = this.getAttribute('data-idea-id');
            var selectElement = this.querySelector('.rmwp__status-select');
            
            if (!selectElement) {
                console.error('RoadMapWP Error: Status select element not found');
                return;
            }

            var selectedStatuses = Array.from(selectElement.selectedOptions).map(option => option.value);
            
            if (!selectedStatuses.length) {
                alert('Please select at least one status.');
                return;
            }

            // Debug logging
            if (RoadMapWPAdminFrontendAjax.debug) {
                console.log('Selected statuses:', selectedStatuses);
                console.log('Idea ID:', ideaId);
            }

            var formData = new FormData();
            formData.append('action', 'update_idea_status');
            formData.append('idea_id', ideaId);
            formData.append('statuses', selectedStatuses.join(',')); // Send as comma-separated string instead of JSON
            formData.append('nonce', RoadMapWPAdminFrontendAjax.nonce);

            fetch(RoadMapWPAdminFrontendAjax.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Server response:', data); // Debug log
                if (data.success) {
                    alert(data.data.message || 'Status updated!');
                    console.log('Updated terms:', data.data.updated_terms); // Debug log
                } else {
                    console.error('Status update failed:', data);
                    alert(data.data.message || 'Error updating status. Check console for details.');
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                console.log('Request details:', {
                    url: RoadMapWPAdminFrontendAjax.ajax_url,
                    ideaId: ideaId,
                    statuses: selectedStatuses,
                    noncePresent: !!RoadMapWPAdminFrontendAjax.nonce
                });
                alert('Error connecting to server. Check console for details.');
            });
        });
    });
});
