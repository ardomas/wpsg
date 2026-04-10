<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WPSG_ChildrenRESTController extends WP_REST_Controller {

    protected $namespace = 'wpsg/v1';
    protected $rest_base = 'children';

    protected $person;
    protected $person_rel;
    protected $site_person;

    public function __construct() {
        // kosong dulu
        // $this->person      = new WPSG_PersonsService;
        // $this->person_rel  = new WPSG_PersonRelationsService;
        // $this->site_person = new WPSG_SitePersonsRepository;
    }

    /**
     * Register routes
     */
    public function register_routes() {

        // GET /children
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_items' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'create_item' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
        ]);

        // GET /children/{id}, PUT /children/{id}
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>\d+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_item' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_item' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
        ]);
    }

    /* =========================
     * Permissions
     * ========================= */

    public function permissions_check( $request ) {
        /**
         * sementara:
         * - user harus login
         * - nanti bisa diperketat: role staff / guru
         */
        return is_user_logged_in();
    }

    /* =========================
     * Callbacks (belum logic)
     * ========================= */

    public function get_items( WP_REST_Request $request ) {

        $args = [
            'search' => $request->get_param( 'search' ),
            'limit'  => $request->get_param( 'limit' ),
            'offset' => $request->get_param( 'offset' ),
            'status' => $request->get_param( 'status' ),
        ];

        $children = $this->children_service->get_children( $args );

        return rest_ensure_response( $children );
    }

    public function get_item( WP_REST_Request $request ) {

        $id = absint( $request['id'] );

        $child = $this->children_service->get_child( $id );

        if ( ! $child ) {
            return new WP_Error(
                'wpsg_child_not_found',
                'Child not found',
                [ 'status' => 404 ]
            );
        }

        return rest_ensure_response( $child );
    }

    public function create_item( WP_REST_Request $request ) {

        $data = [
            'name'       => sanitize_text_field( $request->get_param( 'name' ) ),
            'birth_date' => $request->get_param( 'birth_date' ),
            'gender'     => $request->get_param( 'gender' ),
            'status'     => $request->get_param( 'status' ) ?: 'active',
        ];

        $result = $this->children_service->create_child( $data );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response([
            'person_id' => $result,
        ]);
    }

    public function update_item( WP_REST_Request $request ) {

        $id = absint( $request['id'] );

        $data = [
            'name'       => sanitize_text_field( $request->get_param( 'name' ) ),
            'birth_date' => $request->get_param( 'birth_date' ),
            'gender'     => $request->get_param( 'gender' ),
            'status'     => $request->get_param( 'status' ),
        ];

        $result = $this->children_service->update_child( $id, $data );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response([ 'success' => true ]);
    }

}
