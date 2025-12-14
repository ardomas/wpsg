<?php
if (!defined('ABSPATH')) {
    exit;
}

function wpsg_shortcode_memberships() {

    $data = new WPSG_MembershipsService();
    $list = $data->list_memberships();

    $num_order = 0;

    ob_start();

    if( $list != [] ) {

        ?><div class="wpsg-short-memberships">
            <!-- <h1>Daftar Anggota PTPAI</h1> -->
            <table class="wpsg-full-width outer-border bordered hover striped" style="width: 100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th style="text-align: left;">Nama</th>
                        <th>Status</th>
                        <th colspan="2">Tanggal</th>
                    </tr>
                </thead>
                <tbody><?php

                foreach( $list as $raw_item ) {
                    $item = $raw_item['membership'];
                    if( $item['site_id'] != '1' ){

                        $num_order++;

                        ?><tr><td style="text-align: right;"><?php 
                            echo $num_order . '.';
                        ?></td><td><?php
                            echo( $item['name'] ); 
                        ?></td><td style="text-align: center;"><?php
                            echo( $item['status'] );
                        ?></td><td style="text-align: center;"><?php
                            echo( mysql2date( 'Y-m-d', $item['start_date'], true ) );
                        ?></td><td style="text-align: center;"><?php
                            echo( mysql2date( 'Y-m-d', $item['end_date'  ], true ) );
                        ?></td></tr><?php

                    }
                }

            ?></tbody>
            </table>
        </div><?php
    }

    return ob_get_clean();

}