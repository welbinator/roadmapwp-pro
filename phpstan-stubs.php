<?php
// Minimal stubs for PHPStan analysis of this plugin.

// Minimal stubs for PHPStan analysis: only define functions that are not provided by
// common wordpress-stubs packages. We only need LearnDash helper stub here.
if ( ! function_exists( 'sfwd_lms_has_access' ) ) {
    /**
     * Stub for LearnDash access check used in plugin code. Return type is bool.
     * PHPStan will use this signature for analysis; runtime behavior is irrelevant.
     *
     * @param int      $course_id
     * @param int|null $user_id
     * @return bool
     */
    function sfwd_lms_has_access( $course_id, $user_id = null ): bool {
        return true;
    }
}

// Define a couple of plugin constants used across the codebase so PHPStan can resolve them.
if ( ! defined( 'RMWP_PLUGIN_VERSION' ) ) {
    define( 'RMWP_PLUGIN_VERSION', 'dev' );
}
if ( ! defined( 'ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE' ) ) {
    define( 'ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE', 'roadmapwp-license' );
}
