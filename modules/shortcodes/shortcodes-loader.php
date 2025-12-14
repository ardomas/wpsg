<?php
if (!defined('ABSPATH')) {
    exit;
}

// Register announcements shortcode
require_once WPSG_DIR . 'modules/shortcodes/announcements.php';
require_once WPSG_DIR . 'modules/shortcodes/memberships.php';

require_once WPSG_DIR . 'modules/shortcodes/profiles.php';

require_once WPSG_DIR . 'modules/shortcodes/galleries.php';

add_shortcode('wpsg_profile_about_short' , 'wpsg_shortcode_about_us'     );
add_shortcode('wpsg_profile_short_legal' , 'wpsg_shortcode_legal'        );
add_shortcode('wpsg_profile_short_values', 'wpsg_shortcode_values'       );

add_shortcode('wpsg_announcements_list'  , 'wpsg_shortcode_announcements');

add_shortcode('wpsg_memberships_list'    , 'wpsg_shortcode_memberships'  );

add_shortcode('wpsg_media_gallery', 'wpsg_media_gallery_shortcode' );

// add_shortcode('wpsg_short_values_vision' , 'wpsg_shortcode_values_vision'  );
// add_shortcode('wpsg_short_values_mission', 'wpsg_shortcode_values_mission' );
// add_shortcode('wpsg_short_values_goal'   , 'wpsg_shortcode_values_goal'    );