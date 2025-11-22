jQuery(document).ready(function($) {
    // Template chooser functionality
    function togglePageSetting() {
        var selectedTemplate = $("#wp_roadmap_single_idea_template").val();
        if (selectedTemplate === "page") {
            $("#single_idea_page_setting").show();
        } else {
            $("#single_idea_page_setting").hide();
        }
    }
    togglePageSetting();
    $("#wp_roadmap_single_idea_template").change(togglePageSetting);

    // Custom idea heading toggle
    var ideaHeadingCheckbox = document.getElementById("hide_custom_idea_heading");
    if (ideaHeadingCheckbox) {
        function toggleIdeaHeadingInput() {
            var label = document.querySelector("label[for='custom_idea_heading']");
            var input = document.querySelector("input[name='wp_roadmap_settings[custom_idea_heading]']");
            if (ideaHeadingCheckbox.checked) {
                label.style.display = "none";
                input.style.display = "none";
            } else {
                label.style.display = "inline";
                input.style.display = "inline";
            }
        }
        ideaHeadingCheckbox.addEventListener("change", toggleIdeaHeadingInput);
        toggleIdeaHeadingInput();
    }

    // Display ideas heading toggle
    var displayIdeasCheckbox = document.getElementById("hide_display_ideas_heading");
    if (displayIdeasCheckbox) {
        function toggleDisplayHeadingInput() {
            var label = document.querySelector("label[for='custom_display_ideas_heading']");
            var input = document.querySelector("input[name='wp_roadmap_settings[custom_display_ideas_heading]']");
            if (displayIdeasCheckbox.checked) {
                label.style.display = "none";
                input.style.display = "none";
            } else {
                label.style.display = "inline";
                input.style.display = "inline";
            }
        }
        displayIdeasCheckbox.addEventListener("change", toggleDisplayHeadingInput);
        toggleDisplayHeadingInput();
    }
});