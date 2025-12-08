<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register announcements shortcode
require_once WPSG_DIR . 'modules/shortcodes/announcements.php';
require_once WPSG_DIR . 'modules/shortcodes/memberships.php';

require_once WPSG_DIR . 'modules/shortcodes/profiles.php';

add_shortcode('wpsg_list_announcements', 'wpsg_shortcode_announcements');
add_shortcode('wpsg_list_memberships'  , 'wpsg_shortcode_memberships'  );
add_shortcode('wpsg_about_short', 'wpsg_shortcode_about_us');

add_shortcode('wpsg_short_values', 'wpsg_shortcode_values');

// add_shortcode('wpsg_short_values_vision' , 'wpsg_shortcode_values_vision'  );
// add_shortcode('wpsg_short_values_mission', 'wpsg_shortcode_values_mission' );
// add_shortcode('wpsg_short_values_goal'   , 'wpsg_shortcode_values_goal'    );