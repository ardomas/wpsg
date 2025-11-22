<?php
if (!defined('ABSPATH')) exit;

class WPSG_Settings {

    public static function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_wpsg-admin' && strpos($hook, 'wpsg-admin') === false) return;
        // enqueue CSS/JS umum untuk Settings jika diperlukan
    }

    public function render() {
        // Tentukan tab aktif
        $tab = $_GET['tab'] ?? 'general';

        // Ambil semua tab settings dari admin.json
        $settings_menu = WPSG_AdminData::get('settings-menu', []);

        ?>
        <div class="wpsg">
            <h1>Settings</h1>
            <p>Configure default settings for your site/organization.</p>

            <!-- Tab navigation -->
            <h2 class="nav-tab-wrapper">
                <?php foreach ($settings_menu as $key => $item): ?>
                    <a href="<?php echo esc_url(add_query_arg('tab', $key)); ?>"
                    class="nav-tab <?php echo ($tab === $key) ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html($item['title']); ?>
                    </a>
                <?php endforeach; ?>
            </h2>

            <!-- Tab content -->
            <div class="wpsg-tab-content">
                <?php
                if (isset($settings_menu[$tab])) {
                    $sub_tab_file = WPSG_DIR . $settings_menu[$tab]['path'];
                    $class_name = $settings_menu[$tab]['module_class'];

                    if (file_exists($sub_tab_file)) {
                        require_once $sub_tab_file;

                        if (class_exists($class_name)) {
                            $module = new $class_name();
                            $module->render();
                        } else {
                            echo '<p>Class not found: ' . esc_html($class_name) . '</p>';
                        }
                    } else {
                        echo '<p>Tab file not found: ' . esc_html($sub_tab_file) . '</p>';
                    }
                } else {
                    echo '<p>Tab not found: ' . esc_html($tab) . '</p>';
                }
                ?>
            </div>
        </div>
        <?php
    }


    // Helper untuk get/save option
    public static function get($key, $default = '') {
        return get_option($key, $default);
    }

    public static function save($key, $value) {
        update_option($key, $value);
    }
}
