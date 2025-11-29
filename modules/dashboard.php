<?php
if (!defined('ABSPATH')) exit;

class WPSG_Dashboard {

    /*
    public static function enqueue_assets($hook) {

        if ($hook !== 'toplevel_page_wpsg-admin' && strpos($hook, 'wpsg-admin') === false) return;

    }
    */

    public function render() {

        wp_enqueue_style('wpsg-dashboard', plugin_dir_url(__FILE__) . '../assets/css/dashboard.css', [], WPSG_VERSION);

        ?>

        <div class="wpsg">

            <h1>WPSG Dashboard</h1>
            <p>Welcome to WPSG Administration's Panel.</p>

            <div class="wpsg-admin-wrapper">
                <div class="wpsg-flex" style="gap: 20px; flex-wrap: wrap;">
                    <?php self::render_cards(); ?>
                </div>
            </div>

        </div>

        <?php

    }

    public static function render_cards() {

        $sidebar_menu = WPSG_AdminData::get_sidebar_menu();

        foreach ($sidebar_menu as $key => $item_menu) {

            if ( $item_menu['dashboard'] ) {
                $link = esc_url('admin.php?page=wpsg-admin&view=' . $key);
                ?>
                <a class="wpsg-card-link" href="<?php echo $link; ?>"><div class="wpsg-card">
                        <h3><?php echo esc_html($item_menu['title']); ?></h3>
                        <p><?php echo esc_html($item_menu['description']); ?></p>
                </div></a>
                <?php
            }

        }
    }

}
