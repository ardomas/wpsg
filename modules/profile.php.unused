<?php
if (!defined('ABSPATH')) exit;

class WPSG_Profile {

    /**
     * Load assets only for profile view
     */
    public static function enqueue_assets($hook) {
        if ($hook !== 'toplevel_page_wpsg-admin' && strpos($hook, 'wpsg-admin') === false) return;

        // Only load if view is 'profile'
        $view = $_GET['view'] ?? '';
        if ($view !== 'profile') return;

        // follow dashboard model (no CSS for now)
    }

    /**
     * Render main Profile Management page
     */
    public function render() {
        ?>
        <div class="wpsg">

            <h1>Profile Management</h1>
            <p>Manage all organizational profile settings.</p>

            <!-- Back to Dashboard -->
            <!--
            <p>
                <a href="<?php echo admin_url('admin.php?page=wpsg-admin&view=dashboard'); ?>"
                   class="button button-secondary">
                    ← Back to Dashboard
                </a>
            </p>
            -->

                <div class="wpsg-admin-wrapper">
                    <div class="wpsg-flex" style="gap: 20px; flex-wrap: wrap;">
                        <?php self::render_cards(); ?>
                    </div>

                </div>
            </div>

        </div>
        <?php
    }

    /**
     * Render profile cards based on admin.json → profile-menu
     */
    public static function render_cards() {
        $profile_menu = WPSG_AdminData::get('profile-menu', []);

        if (empty($profile_menu)) {
            echo '<p><em>No profile categories found.</em></p>';
            return;
        }

        foreach ($profile_menu as $key => $item) {

            if( $item['view']===true ){

                $title = $item['title'] ?? ucfirst($key);

                $link = admin_url(
                    'admin.php?page=wpsg-admin&view=profile&tab=' . urlencode($key)
                );
                ?>
                
                <a class="wpsg-card-link" href="<?php echo esc_url($link); ?>">
                    <div class="wpsg-card">
                        <h3><?php echo esc_html($title); ?></h3>
                        <p><?php echo esc_html('Manage ' . strtolower($title) . ' settings'); ?></p>
                    </div>
                </a>

                <?php
            }
        }
    }

}
