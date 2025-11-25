<?php
// admin/admin-frontend.php
if (!defined('ABSPATH')) exit;

class WPSG_AdminFrontend {

    private static $instance = null;
    private static $admin_data = [];

    private function __construct() {
        // Load Admin Default Data
        self::load_admin_default_data();

        // REGISTER MENU
        add_action('admin_menu', [$this, 'register_admin_menu']);

        // ENQUEUE GLOBAL ADMIN ASSETS
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function load_admin_default_data() {
        $json = plugin_dir_path(__FILE__) . 'assets/json/admin.json';
        if (!file_exists($json)) return [];
        self::$admin_data = json_decode(file_get_contents($json), true);
        return self::$admin_data;
    }

    public function get_admin_data() {
        return self::$admin_data;
    }

    public function get_admin_data_by_key($key) {
        return self::$admin_data[$key] ?? null;
    }

    /**
     * REGISTER ADMIN MENU
     */
    public function register_admin_menu() {
        add_menu_page(
            'WPSG Dashboard',
            'WPSG Admin',
            'manage_options',
            'wpsg-admin',
            [$this, 'load_admin_page'],
            'dashicons-admin-generic',
            3
        );
    }

    /**
     * ENQUEUE GLOBAL ADMIN ASSETS
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_wpsg-admin' && strpos($hook, 'wpsg-admin') === false) return;

        wp_enqueue_style('dashicons');
        wp_enqueue_style('wp-admin');
        wp_enqueue_style('admin-menu');
        wp_enqueue_style('admin-bar');
        wp_enqueue_style('common');
        wp_enqueue_style('forms');

        wp_enqueue_editor();
        wp_enqueue_media();

        wp_enqueue_style('wpsg-core-layout', plugin_dir_url(__FILE__) . 'assets/css/core-layout.css', [], WPSG_VERSION);
        wp_enqueue_style('wpsg-sidebar'    , plugin_dir_url(__FILE__) . 'assets/css/sidebar.css'    , [], WPSG_VERSION);
        wp_enqueue_style('wpsg-content'    , plugin_dir_url(__FILE__) . 'assets/css/content.css'    , [], WPSG_VERSION);

        require_once WPSG_DIR . 'admin/modules/class-dashboard.php';
        add_action('admin_enqueue_scripts', ['WPSG_Dashboard', 'enqueue_assets']);
    }

    public function render_display( $view_data ){

        $view  = $_GET['view'] ?? 'dashboard';
        $file  = WPSG_DIR . $view_data['path'];
        $class = $view_data['module_class'];

        if( file_exists( $file ) ){

            require_once $file;

            if (class_exists($class)) {

                $module = new $class();
                $module->render();

            } else {

                echo '<h2>Class not found: ' . esc_html($class_name) . '</h2>';

            }

        } else {

            echo '<h2>Module file not found for view: ' . esc_html($view) . '</h2>';
            return;

        }

    }

    public function render_tabs( $menu_data ){

        $tab  = $_GET['tab'] ?? '';

        $current_tab = $tab ?: array_key_first($menu_data);

        echo '<h2 class="nav-tab-wrapper">';
        foreach ($menu_data as $tab_key => $tab_item) {

            if( $tab_item['view']===true ){
                $url = add_query_arg(['tab' => $tab_key]);
                $active = ($current_tab === $tab_key) ? 'nav-tab-active' : '';
                echo "<a href='" . esc_url($url) . "' class='nav-tab $active'>" . esc_html($tab_item['title']) . "</a>";
            }
        }
        echo '</h2>';

        $view_data = $menu_data[$current_tab];
        $this->render_display( $view_data );

    }

    public function get_admin_config($page, $view, $action = 'list') {

        $admin_config = self::admin_data; // Load admin.json

        if (isset($admin_config[$view][$action])) {
            return $admin_config[$view][$action];
        }

        // fallback: default list
        if (isset($admin_config[$view]['list'])) {
            return $admin_config[$view]['list'];
        }

        return null;
    }

    /**
     * LOAD ADMIN PAGE
     */
    public function load_admin_page() {
        $page = $_GET['page'] ?? 'wpsg-admin';
        $view = $_GET['view'] ?? 'dashboard';

        // $action = $_GET['action'] ?? '';
        // $config = $this->get_admin_config($page, $view, $action);

        $GLOBALS['wpsg_current_page'] = $page;
        $GLOBALS['wpsg_current_view'] = $view;

        $sidebar_menu = self::get_admin_data_by_key('sidebar-menu');

        ?>
        <div id="wpsg-admin-container" style="display:flex; align-items:flex-start;">

            <!-- SIDEBAR -->
            <?php require plugin_dir_path(__FILE__) . 'views/sidebar.php'; ?>

            <!-- MAIN CONTENT -->
            <div id="wpsg-admin-main" style="flex:1; padding:20px;">
                <div class="wrap">
                    <?php
                    if (!isset($sidebar_menu[$view])) {
                        echo '<h2>404: View Not Found</h2>';
                        return;
                    }

                    $view_data = $sidebar_menu[$view];
                    $submenu = $view_data['submenu'] ?? null;

                    if ($submenu) {

                        self::render_tabs( self::get_admin_data_by_key($submenu) );

                    } else {

                        self::render_display( $view_data );

                    }
                    ?>
                </div>
            </div>

        </div>
        <?php
    }
}

// INITIALIZE
WPSG_AdminFrontend::get_instance();
