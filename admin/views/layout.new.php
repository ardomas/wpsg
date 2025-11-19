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
/* Small adjustments to ensure our layout uses WP-admin containers */
html, body {
    margin: 0 !important;
    padding: 0 !important;
    height: 100%;
    min-height: 100%;
    font-family: var(--wp--preset--font-family--system-font, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial), sans-serif;
}

/* Use WP admin layout containers so admin CSS applies */
#wpbody, #wpbody-content, #wpcontent {
    margin: 0 !important;
    padding: 0 !important;
    width: 100%;
}

/* Our custom wrapper inside WP containers */
.wpsg-admin-wrapper {
    display: flex;
    min-height: calc(100vh - 32px); /* account for admin bar */
}

/* Sidebar styling override to match WPSG look but within WP admin */
.wpsg-sidebar {
    width: 230px;
    background: #23282d;
    color: #c3c4c7;
}

/* Keep .wrap behavior for WP patterns */
.wpsg-main-content .wrap {
    margin: 0;
    padding: 20px;
    background: #fff;
}
</style>
</head>
<body <?php body_class('wpsg-admin-full wp-admin'); ?>>

<?php
// Output WP Admin Bar (if active) and other admin markup
// Admin bar is printed by wp_toolbar through wp_head() & body_class; nothing else needed here.
?>

<div id="wpbody" role="main">
    <div id="wpbody-content" aria-label="<?php esc_attr_e( 'Main content' ); ?>">
        <div id="wpsg-admin-root">
            <div class="wpsg-admin-wrapper">

                <!-- SIDEBAR -->
                <aside class="wpsg-sidebar" role="navigation" aria-label="<?php esc_attr_e('WPSG Admin Menu'); ?>">
                    <div class="wpsg-sidebar-title" style="padding:12px; font-weight:600; color:#fff;">WPSG Admin</div>

                    <ul style="list-style:none; margin:0; padding:0;">
                        <li><a class="<?php echo $current==='dashboard' ? 'current' : ''; ?>" href="<?php echo esc_url( home_url('/wpsg-admin') ); ?>">Dashboard</a></li>
                        <li><a class="<?php echo $current==='articles' ? 'current' : ''; ?>" href="<?php echo esc_url( home_url('/wpsg-admin/articles') ); ?>">Articles</a></li>
                        <li><a class="<?php echo $current==='announcement' ? 'current' : ''; ?>" href="<?php echo esc_url( home_url('/wpsg-admin/announcement') ); ?>">Announcement</a></li>
                        <li><a class="<?php echo $current==='profile' ? 'current' : ''; ?>" href="<?php echo esc_url( home_url('/wpsg-admin/profile') ); ?>">Profile</a></li>
                        <li><a class="<?php echo $current==='social-media' ? 'current' : ''; ?>" href="<?php echo esc_url( home_url('/wpsg-admin/social-media') ); ?>">Social Media</a></li>
                        <li><a class="<?php echo $current==='membership' ? 'current' : ''; ?>" href="<?php echo esc_url( home_url('/wpsg-admin/membership') ); ?>">Membership</a></li>
                    </ul>
                </aside>

                <!-- MAIN -->
                <main class="wpsg-main-content" role="main" style="flex:1; padding:20px;">
                    <div class="wrap">
                        <?php
                        if ( $view_file && file_exists( $view_file ) ) {
                            include $view_file;
                        } else {
                            echo '<h2>View not found</h2>';
                        }
                        ?>
                    </div>
                </main>

            </div>
        </div>
    </div>
</div>

<?php
// Let WP inject footer scripts/styles
wp_footer();
?>
</body>
</html>
