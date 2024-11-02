document.addEventListener("DOMContentLoaded", function() {


    function togglePageSetting() {
        var templateSelect = document.getElementById("wp_roadmap_single_idea_template");
        var pageSetting = document.getElementById("single_idea_page_setting");

        // Check if elements exist
        if (templateSelect && pageSetting) {
            var selectedTemplate = templateSelect.value;
            pageSetting.style.display = (selectedTemplate === "page") ? "" : "none";
        }
    }

    togglePageSetting();

    var templateSelect = document.getElementById("wp_roadmap_single_idea_template");
    if (templateSelect) {
        templateSelect.addEventListener("change", togglePageSetting);
    }
});
