/**
 * Use this file for JavaScript code that you want to run in the front-end 
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any 
 * JavaScript running in the front-end, then you should delete this file and remove 
 * the `viewScript` property from `block.json`. 
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */
 
/* eslint-disable no-console */
document.addEventListener('DOMContentLoaded', function() {
    var blocks = document.querySelectorAll('div[data-selected-statuses]');
    // console.log('Blocks found:', blocks.length);
    blocks.forEach(function(block) {
        var selectedStatuses = block.getAttribute('data-selected-statuses').split(',');
        // console.log('Selected Statuses:', selectedStatuses);
        var form = block.querySelector('form');
        // console.log('Form found:', form);
        if (form) {
            selectedStatuses.forEach(function(statusId) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_statuses[]';
                input.value = statusId;
                form.appendChild(input);
                // console.log('Input added for status:', statusId);
            });
        }
    });
});

/* eslint-enable no-console */
