<?php
/**
 * WPSG Program Module
 *
 * @package WPSG
 * @subpackage Modules/Program
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPSG_Programs' ) ) :

final class WPSG_Programs extends WPSG_PostContent_Modules {

	protected static $instance;
	protected $list_blocks = [];

	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function __construct() {
		$this->module_title = 'Program';
		parent::__construct();
		$this->init_service();
		do_action( 'wpsg_programs_loaded', $this );
	}

	protected function get_view(): string {
		return 'programs';
	}

	protected function init_service(): void {
		$this->service = new WPSG_ContentsService([
			'post_type' => 'program',
		]);
	}

	public function render(): void {

		$action = $_GET['action'] ?? 'list';

		echo '<div class="wrap">';

		$this->render_header();

		echo '<hr class="wp-header-end">';

		// STATE RENDER
		if ( $action === 'add' ) {

			$this->render_form();

		} else {

			$this->render_list();

		}

		echo '</div>';
	}

	public function render_list() {

		ob_start();

		$programs = $this->service->get_list('program');
		?>
		<div class="wpsg-program-list wpsg-view-<?php echo esc_attr( $view_mode ); ?>">
			<?php foreach ( $programs as $program ): ?>
				<div class="wpsg-program-item">
					<h3 class="wpsg-program-title">
						<?php echo esc_html( $program['title'] ?? '' ); ?>
					</h3>

					<?php if ( ! empty( $program['excerpt'] ) ): ?>
						<p class="wpsg-program-excerpt">
							<?php echo esc_html( $program['excerpt'] ); ?>
						</p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php

		return ob_get_clean();

	}

	public function render_form() {

		$action = esc_url( admin_url( 'admin-post.php' ) );

		?>
		<form method="post" action="<?php echo $action; ?>" class="wpsg-program-form">

			<?php wp_nonce_field( 'wpsg_save_program', 'wpsg_nonce' ); ?>

			<input type="hidden" name="action" value="wpsg_save_program">
			<input type="hidden" name="module" value="program">

			<div class="wpsg wpsg-boxed">
				<div id="poststuff">
					<div id="post-body" class="metabox-holder">
						<!-- main content -->
						 <div class="post-body-content">

                            <div class="wpsg-form-full">

                                <div class="wpsg-row">
									<div class="col-12">

										<div class="wpsg-form-field">

											<label for="program_title">Program Title</label>
											<input type="text" id="program_title" name="program[title]" class="regular-text"
												value="<?php echo esc_attr($this->data['title'] ?? ''); ?>" required />

										</div>
									</div>

								</div>

							</div>
							<div class="wpsg-form-full">

								<div class="wpsg-row">
									<div class="col-12">

										<div class="wpsg-form-field">

											<label for="program_description">Description</label>
											<textarea id="program_description" name="program[description]" rows="5" class="large-text"><?php echo esc_textarea($this->data['description'] ?? ''); ?></textarea>

										</div>
									</div>

								</div>
							</div>
							<div class="wpsg-form-full">

								<div class="wpsg-row">
									<div class="col-12">

										<div class="wpsg-form-field">

											<label for="program_image">Featured Image</label>
											<div id="program_image_wrapper">
												<?php if (!empty($this->data['image'])): ?>
													<img src="<?php echo esc_url($this->data['image']); ?>" class="preview-image"/>
												<?php endif; ?>
												<input type="hidden" id="program_image" name="program_image" value="<?php echo esc_attr($this->data['image'] ?? ''); ?>" />
												<button type="button" class="button" id="program_image_select">Select Image</button>
												<button type="button" class="button" id="program_image_remove">Remove</button>
											</div>

										</div>
									</div>

								</div>
							</div>

						</div>

					</div>

					<?php submit_button( 'Save Program' ); ?>

				</div>
			</div>

		</form>

		<?php
	}

}

endif;