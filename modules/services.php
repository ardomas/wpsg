<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WPSG_Services
 *
 * Module UI for Services
 * - List services (uses service->list_services())
 * - Form add/edit service (uses service->save_service())
 * - Delete service (uses service->delete_service())
 *
 * Note:
 * - Service layer is the source of truth; module is thin UI/controller.
 */

class WPSG_Services {

    protected $service;
    protected $page = 'wpsg-admin';
    protected $view = 'services';
    protected $base_url;

    protected $module_title = 'Services';

    public function __construct() {
        $this->service = new WPSG_ContentsService();
        $this->page          = $_GET['page'] ?? null;
        $this->view          = $_GET['view'] ?? null;
        $this->base_url      = 'admin.php?page=wpsg-admin&view=services';
    }

    // Additional methods for handling UI and interactions would go here
    public function render(){
        echo "<h1>" . esc_html( $this->module_title ) . "</h1>";
        // Further rendering logic would be implemented here
    }
}