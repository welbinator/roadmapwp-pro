<?php
// Hook into the filter provided by the free version
add_filter('wp_roadmap_pro_add_taxonomy_feature', 'wp_roadmap_pro_custom_taxonomy_content');

/**
 * Outputs the HTML content for the custom taxonomy management in the Pro version.
 *
 * @return string The HTML output for the custom taxonomy management.
 */
function wp_roadmap_pro_custom_taxonomy_content() {
    ob_start();
    ?>
    <div class="wrap">
        <h1>Add New Custom Taxonomy</h1>
        <form action="" method="post">
            <?php wp_nonce_field('wp_roadmap_pro_add_taxonomy', 'wp_roadmap_pro_nonce'); ?>
            <ul class="flex-outer">
                <li class="new_taxonomy_form_input">
                    <label for="taxonomy_slug">Slug:</label>
                    <input type="text" id="taxonomy_slug" name="taxonomy_slug" required>
                </li>

                <li class="new_taxonomy_form_input">
                    <label for="taxonomy_singular">Singular Name:</label>
                    <input type="text" id="taxonomy_singular" name="taxonomy_singular" required>
                </li>

                <li class="new_taxonomy_form_input">
                    <label for="taxonomy_plural">Plural Name:</label>
                    <input type="text" id="taxonomy_plural" name="taxonomy_plural" required>
                </li>

                <li class="new_taxonomy_form_input">
                    <label for="public">Public:</label>
                    <select id="public" name="public">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </li>

                <li class="new_taxonomy_form_input">
                    <input type="submit" value="Add Taxonomy">
                </li>
            </ul>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

// Add additional logic here if needed for handling form submissions, etc.
