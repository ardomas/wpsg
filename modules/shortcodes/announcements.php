<?php
if (!defined('ABSPATH')) {
    exit;
}

function wpsg_load_active_announcements($atts){
    $posts = [];
    $status    = false;
    $init_data = [];
    $date_0 = Date('Y-m-d');

    $atts = shortcode_atts([
        'post_type' => 'announcement',
        'status'    => 'published',
        'limit'     => 5,
        'site_id'   => null, // biarkan null untuk aturan multisite otomatis
    ], $atts, 'wpsg_announcements');

    // Pastikan class data tersedia
    if (class_exists('WPSG_PostsData')) {
        $data = WPSG_PostsData::get_instance();
        $temp = $data->get_all_posts([
            'post_type' => $atts['post_type'],
            'status'    => $atts['status'],
            'limit'     => intval($atts['limit']),
            'site_id'   => $atts['site_id'],
        ]);
        if( $temp!=[] ){
            foreach( $temp as $temp_data ){
                $temp_meta   = $data->get_meta( $temp_data['id'] );
                $init_data[] = $temp_data;
            }
        }
    }

    return ['status'=>$status, 'data'=>$init_data];

}

function wpsg_load_all_announcements($atts){

}

function wpsg_shortcode_announcements($atts) {

    $date_0 = Date('Y-m-d');

    $atts = shortcode_atts([
        'post_type' => 'announcement',
        'status'    => 'published',
        'limit'     => 5,
        'site_id'   => null, // biarkan null untuk aturan multisite otomatis
    ], $atts, 'wpsg_announcements');

    // Pastikan class data tersedia
    if (!class_exists('WPSG_PostsData')) {
        return '<div class="wpsg-error">WPSG_PostsData not found.</div>';
    }

    $data = WPSG_PostsData::get_instance();

    $posts = $data->get_all_posts([
        'post_type' => $atts['post_type'],
        'status'    => $atts['status'],
        'limit'     => intval($atts['limit']),
        'site_id'   => $atts['site_id'],
    ]);

    if (empty($posts)) {
        return '<div class="wpsg-no-posts">No announcements available.</div>';
    }

    ob_start();
    ?>
    <div class="wpsg-short-announcements">
        <!-- <h1>Pengumuman PTPAI</h1> -->

        <?php if( $posts!=[] ): ?>
            <?php

            $new_lists = [];

            foreach( $posts as $post ):
                $meta = $data->get_meta( $post->id );
                if( $meta['date_end'] > Date('Y-m-d') ){

                    $post->meta = $meta;
                    $new_lists[] = $post;

                }
            endforeach;

            ?>
            <?php if( $new_lists != [] ): ?>
                <?php foreach( $new_lists as $p ): ?>
                    <?php
                        $date_1 = $p->meta['date_start'];
                        $date_2 = $p->meta['date_end'];
                        $time_1 = $p->meta['time_start'];
                        $time_2 = $p->meta['time_end'];
                        $speakers = $p->meta['speakers'] ?? [];
                        $loc_addr = $p->meta['locations']['address'];
                    ?>

                    <div class="wpsg-announcement-item">
                        <div class="wpsg-announcement-content">
                            <table class="wpsg-full-width bordered">
                                <thead>
                                    <tr>
                                        <th colspan="2"><h3><?php echo esc_html($p->title); ?></h3></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if( $speakers!=[] ): ?>
                                        <tr><?php
                                            if( $p->meta['image'] ): ?>
                                                <td style="width: 120px;"><img style="width: 120px;" src="<?php echo $p->meta['image']; ?>"/></td>
                                            <?php endif; ?><td>
                                                <?php foreach( $meta['speakers'] as $item ): ?>
                                                    <div><?php echo $item['name']; ?><br/>
                                                    <?php echo $item['company']; ?> - <?php echo $item['position']; ?></div>
                                                <?php endforeach; ?>
                                                <?php
                                                    echo $p->meta['locations']['city'];
                                                ?><br/><?php echo $date_1; ?>
                                                <?php
                                                    if( !is_null($date_2) && $date_2 != $date_1 ) { echo ' s/d ' . $date_2; }
                                                ?><br/>Jam : <?php echo $time_1; ?> s/d <?php echo $time_2; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="wpsg-announcement-item">
                    <div style="text-align: center;">tidak ada pengumuman aktif</div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="wpsg-announcement-item">
                <div style="text-align: center;">tidak ada pengumuman aktif</div>
            </div>
        <?php endif; ?>

    </div>
    <?php

    return ob_get_clean();
}
