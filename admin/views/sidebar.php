<?php
// admin/views/sidebar.php

if (!defined('ABSPATH')) exit;

require_once WPSG_DIR . 'includes/class-admin-data.php';

// Ambil sidebar menu dari WPSG_AdminData
$sidebar_menu = WPSG_AdminData::get_sidebar_menu();

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
        foreach ($sidebar_menu as $key => $item_menu) {
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

</div>
