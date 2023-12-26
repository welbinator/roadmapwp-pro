document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.idea-status-update-form').forEach(function(form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            var ideaId = this.getAttribute('data-idea-id');
            var selectedStatuses = Array.from(this.querySelector('.status-select').selectedOptions).map(option => option.value);

            // Add the console.log here
            console.log('Sending AJAX request with idea ID:', ideaId, 'and statuses:', selectedStatuses);

            var formData = new FormData();
            formData.append('action', 'update_idea_status');
            formData.append('idea_id', ideaId);
            formData.append('statuses', JSON.stringify(selectedStatuses));
            formData.append('nonce', wpRoadMapAjax.nonce);

            fetch(wpRoadMapAjax.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Status updated!');
                } else {
                    alert('Error updating status.');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
