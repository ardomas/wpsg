<?php
/**
 * WPSG Activities Module
 *
 * @package WPSG
 * @subpackage Modules/Activities
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPSG_Activities' ) ) :

final class WPSG_Activities extends WPSG_PostContent_Modules {

	protected static $instance;

    protected $data = [
        'id'          => null,
        'title'       => '',
        'description' => '',
        'image'       => '',
    ];

	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

        return self::$instance;
	}

	protected function __construct() {

		$this->module_title = 'Aktivitas';

		parent::__construct();
		$this->init_service();
		do_action( 'wpsg_activities_loaded', $this );
        add_action( 'admin_post_wpsg_save_activity', 'wpsg_handle_save_activity' );
	}

	protected function get_view(): string {
		return 'activities';
	}

	protected function init_service(): void {
		$this->service = new WPSG_ContentsService([
			'post_type' => 'activity',
		]);
	}

    /**
     * Load activity data for edit mode based on request
     */
    protected function load_data_from_request(): void {

        // Only load data when editing
        if ( empty( $_GET['action'] ) || $_GET['action'] !== 'edit' ) {
            return;
        }

        if ( empty( $_GET['id'] ) ) {
            return;
        }

        $id = absint( $_GET['id'] );
        if ( $id <= 0 ) {
            return;
        }

        // Fetch from service
        $activity = $this->get_data( $id );
        if ( ! $activity ) {
            return;
        }

        // Normalize data for form
        $this->data = [
            'id'          => $id,
            'title'       => $activity['main']['title']       ?? '',
            'description' => $activity['meta']['description'] ?? '',
            'image'       => $activity['meta']['image']       ?? '',
        ];
    }

    /**
     * Render the module UI
     */
    public function render(): void {

		$action = $_GET['action'] ?? 'list';

		// echo '<div class="wrap">';

        $this->render_header();
        echo '<hr class="wp-header-end">';

		// STATE RENDER
		if ( $action === 'add' || $action === 'edit' ) {

            add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

            $this->load_data_from_request();
			$this->render_form();

		} else {

			$this->render_list();

		}

		// echo '</div>';
	}

    /**
     * Render the Aktivitas list
     */
	public function render_list() {

        $view_mode = $_GET['view'] ?? 'activities';

        $activities = $this->service->get_list('activity', ['order'=>'published_at']);

        ?>
        <div class="wrap wpsg-view-<?php echo esc_attr( $view_mode ); ?>">

            <?php foreach ( $activities as $item ): ?>

                <?php $activity = $this->get_data( $item->id ); 

                $main_data = (array) $activity['main'];
                $meta_data = $activity['meta'];
                ?>
				<div class="wpsg-activity-item">
                    <div class="wpsg wpsg-boxed">
                        <div class="wpsg-row-full">
                            <div class="wpsg-row">
                                <div class="col-8">
                                    <b><?php echo $main_data['title'] ?? ''; ?></b>
                                </div>
                                <div class="col-4" style="text-align:right;">
                                    Edit | Delete
                                </div>
                            </div>
                            <hr/>
                            <div class="wpsg-row">
                                <div class="col-2">
                                    <img src="<?php echo $meta_data['image'] ?? WPSG_URL . 'assets/images/placeholder.png'; ?>" alt="<?php echo esc_attr( $main_data['title'] ?? 'Activity Image' ); ?>" style="max-width:100%; height:auto;" />
                                </div>
                                <div class="col-10">
                                    <div class="wpsg-row">
                                        <div class="wpsg-row-full">
                                            <p><?php echo $meta_data['description'] ?? ''; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
				</div>

            <?php endforeach; ?>

        </div>

        <?php

	}

    public function enqueue_assets($hook) {

        // optional: batasi hanya di halaman plugin kamu
        if (strpos($hook, 'wpsg') === false) {
            return;
        }

        // Media uploader WP
        wp_enqueue_media();

    }

    /**
     * Render the activity form for add/edit
     */
	public function render_form() {

        $action = esc_url( admin_url( $this->base_url . '&action=save' ) );

        ?>
		<form method="post" action="<?php echo $action; ?>" class="form">

			<?php wp_nonce_field( 'wpsg_save_activity', 'wpsg_nonce' ); ?>

			<input type="hidden" name="action" value="wpsg_save_activity">
			<input type="hidden" name="module" value="activity">

			<div class="wpsg wpsg-boxed">
				<div id="poststuff">
					<div id="post-body" class="metabox-holder">
						<!-- main content -->
						 <div class="post-body-content">

                            <div class="wpsg-form-full">

                                <div class="wpsg-row">
									<div class="col-12">

										<div class="wpsg-form-field">

											<label for="activity_title">Activity Title</label>
											<input type="text" id="activity_title" name="activity[title]" class="regular-text"
												value="<?php echo esc_attr($this->data['title'] ?? ''); ?>" required />

										</div>
									</div>

								</div>

							</div>
							<div class="wpsg-form-full">

								<div class="wpsg-row">
									<div class="col-4">

										<div class="wpsg-form-field">

											<label for="activity_image">Featured Image</label>
											<div id="activity_image_wrapper">
												<?php if (!empty($this->data['image'])): ?>
													<img src="<?php echo esc_url($this->data['image']); ?>" class="preview-image"/>
												<?php endif; ?>
												<input type="hidden" id="activity_image" name="activity[image]" value="<?php echo esc_attr($this->data['image'] ?? ''); ?>" />
											</div>
                                            <button type="button" class="button" id="activity_image_select">Select Image</button>
                                            <button type="button" class="button" id="activity_image_remove">Remove</button>

										</div>
									</div>

									<div class="col-8">

										<div class="wpsg-form-field">

											<label for="activity_description">Description</label>
											<textarea id="activity_description" name="activity[description]" rows="5" class="large-text"><?php echo esc_textarea($this->data['description'] ?? ''); ?></textarea>

										</div>
									</div>

								</div>
							</div>
							<div class="wpsg-form-full">

								<div class="wpsg-row">
								</div>
							</div>

						</div>

					</div>

					<?php submit_button( 'Save Activity' ); ?>

				</div>
			</div>

            <script type="text/javascript" data-wpsg="activity-image-handler">
            jQuery(function ($) {

                let frame;

                $('#activity_image_select').on('click', function (e) {
                    e.preventDefault();

                    if (frame) {
                        frame.open();
                        return;
                    }

                    frame = wp.media({
                        title: 'Select Featured Image',
                        button: {
                            text: 'Use this image'
                        },
                        multiple: false
                    });

                    frame.on('select', function () {

                        const attachment = frame.state().get('selection').first().toJSON();

                        let imageUrl = attachment.url;

                        if (attachment.sizes && attachment.sizes.medium) {
                            imageUrl = attachment.sizes.medium.url;
                        }

                        $('#activity_image').val(imageUrl);


                        const img = $('<img>', {
                            src: attachment.url,
                            class: 'preview-image',
                            css: {
                                maxWidth: '350px',
                                width: '100%',
                                marginBottom: '10px'
                            }
                        });

                        $('#activity_image_wrapper img').remove();
                        $('#activity_image_wrapper').prepend(img);
                    });

                    frame.open();
                });

                $('#activity_image_remove').on('click', function (e) {
                    e.preventDefault();

                    $('#activity_image').val('');
                    $('#activity_image_wrapper img').remove();
                });

            });
            </script>

		</form>

		<?php
	}

    public function wpsg_handle_save_activity() {

        // 1. Capability check
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'You are not allowed to do this.' );
        }

        // 2. Nonce check
        if (
            ! isset( $_POST['wpsg_nonce'] ) ||
            ! wp_verify_nonce( $_POST['wpsg_nonce'], 'wpsg_save_activity' )
        ) {
            wp_die( 'Invalid nonce.' );
        }

        // 3. Validate payload
        if ( empty( $_POST['activity'] ) || ! is_array( $_POST['activity'] ) ) {
            wp_die( 'Invalid form data.' );
        }

        $activity = wp_unslash( $_POST['activity'] );
        // 4. Normalize main data
        $title = sanitize_text_field( $activity['title'] ?? '' );

        if ( $title === '' ) {
            wp_die( 'Activity title is required.' );
        }

        // 5. Init service
        $service = new WPSG_ContentsService();

        // 6. Detect add vs edit
        $id = ! empty( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;

        $init_data = [
            'title' => $title,
            'updated_at' => current_time( 'mysql' ),
        ];

        if( $id==0 ){
            $init_data['site_id'] = get_current_network_id();
            $init_data['author_id'] = get_current_user_id();
            $init_data['status'] = 'published';
            $init_data['created_at'] = current_time( 'mysql' );
            $init_data['published_at'] = current_time( 'mysql' );
        }

        if ( $id > 0 ) {

            // UPDATE
            $service->update( $id, $init_data );

        } else {

            // CREATE
            $id = $service->create( 'activity', $init_data );

        }

        // 7. Save meta fields
        if ( $id ) {

            if ( isset( $activity['description'] ) ) {
                $service->set_meta(
                    $id,
                    'description',
                    wp_kses_post( $activity['description'] )
                );
            }

            if ( isset( $activity['image'] ) ) {
                $service->set_meta(
                    $id,
                    'image',
                    esc_url_raw( $activity['image'] )
                );
            }
        }

        // 8. Redirect back to list
        wp_redirect(
            admin_url( $this->base_url . '&action=list' )
		);

		exit;

	}

}

endif;