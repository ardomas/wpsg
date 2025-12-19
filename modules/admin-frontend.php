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
        $json = WPSG_DIR . '/assets/json/admin.json';
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
            'WPSG Admin\'s Dashboard',
            'WPSG',
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

        wp_enqueue_style('wpsg-core-layout', plugin_dir_url(__FILE__) . '../assets/css/core-layout.css', [], WPSG_VERSION);
        wp_enqueue_style('wpsg-sidebar'    , plugin_dir_url(__FILE__) . '../assets/css/sidebar.css'    , [], WPSG_VERSION);
        wp_enqueue_style('wpsg-content'    , plugin_dir_url(__FILE__) . '../assets/css/content.css'    , [], WPSG_VERSION);

        require_once WPSG_DIR . 'modules/dashboard.php';
        add_action('admin_enqueue_scripts', ['WPSG_Dashboard', 'enqueue_assets']);
    }

    private function render_sidebar(){

        $sidebar_main = WPSG_AdminData::get_sidebar_menu();

        // Ambil page aktif
        $current_page = isset($GLOBALS['wpsg_current_page']) ? $GLOBALS['wpsg_current_page'] : 'wpsg-admin';
        $current_view = isset($GLOBALS['wpsg_current_view']) ? $GLOBALS['wpsg_current_view'] : 'dashboard';

        ?>

        <div id="wpsg-sidebar" class="wpsg-admin-sidebar">

            <div class="wpsg-sidebar-header">
                <h2>WPSG</h2>
                <span class="version">v<?php echo WPSG_VERSION; ?></span>
            </div>

            <ul class="wpsg-menu">

                <li class="<?php echo $current_page === '' ? 'active' : ''; ?>">
                    <a href="<?php echo site_url('/'); ?>">
                        <span class="dashicons dashicons-admin-home"></span>
                        Back to Home
                    </a>
                </li>

                <?php
                foreach ($sidebar_main as $key => $item_menu) {
                    // Default link

                    // if( $item_menu['site']==='all' || is_super_admin() ) {

                        $link = '/wp-admin';
                        if ($key !== 'wp-admin') {
                            $link = esc_url('admin.php?page=wpsg-admin&view=' . $key);
                        }
                        ?>
                        <li class="<?php echo ($current_view === $key) ? 'active' : ''; ?>">
                            <a href="<?php echo $link; ?>">
                                <span class="dashicons <?php echo esc_attr($item_menu['icon']); ?>"></span>
                                <?php echo esc_html($item_menu['title']); ?>
                            </a>
                        </li>

                    <?php

                    // }

                } 
                ?>

                <!-- Placeholder menu lain -->
                <li class="disabled">
                    <a href="#">
                        <span class="dashicons dashicons-ellipsis"></span>
                        More Modules Coming...
                    </a>
                </li>

            </ul>

        </div><?php

    }

    private function render_display( $view_data ){

        $view   = $GLOBALS['wpsg_current_view'];
        $file   = WPSG_DIR . $view_data['path'];
        $class  = $view_data['class'];
        $method = $view_data['method'] ?? 'render';

        wpsg_enqueue_fontawesome();

        if( file_exists( $file ) ){

            require_once $file;

            if (class_exists($class)) {

                $module = new $class();
                $module->$method();

            } else {

                echo '<h2>Class not found: ' . esc_html($class_name) . '</h2>';

            }

        } else {

            echo '<h2>Module file not found for view: ' . esc_html($view) . '</h2>';
            return;

        }

    }

    private function render_tabs( $menu_data ){

        $tab  = $GLOBALS['wpsg_current_tab'];

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

    /* previous name: get_admin_config() */
    private static function get_admin_menu() {

        $view = $GLOBALS['wpsg_current_view'];
        if( $view == 'dashboard' ) $view = 'sidebar';

        $result = [];
        $admin_config = self::$admin_data; // Load admin.json

        if( $view!='' ){
            if( isset( $admin_config[$view] ) ){
                $result = $admin_config[$view];
            }
        }

        return $result;
    }

    /**
     * LOAD ADMIN PAGE
     */
    public function load_admin_page() {

        $view_with_actions = ['announcements', 'galleries', 'memberships'];
        $is_action = false;

        $page   = $_GET['page']   ?? 'wpsg-admin';
        $view   = $_GET['view']   ?? 'dashboard';
        $tab    = $_GET['tab' ]   ?? '';
        $action = 'list';

        $GLOBALS['wpsg_current_page'  ] = $page;
        $GLOBALS['wpsg_current_view'  ] = $view;
        $GLOBALS['wpsg_current_tab'   ] = $tab;

        if( in_array( $view, $view_with_actions ) ){
            $is_action = true;
            $action = $_GET['action'] ?? 'list';
            $GLOBALS['wpsg_current_action'] = $action;
        }

        $sidebar_menu = WPSG_AdminData::get_sidebar_menu();

        ?>

        <div id="wpsg-admin-container" style="display:flex; align-items:flex-start;">

            <!-- SIDEBAR -->
            <?php $this->render_sidebar(); ?>

            <!-- MAIN CONTENT -->
            <div id="wpsg-admin-main" style="flex:1; padding:20px;">
                <div class="wrap">
                    <?php

                    if (!isset($sidebar_menu[$view])) {

                        $sidebar_menu = WPSG_AdminData::get($view);
                        if( $sidebar_menu['data'] ){
                            $view_data = $sidebar_menu['data'];
                            self::render_display( $view_data[$action] );
                        }
                    } else {

                        $view_data = $sidebar_menu[$view];

                        $submenu = $view_data['submenu'] ?? null;

                        if ($submenu) {

                            self::render_tabs( self::get_admin_data_by_key($submenu)['data'] );

                        } else {

                            if( $is_action ){

                                $view_data = self::get_admin_data_by_key( $view )['data'];

                                self::render_display( $view_data[$action] );

                            } else {

                                self::render_display( $view_data );

                            }

                        }

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
