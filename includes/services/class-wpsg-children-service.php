<?php
if (!defined('ABSPATH')) exit;

class WPSG_ChildrenService {

    protected WPSG_PersonsRepository $persons;
    protected WPSG_SitePersonsRepository $site_persons;
    protected WPSG_PersonRelationsRepository $relation;

    protected $site_id;

    public function __construct(
    ) {
        $this->persons      = new WPSG_PersonsRepository();
        $this->relation     = new WPSG_PersonRelationsRepository();
        $this->site_persons = new WPSG_SitePersonsRepository();

        $this->site_id = get_current_network_id();
    }

    /**
     * create or update person
     */

    public function save_person( array $raw_data, int $person_id = 0, string $role='child' ) : int {

        // $person_id = 0;
        $data = [];

        // 1. sanitize data
        // $excluded_fields = [ 'sid', 'cid', 'vid', 'act', 'action', 'nonce', 'submit', 'wpsg_person_nonce', '_wp_http_referer' ];
        foreach( $raw_data as $key=>$val ){
            // if( !in_array( $key, $excluded_fields ) ){
                if( $key=='person_id' ){
                    $person_id = absint( $val );
                    $data['id'] = $person_id;
                } else {
                    $data[$key] = sanitize_text_field( $val );
                }
            // }
        }

        // 2. simpan ke persons
        $person_id = $this->persons->set($data);
        /*
        if( $person_id > 0 ) {
            $this->persons->update( $person_id, $data );
        } else {
            $person_id = $this->persons->insert( $data );
        }
        */

        if ( ! $person_id ) {
            throw new RuntimeException( 'Gagal menyimpan data.' );
        }

        // 3. auto inject site_id (daycare)
        $site_id = $this->site_id;
        $this->site_persons->ensure_link(
            $site_id,
            $person_id,
            $role
        );

        return $person_id;

    }

    public function save_userdata( array $raw_data, int $person_id = 0 ) : int {

        $data = [];

        // 1. sanitize data
        $excluded_fields = [ 'sid', 'cid', 'vid', 'act', 'action', 'nonce', 'submit', 'wpsg_person_nonce', '_wp_http_referer' ];
        foreach( $raw_data as $key=>$val ){
            if( !in_array( $key, $excluded_fields ) ){
                if( $key=='person_id' ){
                    $person_id = absint( $val );
                    $data['id'] = $person_id;
                } else {
                    $data[$key] = sanitize_text_field( $val );
                }
            }
        }

        return $this->save_person( $data, $person_id, 'guardian' );

    }

    /**
     * Create or update child person
     */
    public function save_child_data( array $raw_data, int $person_id = 0 ) : int {

        $data = [];

        // 1. sanitize data
        $new_data = wpsg_retransform_array( $raw_data, [] );
        $data = $new_data['data'] ?? [];

        // 2. simpan ke persons
        // 3. auto inject site_id (daycare)

        return $this->save_person( $data, $person_id, 'child' );

    }

    public function save_guardian( array $raw_data, int $person_id = 0 ) : int {

        $data = [];

        // 1. sanitize data
        $init_data = wpsg_retransform_array( $raw_data, [] );
        $new_data  = $init_data['data'] ?? [];

        $relation_type = 'father';
        foreach( $new_data as $key => $value ) {
            if( $key=='relation_type' ){
                $relation_type = sanitize_text_field( $value );
                if( $relation_type=='father' ){
                    $data['gender'] = 'M';
                } else if( $relation_type=='mother' ){
                    $data['gender'] = 'F';
                }
            }
            $data[$key] = sanitize_text_field( $value );
        };

        // 2. simpan ke persons
        // 3. auto inject site_id (daycare)
        $person_id = $this->save_person( $data, $person_id, 'guardian' );

        if ( ! $person_id ) {
            throw new RuntimeException( 'Gagal menyimpan data wali.' );
        }

        // 4. relation to child
        // $this->relation->remove_relations_by_type( absint( $raw_data['child_id'] ), $raw_data['relation_type'] );
        $test = $this->relation->get_relations_by_type( $raw_data['child_id'], $relation_type );
        /*
        ?>test<br/><xmp><?php
        print_r( $init_data );
        ?></xmp><xmp><?php
        print_r( $test );
        ?></xmp><br/><?php
        /* */
        if( $test==[] ){
            // echo 'set relation';
            $this->relation->create([
                'person_id' => $raw_data['child_id'],
                'related_person_id' => $raw_data['person_id'],
                'relation_type' => $raw_data['relation_type']
            ]);
            // $rel_id = $this->relation->get_relation_id(
            //     absint( $raw_data['child_id'] ),
            //     absint( $raw_data['person_id'] ),
            //     $raw_data['relation_type']
            // );
            // $this->relation->activate_relation(
            //     absint( $raw_data['child_id'] ),
            //     absint( $raw_data['person_id'] ),
            //     $raw_data['relation_type']
            // );
        } else {
            // do nothing
        }

        return $person_id;

    }

    public function get_children( array $args = [] ): array {
        $user = wp_get_current_user();
        $defaults = [
            'role'   => 'child'
        ];
        $args['site_id'] = $this->site_id;

        /*
        echo '<xmp>';
        print_r($user->roles);
        echo '</xmp>';
        /* */

        foreach( $user->roles as $role ) {
            if( $role=='administrator' ){
                $defaults['site_id'] = $this->site_id;
                break;
            }
        }
        if( in_array( 'administrator', (array) $user->roles ) ){
            // jika administrator, tampilkan semua anak di site
            $args['site_id'] = $this->site_id;
        } else {
            // jika bukan administrator, tampilkan anak yang berelasi dengan user
            $view_all = false;
            $this_person = $this->persons->get_by_user_id( $user->ID );

            /*
            echo 'user person: <br/>';
            echo '<xmp>';
            print_r($this_person);
            echo '</xmp>';
            /* */

            $person_by_site = $this->site_persons->get_sites_by_person( $this_person['id'] );
            $person_roles   = wp_list_pluck( $person_by_site, 'role' );
            foreach( $person_roles as $role ){
                if( $role=='owner' ){
                    $view_all = true;
                } else if( $role=='administrator' ){
                    $view_all = true;
                } else if( $role=='staff' ){
                    $view_all = false;
                } else if( $role=='teacher' ){
                    $view_all = true;
                } else {
                    // others: guardian, child
                    $view_all = false;
                }
            }

            /*
            echo 'user id: ' . $user->ID . '<br/>';
            echo 'person_by_site: <br/>';
            echo '<xmp>';
            print_r($person_by_site);
            echo '</xmp>';
            /* */

            if( $view_all ){
                $args['site_id'] = $this->site_id;
            } else {
                if( in_array( 'guardian', $person_roles ) ){
                    $person = current( wp_filter_object_list( $person_by_site, [ 'role' => 'guardian' ] ) );
                    $args['site_id'] = $this->site_id;
                    $args['related_person_id'] = $person['person_id'];
                } else if( in_array( 'child', $person_roles ) ){
                    $person = current( wp_filter_object_list( $person_by_site, [ 'role' => 'child' ] ) );
                } else {
                    $person = null;
                }

                /*
                echo 'person roles: <br/>';
                echo '<xmp>';
                print_r($person_roles);
                echo '</xmp>';
                echo 'person: <br/>';
                echo '<xmp>';
                print_r($person);
                echo '</xmp>';
                /* */

            }
        }
        /*
        echo 'args: <br/>';
        echo '<xmp>';
        print_r($args);
        echo '</xmp>';
        /* */
        $init_data = $this->_get_full_list( wp_parse_args( $args, $defaults ) );
        return $init_data;
    }

    public function get_guardians( array $args = [] ): array {
        $defaults = [
            'role'   => 'guardian'
        ];
        return $this->_get_full_list( wp_parse_args( $args, $defaults ) );
    }

    public function get_guardians_by_child( int $child_id ): array {
        $guardians = $this->relation->get_related_persons_by_type(
            $child_id,
            'guardian'
        );
        return $guardians;
    }

    protected function _get_full_list( array $args = [] ): array {
        $links = $this->_get_id_list( $args );
        /*
        ?><p>link_ids<xmp><?php
        print_r($links);
        ?></xmp></p><?php
        /* */
        $persons = [];
        foreach ( $links as $link ) {
            $person = $this->persons->get( $link['person_id'] );
            if ( $person ) {
                $persons[] = $person;
            }
        }
        return $persons;
    }
    public function delete_person(int $person_id) {
        return $this->persons->delete( $person_id );
    }

    protected function _get_id_list( array $args = [] ): array {
        // dapatkan semua person_id dari site_persons dengan role 'child'
        $person_by_site  = [];
        $filter_relation = false;
        $site_id = $this->site_id;
        if( isset($args['site_id']) ){
            $site_id = $args['site_id'];
        }
        if( isset($args['related_person_id']) ){
            $related_person_id = $args['related_person_id'];
            $filter_relation = true;
            unset( $args['related_person_id'] );
            $person_by_relations = $this->relation->get_all_by_related_person( $related_person_id );
            $relation_ids = wp_list_pluck( $person_by_relations, 'person_id' );

            /*
            ?><p>person by relations : <?php echo $related_person_id; ?><xmp><?php
            print_r($person_by_relations);
            ?></xmp></p><?php
            /* */

        }
        $temp_person_by_site = $this->site_persons->get_persons_by_site(
            $site_id,
            $args
        );
        if( $filter_relation && isset( $relation_ids ) && is_array( $relation_ids ) && $relation_ids!=[] ){
            foreach( $temp_person_by_site as $person_site ){
                if( in_array( $person_site['person_id'], $relation_ids ) ){
                    $person_by_site[] = $person_site;
                }
            }
        } else {
            $person_by_site = $temp_person_by_site;
        }
        return $person_by_site;
    }

    public function get_related_persons_by_types( int $person_id, array $relation_types ): array {
        return $this->relation->get_related_persons_by_types( $person_id, $relation_types );
    }
    public function delete_relation( int $person_id, int $related_person_id ){

    }

    public function sanitize(array $data){
        // $clean_data = [];
        $person     = [];
        $relation   = [];
        $relate_fields  = ['id','person_id','related_person_id','relation_type','is_active','start_date','end_date'];
        $exclude_fields = ['action','sid','nonce','submit','wpsg_children_nonce','_wp_http_referer','child_id'];
        // $person_fields = ['id','user_id','name','email','slug','status','description'];
        die('sanitize');
        $results = wpsg_retransform_array( 
            $data, [
                'is_key' => true, 
                'key_id' => 'person_id', 
                'relation_key' => $relate_fields, 
                'exclude_keys' => $exclude_fields
            ] 
        );
        /*
        foreach( $data as $key=>$item ){
            if( $key=='person_id' ){
                $person['id'] = $item;
                $relation['person_id'] = $item;
            } else {
                if( !in_array( $key, $exclude_fields ) ){
                    if( in_array( $key, $relate_fields ) && $key!='person_id' ){
                        $relation[$key] = $item;
                    } else {
                        $person[$key] = $item;
                    }
                }
            }
        }
        */
        return [
            'person' => $results['person'] ?? [],
            'relation' => $results['relation'] ?? []
        ];

        /*
        foreach( $data as $key=>$item ){
            if( strpos( $key, 'person_' )===0 ){
                $clean_data['person'][ str_replace('person_','',$key) ] = $item;
            }
            if( strpos( $key, 'relation_' )===0 ){
                $clean_data['relations'][ str_replace('relation_','',$key) ] = $item;
            }
        }
        return $clean_data;
        */
    }

}

?>