<?php
// admin/views/layout.php
if ( ! defined('ABSPATH') ) exit;

// expected globals set by router:
$current = isset($GLOBALS['wpsg_current_page']) ? $GLOBALS['wpsg_current_page'] : 'dashboard';
$view_file = isset($GLOBALS['wpsg_view_file']) ? $GLOBALS['wpsg_view_file'] : null;

if ( is_user_logged_in() ) {
    show_admin_bar(true);
}

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( 'WPSG Admin — ' . ucfirst($current) ); ?></title>
<?php wp_head(); ?>

<style>
/* Reset margin/padding */
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
}

/* Wrapper: flex layout */
.wpsg-admin-wrapper {
    display: flex;
    height: 100vh;
    overflow: hidden;
    background: #f1f1f1;
}

/* SIDEBAR */
.wpsg-sidebar {
    width: 230px;
    background: #23282d;
    color: #c3c4c7;
    display: flex;
    flex-direction: column;
}

/* Sticky top part */
.wpsg-sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #23282d;
    border-bottom: 1px solid #44494e;
}

/* Sidebar title */
.wpsg-sidebar-title {
    font-size: 1.5rem;
    font-weight: bold;
    text-align: center;
    padding: 15px;
    background: #082847;
    color: #f0f0f0;
    border-bottom: 1px solid #4e5358;
}

/* Home menu link */
.wpsg-home-menu a {
    display: block;
    padding: 12px 20px;
    color: #c3c4c7;
    text-decoration: none;
}
.wpsg-home-menu a:hover {
    background: #031d36;
    color: #98c4e7;
}

/* Sidebar menu items */
.wpsg-group-menu {
    flex: 1;
    overflow-y: auto; /* scroll independen */
}
.wpsg-item-menu a {
    display: block;
    padding: 12px 20px;
    color: #c3c4c7;
    text-decoration: none;
}
.wpsg-item-menu a:hover {
    background: #031d36;
    color: #98c4e7;
}

/* Active link */
.wpsg-item-menu a.active {
    background: #031d36;
    color: #98c4e7;
    font-weight: bold;
}

/* MAIN CONTENT */
.wpsg-main-content {
    flex: 1;
    background: #fff;
    padding: 30px;
    overflow-y: auto; /* scroll independen */
}

/* Section card styling */
.wpsg-section-card {
    border: 1px solid #ddd;
    border-radius: 6px;
    background: #fff;
    padding: 20px;
    margin-bottom: 25px;
}
.wpsg-section-card h2 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 1.3rem;
}

/* Form row styling */
.wpsg-form-row {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 15px;
    align-items: flex-start;
}
.wpsg-form-row label {
    width: 200px;
    font-weight: bold;
    padding-top: 5px;
}
.wpsg-form-row input[type=text],
.wpsg-form-row input[type=email],
.wpsg-form-row textarea,
.wpsg-form-row select {
    flex: 1;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

/* Logo preview */
.wpsg-logo-preview {
    display: block;
    max-width: 150px;
    max-height: 150px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    padding: 5px;
    border-radius: 4px;
}
#choose-logo {
    margin-top: 5px;
}

/* Full-width WP Editor */
.wp-editor-wrap, .wp-editor-container, .wp-editor-area {
    width: 100% !important;
    min-height: 250px;
    margin-top: 5px;
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
            <div class="wpsg-sidebar-title">WPSG Admin</div>
            <div class="wpsg-home-menu">
                <a href="/"<?= wpsg_active('home') ?>>Home</a>
            </div>
        </div>

        <!-- Static menu -->
        <div class="wpsg-group-menu">
            <div class="wpsg-item-menu"><a href="/wp-admin"<?= wpsg_active('../') ?>>WP-Admin</a></div>
            <div class="wpsg-item-menu"><a href="/wpsg-admin"<?= wpsg_active('dashboard') ?>>Dashboard</a></div>
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
        if ( isset($view_file) && file_exists($view_file) ) {
            include $view_file;
        } else {
            echo "<h2>View file not found.</h2>";
        }
        ?>
    </main>

</div>
