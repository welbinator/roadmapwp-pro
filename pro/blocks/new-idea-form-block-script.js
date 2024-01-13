document.addEventListener('DOMContentLoaded', function() {
    var blocks = document.querySelectorAll('div[data-selected-statuses]');
    console.log('Blocks found:', blocks.length);  // Debug: Check if blocks are found
    blocks.forEach(function(block) {
        var selectedStatuses = block.getAttribute('data-selected-statuses').split(',');
        console.log('Selected Statuses:', selectedStatuses);  // Debug: Log selected statuses
        var form = block.querySelector('form');
        console.log('Form found:', form);  // Debug: Check if form is found
        if (form) {
            selectedStatuses.forEach(function(statusId) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_statuses[]';
                input.value = statusId;
                form.appendChild(input);
                console.log('Input added for status:', statusId);  // Debug: Log input addition
            });
        }
    });
});
