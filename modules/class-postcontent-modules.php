<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPSG_PostContent_Modules
 *
 * Base module for post-based content (program, activity, service, faq, etc).
 *
 * Responsibilities:
 * - Acts as thin UI/controller layer
 * - Delegates data operations to service layer
 * - Provides common admin context (page, view, base_url)
 *
 * Note:
 * - Service layer is the source of truth
 * - Child modules must define rendering & service binding
 */

abstract class WPSG_PostContent_Modules {

	/** @var object */
	protected $service;

	/** @var string */
	protected $page = 'wpsg-admin';

	/** @var string */
	protected $view = '';

	/** @var string */
	protected $base_url = '';

	/** @var string */
	protected $module_title = 'Post Content Modules';

	/**
	 * Protected constructor
	 * Allows singleton & normal instantiation
	 */
	protected function __construct() {
		$this->init_context();
		$this->init_service();
        // add_action( 'init', [ $this, 'boot' ] );
		// $this->boot();
	}

    /**
	 * Initialize basic admin context
	 */
	protected function init_context(): void {
		$this->view     = $this->get_view();
		$this->base_url = 'admin.php?page=' . $this->page . '&view=' . $this->view;
	}

	// public function boot(): void {
	// 	$this->register_blocks();
	// }

	/**
	 * Register all blocks for this module
	 */
	// abstract protected function register_blocks(): void;

    /**
	 * Bind service layer (must be implemented by child)
	 */
	abstract protected function init_service(): void;

	/**
	 * Module view slug (program, activity, etc)
	 */
	abstract protected function get_view(): string;

	/**
	 * Render UI output
	 */
	abstract public function render(): void;

	protected function get_add_url() {
		return add_query_arg(
			[
				'page'   => 'wpsg-admin',
				'view'   => $this->view,
				'action' => 'add',
			],
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Load data from request (for add and edit mode)
	 */
	abstract protected function load_data_from_request(): void;

	/**
	 * Helper: get all data for given ID
	 */
	public function get_data( int $id ): array {

		$main = $this->service->get( $id );
		if ( ! $main ) {
			return [];
		}
		return [
			'main' => $main,
			'meta' => $this->service->get_meta( $id ) ?? [],
		];
	}

	/**
	 * Helper: render module header
	 */
	protected function render_header(): void {

		$action = $_GET['action'] ?? 'list';

		// echo '<div class="wrap">';
		echo '<h2 class="wp-heading-inline">' . esc_html( $this->module_title ) . '</h2>';
		if( $action === 'list' ) {
	        echo '<a href="' . esc_url( $this->get_add_url() ) . '" id="new_post_data" class="button button-primary">Add Item ' . $this->module_title . '</a>';
		} else {
			echo '<a href="' . esc_url( $this->base_url ) . '" id="back_to_list" class="button button-secondary">Back to ' . $this->module_title . ' List</a>';
		}
		// echo '</div>';
	}
}
