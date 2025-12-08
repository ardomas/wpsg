<?php
/**
 * modules/frontend/frontend-loader.php
 * Loader & controller untuk modul frontend WPSG
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('WPSG_Frontend')):

class WPSG_Frontend {

    /**
     * Initialize module
     */
    public static function init() {
        // shortcodes & actions
        add_action('init', [__CLASS__, 'register_shortcodes']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

        // If developer wants plugin to replace front-page content automatically,
        // define WPSG_FRONTPAGE_OVERRIDE as true in plugin main or wp-config.php.
        add_action('wp', function() {
            if ( is_front_page() && defined('WPSG_FRONTPAGE_OVERRIDE') && WPSG_FRONTPAGE_OVERRIDE ) {
                // replace the_content on front page only
                add_filter('the_content', [__CLASS__, 'render_frontpage_content'], 20);
            }
        });
    }

    /**
     * Register shortcodes
     */
    public static function register_shortcodes() {
        add_shortcode('wpsg_home', [__CLASS__, 'shortcode_home']);
    }

    /**
     * Shortcode callback
     */
    public static function shortcode_home($atts = [], $content = null) {
        // optional attributes handling
        $atts = shortcode_atts([], $atts, 'wpsg_home');

        ob_start();
        self::render_content_partial();
        return ob_get_clean();
    }

    /**
     * Render content partial and return it for the_content filter
     */
    public static function render_frontpage_content($original_content = '') {
        // Replace default content entirely with plugin frontpage.
        // If you want to append instead, return $original_content . $partial;
        ob_start();
        self::render_content_partial();
        return ob_get_clean();
    }

    /**
     * Include content partial (front-page-content.php)
     */
    public static function render_content_partial() {
        $file = WPSG_DIR . 'modules/frontend/front-page-content.php';
        if (file_exists($file)) {
            // provide $data to the partial
            $data = self::get_frontpage_data();
            include $file;
        } else {
            echo '<p>WPSG front page content not found.</p>';
        }
    }

    /**
     * Enqueue assets (css/js)
     */
    public static function enqueue_assets() {
        // Only enqueue on pages where shortcode is present OR front page override
        // We use a lightweight approach: enqueue always, but you can optimize later.
        $css = WPSG_URL . 'modules/frontend/assets/css/frontend.css';
        $js  = WPSG_URL . 'modules/frontend/assets/js/frontend.js';

        if ( file_exists( WPSG_DIR . 'modules/frontend/assets/css/frontend.css' ) ) {
            wp_enqueue_style('wpsg-frontend', $css, [], '1.0.0');
        }

        if ( file_exists( WPSG_DIR . 'modules/frontend/assets/js/frontend.js' ) ) {
            wp_enqueue_script('wpsg-frontend', $js, ['jquery'], '1.0.0', true);
        }
    }

    /**
     * Build data to be used on the front page partial.
     * Replace the placeholders with real DB/API calls later.
     */
    public static function get_frontpage_data() {
        $current_user = wp_get_current_user();

        // Example placeholders: replace with actual queries to WPSG core
        $children = [];
        if ( function_exists('wpsg_get_children_by_parent') ) {
            $children = wpsg_get_children_by_parent($current_user->ID);
        }

        $today_activities = [];
        if ( function_exists('wpsg_get_today_activities') && ! empty($children) ) {
            $child_ids = wp_list_pluck($children, 'id');
            $today_activities = wpsg_get_today_activities($child_ids);
        }

        $announcements = [];
        if ( function_exists('wpsg_get_announcements') ) {
            $announcements = wpsg_get_announcements(5);
        }

        return [
            'user' => $current_user,
            'children' => $children,
            'activities' => $today_activities,
            'announcements' => $announcements,
        ];
    }
}

endif;

// Auto-init when file included
WPSG_Frontend::init();
