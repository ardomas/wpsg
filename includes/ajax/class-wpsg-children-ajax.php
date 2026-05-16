<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// wpsg/includes/ajax/class-wpsg-galleries-ajax.php

class WPSG_ChildrenAjax {

    public function __construct() {

        // $this->site_id = get_current_network_id();

        /* Get Children List */
        add_action('wp_ajax_wpsg_fetch_children', [$this, 'fetch_children_old']);
        /* ------------------------------------------------ */
        add_action('wp_ajax_wpsg.fe-children.fetch_children', [$this, 'fetch_children']);
        add_action('wp_ajax_nopriv_wpsg.fe-children.fetch_children', [$this, 'fetch_children']);
        /* ------------------------------------------------ */
        add_action('wp_ajax_wpsg.fe-children.fetch_child', [$this, 'fetch_child']);
        add_action('wp_ajax_nopriv_wpsg.fe-children.fetch_child', [$this, 'fetch_child']);
        /* ------------------------------------------------ */
        add_action('wp_ajax_wpsg.fe-children.submit_child', [$this, 'submit_child']);
        add_action('wp_ajax_nopriv_wpsg.fe-children.submit_child', [$this, 'submit_child']);
        /* ------------------------------------------------ */
        add_action('wp_ajax_wpsg.fe-children.submit_guardian', [$this, 'submit_guardian']);
        add_action('wp_ajax_nopriv_wpsg.fe-children.submit_guardian', [$this, 'submit_guardian']);
        /* BE process - use soft delete ------------------- */
        add_action('wp_ajax_wpsg.fe-children.delete_children', [$this, 'delete_children']);
        add_action('wp_ajax_nopriv_wpsg.fe-children.delete_children', [$this, 'delete_children']);
        /* ------------------------------------------------ */

        /* Get Guardians List */
        add_action('wp_ajax_wpsg_fetch_guardians', [$this, 'fetch_guardians_old']);
        add_action('wp_ajax_wpsg.fe-children.fetch_guardians', [$this, 'fetch_guardians']);
        add_action('wp_ajax_nopriv_wpsg.fe-children.fetch_guardians', [$this, 'fetch_guardians']);

        /* Ensure Person Indicator Data */
        /* Data Master */
        /* Data Detail */

        /* Person Indicator */
        /* Master */
        add_action('wp_ajax_wpsg_fetch_person_indicator_master'      , [$this, 'fetch_person_indicator_master']);
        add_action('wp_ajax_wpsg_fetch_master_list_report_by_person' , [$this, 'fetch_master_list_report_by_person']);
        add_action('wp_ajax_wpsg_ensure_person_indicator_data_master', [$this, 'ensure_person_indicator_data_master']);
        add_action('wp_ajax_wpsg_submit_person_indicator_master'     , [$this, 'submit_person_indicator_master']);
        /* Detail */
        add_action('wp_ajax_wpsg_fetch_person_indicator_detail'      , [$this, 'fetch_person_indicator_detail']);
        add_action('wp_ajax_wpsg_fetch_person_indicator_detail_list' , [$this, 'fetch_person_indicator_detail_list']);
        add_action('wp_ajax_wpsg_ensure_person_indicator_data_detail', [$this, 'ensure_person_indicator_data_detail']);
        add_action('wp_ajax_wpsg_submit_person_indicator_detail'     , [$this, 'submit_person_indicator_detail']);
        /* Publish */
        add_action('wp_ajax_wpsg_publish_person_indicator', [$this, 'publish_person_indicator']);
        //

        /* Person Activities */
        /* Master */
        add_action('wp_ajax_wpsg_fetch_person_activities_master'     , [$this, 'fetch_person_activities_master']);
        add_action('wp_ajax_wpsg_fetch_person_activities_list_master', [$this, 'fetch_person_activities_list_master']);
        add_action('wp_ajax_wpsg_submit_person_activity_data_master' , [$this, 'submit_person_activity_data_master']);
        add_action('wp_ajax_wpsg_ensure_person_activity_data_master' , [$this, 'ensure_person_activity_data_master']);
        add_action('wp_ajax_wpsg_delete_person_activity_data_master' , [$this, 'delete_person_activity_data_master']);
        /* Detail */
        add_action('wp_ajax_wpsg_fetch_person_activities_detail'     , [$this, 'fetch_person_activities_detail']);
        add_action('wp_ajax_wpsg_submit_person_activity_data_detail' , [$this, 'submit_person_activity_data_detail']);
        add_action('wp_ajax_wpsg_ensure_person_activity_data_detail' , [$this, 'ensure_person_activity_data_detail']);
        add_action('wp_ajax_wpsg_delete_person_activity_data_detail' , [$this, 'delete_person_activity_data_detail']);
        /* Publish */
        add_action('wp_ajax_wpsg_publish_person_activity', [$this, 'publish_person_activity']);
        //
    }

    private function _fetch_children(){
        $service = new WPSG_ChildrenService();
        $data_list = $service->get_children();
        return $data_list;
    }
    public function fetch_children(){
        check_ajax_referer('fe-children.fetch_children', 'nonce');
        wp_send_json_success( $this->_fetch_children() );
    }
    public function fetch_children_old(){
        check_ajax_referer('fetch_children', 'nonce');
        wp_send_json_success( $this->_fetch_children() );
    }

    private function _fetch_guardians(){
        $service = new WPSG_ChildrenService();
        $data = [];
        if( isset( $_POST['data'] ) ){
            $data = $_POST['data'];
        }
        $data_list = $service->get_guardians( $data );
        return $data_list;
    }
    public function fetch_guardians(){
        check_ajax_referer('fe-children.fetch_guardians', 'nonce');
        $data_list = $this->_fetch_guardians();
        wp_send_json_success($data_list);
    }
    public function fetch_guardians_old(){
        check_ajax_referer('fetch_guardians', 'nonce');
        $data_list = $this->_fetch_guardians();
        wp_send_json_success($data_list);
    }

    public function fetch_child(){
        check_admin_referer('fe-children.fetch_child', 'nonce');
        $data = $_POST['data'];
        $service = new WPSG_ChildrenService();
        $result = $service->get_child( $data['child_id'] );
        wp_send_json_success( $result );
    }

    public function delete_child(){
        check_ajax_referer('fe-children.delete_child', 'nonce');
        $data = $_POST['data'];
        $service = new WPSG_ChildrenService();
        $result = $service->delete_person( $data['id'] );
        wp_send_json_success( $result );
    }

    public function submit_child(){
        check_ajax_referer('fe-children.submit_child', 'nonce');
        $data    = $_POST['data'];
        // $person  = $data['person'];
        // $site_id = $data['site_id'];
        $person_id = 0;
        if( isset($data['id']) ){
            $person_id = $data['id'];
        }
        $service = new WPSG_ChildrenService();
        $result = $service->save_person( $data, $person_id, 'child' );
        wp_send_json_success( $result );
    }

    public function submit_guardian(){
        check_ajax_referer('fe-children.submit_guardian', 'nonce');
        $data   = $_POST['data'];
        $person = $data['person'];
        $role   = $data['role'];
        $child_id      = $data['child_id'];
        $relation_type = $data['relation_type'];
        $person_id = 0;
        if( isset($person['id']) ){
            $person_id = $person['id'];
        } else {
            $person_id = 0;
        }
        $is_success = false;
        $service  = new WPSG_ChildrenService();
        $result_1 = $service->save_person( $person, $person_id, $role );
        $result[1] = $result_1;
        if( $result_1 ){
            $relation = new WPSG_PersonRelationsRepository();
            $result_2 = $relation->ensure_relation( $child_id, $person_id, $relation_type );
            $result[2] = $result_2;
            if( $result_2 ){
                $is_success = true;
            }
        }
        if( $is_success ){
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
        //
        // wp_send_json_success( $result );
        //
    }

    public function ensure_person_indicator_data_master(){
        check_ajax_referer('ensure_person_indicator_data_master', 'nonce' );

        $data = $_POST['data'];

        $service = new WPSG_PersonRecIndicatorsService();
        $person_rec_indicator_id = $service->ensure_data_master( $data );
        wp_send_json_success($person_rec_indicator_id);
    }

    public function ensure_person_indicator_data_detail(){
        check_ajax_referer('ensure_person_indicator_data_detail', 'nonce' );

        $data = $_POST['data'];

        $service = new WPSG_PersonRecIndicatorsService();
        $person_rec_indicator_id = $service->ensure_data_detail( $data );
        wp_send_json_success($person_rec_indicator_id);
    }

    public function fetch_person_indicator_master(){
        check_ajax_referer('fetch_person_indicator_master', 'nonce' );

        $data = $_POST['data'];

        $service = new WPSG_PersonRecIndicatorsRepository();
        $person_rec_data = $service->get( $data['id'] );
        wp_send_json_success($person_rec_data);
    }
    public function fetch_master_list_report_by_person(){
        check_ajax_referer('fetch_master_list_report_by_person', 'nonce' );

        $data = $_POST['data'];

        $service = new WPSG_PersonRecIndicatorsRepository();
        $person_rec_data = [];
        $person_rec_data = $service->get_list( [
            'person_id' => $data['child_id'],
            'date_publish' => 'NOT NULL'
        ]);
        wp_send_json_success( $person_rec_data );
    }

    public function fetch_person_indicator_detail(){
        check_ajax_referer('fetch_person_indicator_detail', 'nonce' );

        $data = $_POST['data'];

        $service = new WPSG_PersonRecIndicatorDetailRepository();
        $person_rec_data = $service->get( $data['id'] );
        wp_send_json_success($person_rec_data);
    }

    public function fetch_person_indicator_detail_list(){
        check_ajax_referer('fetch_person_indicator_detail_list', 'nonce' );

        $data = $_POST['data'];

        $service = new WPSG_PersonRecIndicatorsService();
        $person_rec_data = $service->get_detail_list( $data['id'] );
        wp_send_json_success($person_rec_data);
    }

    public function submit_person_indicator_master(){
        check_ajax_referer('submit_person_indicator_master', 'nonce' ); 

        $data = $_POST['data'];

        $service = new WPSG_PersonRecIndicatorsRepository();
        $service->save( $data );
        $person_rec_data = $service->get( $data['id'] );
        wp_send_json_success($person_rec_data);
    }
    public function submit_person_indicator_detail(){
        check_ajax_referer('submit_person_indicator_detail', 'nonce' ); 

        $data = $_POST['data'];

        $service = new WPSG_PersonRecIndicatorDetailRepository();
        $service->save( $data );
        $person_rec_data = $service->get( $data['id'] );
        wp_send_json_success($person_rec_data);
    }
    public function publish_person_indicator(){
        check_ajax_referer('publish_person_indicator', 'nonce' ); 

        $post = $_POST['data'];
        $data = [
            'id' => $post['id'],
            'date_publish' => ($post['status']=='publish') ? current_time('Y-m-d H:i:s') : NULL
        ];

        $service = new WPSG_PersonRecIndicatorsRepository();
        $service->save( $data );
        $person_rec_data = $service->get( $data['id'] );
        wp_send_json_success($person_rec_data);
    }

    public function fetch_person_activities_master(){
        check_ajax_referer('fetch_person_activities_master', 'nonce' );

        $post = $_POST['data'];

        $service = new WPSG_PersonActivitiesService();
        $person_rec_data = $service->get( $post['id'] );
        wp_send_json_success( $person_rec_data );
    }

    public function fetch_person_activities_list_master(){
        check_ajax_referer('fetch_person_activities_list_master', 'nonce' );

        $post = $_POST['data'];

        $service = new WPSG_PersonActivitiesService();
        $person_rec_data = $service->get_list( $post );
        wp_send_json_success( $person_rec_data );
    }

    public function ensure_person_activity_data_master(){
        check_ajax_referer('ensure_person_activity_data_master', 'nonce');

        $post = $_POST['data'];

        $service = new WPSG_PersonActivitiesService();
        $person_activity_id = $service->ensure_data_master( $post );
        if( $person_activity_id && $post['time_check'] ){
            $this->submit_person_activity_data_master([
                'person_activity_id'=>$person_activity_id[0],
                'time_check'=>$post['time_check']
            ]);
        }
        wp_send_json_success( $person_activity_id );
    }

    public function submit_person_activity_data_master(){
        check_ajax_referer('submit_person_activity_data_master', 'nonce' );

        $post = $_POST['data'];
        if( isset( $post['time_check'] ) ){
            if( $post['time_check']=='' || $post['time_check']==0 || $post['time_check']=='00:00' || $post['time_check']=='00:00:00' ){
                $post['time_check'] = null;
            }
        }
        if( isset( $post['time_leave'] ) ){
            if( $post['time_leave']=='' || $post['time_leave']==0 || $post['time_leave']=='00:00' || $post['time_leave']=='00:00:00' ){
                $post['time_leave'] = null;
            }
        }

        $service = new WPSG_PersonActivitiesService();
        $person_activity_id = $service->save( $post );
        $person_rec_data = $service->get( $person_activity_id );
        wp_send_json_success( $person_rec_data );
        // wp_send_json_success( $post );
    }
    public function delete_person_activity_data_master(){
        check_ajax_referer('delete_person_activity_data_master', 'nonce' );

        $post = $_POST['data'];
        $data = [
            'id' => $post['id'],
            'date_publish' => null,
            'time_check' => null,
            'time_leave' => null
        ];

        $service = new WPSG_PersonActivitiesService();
        $person_activity_id = $service->save( $data );
        wp_send_json_success( $person_activity_id );        
    }
    public function publish_person_activity(){
        check_ajax_referer('publish_person_activity', 'nonce' ); 

        $post = $_POST['data'];
        $data = [
            'id' => $post['id'],
            'date_publish' => ($post['status']=='publish') ? current_time('Y-m-d H:i:s') : NULL
        ];

        $service = new WPSG_PersonActivitiesService();
        $person_activity_id = $service->save( $data );
        wp_send_json_success( $person_activity_id );
    }

    public function fetch_person_activities_detail(){
        check_ajax_referer('fetch_person_activities_detail', 'nonce' );

        $post = $_POST['data'];

        $service = new WPSG_PersonActivitiesService();
        $obj_data = [];
        $raw_data = $service->get_detail_list( $post['person_activity_id'] );
        foreach( $raw_data as $item ){
            $obj_data[$item['daily_activity_id']] = $item;
        }
        wp_send_json_success( $obj_data );
    }
    public function submit_person_activity_data_detail(){
        check_ajax_referer('submit_person_activity_data_detail', 'nonce' ); 

        $data = $_POST['data'];
        $service = new WPSG_PersonActivitiesService();
        // $person_rec_data = $service->update_person_activities_detail( $data );
        // wp_send_json_success($person_rec_data);
        wp_send_json_success( $service->save_detail( $data ) );
    }
    public function ensure_person_activity_data_detail(){
        check_ajax_referer('ensure_person_indicator_data_detail', 'nonce' );

        $data = $_POST['data'];
        $service = new WPSG_PersonActivitiesService();
        $person_activity_detail_id = $service->ensure_data_detail( $data );
        wp_send_json_success($person_activity_detail_id);
    }
    public function delete_person_activity_data_detail(){
        check_ajax_referer('delete_person_activity_data_detail', 'nonce' );

        $data = $_POST['data'];
        $service = new WPSG_PersonActivitiesService();
        $person_activity_detail_id = $service->delete_detail( $data );
        wp_send_json_success($person_activity_detail_id);
    }
}