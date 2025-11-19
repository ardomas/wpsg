<?php
// admin/views/layout.php
if ( ! defined('ABSPATH') ) exit;

// expected globals set by router:
$current = isset($GLOBALS['wpsg_current_page']) ? $GLOBALS['wpsg_current_page'] : 'dashboard';
$view_file = isset($GLOBALS['wpsg_view_file']) ? $GLOBALS['wpsg_view_file'] : null;

// Allow WP admin bar etc.
if ( is_user_logged_in() ) {
    show_admin_bar(true);
}

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( 'WPSG Admin — ' . ucfirst($current) ); ?></title>
<?php
// Let WP inject styles/scripts (we enqueued admin CSS earlier)
wp_head();

?>

<style>
    /* Wrapper */
    html, body {
        margin: 0px !important;
        padding: 0px !important;
    }

    .wpsg-admin-wrapper {
        margin: 0px !important;
        padding: 0px !important;
        display: flex;
        min-height: 100vh;
        background: #f1f1f1;
    }

    .wpsg-sticky-top {
        position: sticky;
        top: 0;
    }

    /* Sidebar */
    .wpsg-sidebar {
        margin: 0px !important;
        padding: 0px !important;
        background: #23282d;
        width: 230px;
        font-size: 1rem;
    }

    .wpsg-sidebar-title {
        cursor: default;
        font-size: 1.5rem;
        padding: 15px;
        text-align: center;
        font-weight: bold;
        background: #082847;
        border-bottom: 1px solid #4e5358;
        color: #f0f0f0;
    }

    .wpsg-home-menu {
        background: #23282d;
        /* text-align: center; */
        border-bottom: 1px solid #44494e;
        height: auto;
    }

    .wpsg-group-menu .wpsg-item-menu {
        height: auto;
    }
    .wpsg-home-menu a,
    .wpsg-group-menu .wpsg-item-menu a {
        display: block;
        padding: 12px 20px;
        color: #c3c4c7;
        text-decoration: none;
    }
    .wpsg-home-menu a:hover,
    .wpsg-group-menu .wpsg-item-menu a:hover {
        /* background: #191e23; */
        background: #031d36;
        color: #98c4e7;
    }

    /* Main Content */
    .wpsg-main-content {
        flex: 1;
        padding: 30px;
        background: #fff;
        min-height: 100vh;
    }
</style>

<?php

function wpsg_active($key) {
    return ($GLOBALS['wpsg_current_page'] === $key) ? ' class="active"' : '';
}

?>

<div class="wpsg-admin-wrapper">

    <!-- SIDEBAR -->
    <aside class="wpsg-sidebar">
        <div class="wpsg-sticky-top">
            <div class="wpsg-sidebar-title">
                WPSG Admin
            </div>
            <!-- Back to home page -->
            <div class="wpsg-home-menu">
                <a href="/"<?= wpsg_active('home') ?>">Home</a>
            </div>
        </div>

        <!-- static menu -->
        <div class="wpsg-group-menu">
            <div class="wpsg-item-menu"><a href="/wp-admin"<?= wpsg_active('../') ?>">
                WP-Admin
            </a></div>
            <div class="wpsg-item-menu"><a href="/wpsg-admin"<?= wpsg_active('dashboard') ?>>Dashboard</a></div>
        </div>

        <!-- dynamic menu -->
        <div class="wpsg-group-menu">

            <div class="wpsg-item-menu"><a href="/wpsg-admin/profile"<?= wpsg_active('profile') ?>>Profile</a></div>
            <div class="wpsg-item-menu"><a href="/wpsg-admin/articles"<?= wpsg_active('articles') ?>>Articles</a></div>
            <div class="wpsg-item-menu"><a href="/wpsg-admin/announcement"<?= wpsg_active('announcement') ?>>Announcement</a></div>
            <div class="wpsg-item-menu"><a href="/wpsg-admin/social-media"<?= wpsg_active('social-media') ?>>Social Media</a></div>
            <div class="wpsg-item-menu"><a href="/wpsg-admin/membership"<?= wpsg_active('membership') ?>>Membership</a></div>

        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="wpsg-main-content">

        <?php 
        // include $GLOBALS['wpsg_view_file']; 
        ?>
        <?php
        // Make sure $view_file set from admin-frontend.php
        // Tampilkan file konten halaman
        if ( isset($view_file) && file_exists($view_file) ) {
            include $view_file;
        } else {
            echo "<h2>View file not found.</h2>";
        }
        ?>

    </main>

</div>

