<?php

$id = isset( $_GET['id'] ) && !empty( $_GET['id'] ) ? wpsg_decrypt( $_GET['id'] ) : 0;

if( $id == 0  ){
    $title = 'Tambah data indikator';
} else {
    $title = 'Edit data indikator';
}

?>
<div class="container">
    <div class="col-12">
        <div class="d-inline">
            <div class="row">

                    <div class="col-8 col-sm-8 col-md-10 text-start">
                        <h3><?php
                        echo $title;
                        ?></h3>
                    </div>
                    <div class="col-4 col-sm-4 col-md-2 text-end"><?php
                        echo fe_generate_href_button([
                            'url_params'=>[ 'sid' => $_GET['sid'], 's1' => $_GET['s1'] ?? null, 's2' => $_GET['s2'] ?? null ], 
                            'class'=>'btn-process',
                            'text'=>'Kembali (Batal)', 
                            'icon'=>'fas fa-reply fa-fw'
                        ]);
                    ?></div>

            </div>
            <form method="post" action="<?php echo admin_url("admin-post.php"); ?>">

                <div class="row">
                    <div class="container"><?php

                        $can_edit_indicator = true;
                        require __DIR__ . '/indicator-dataform.php';

                    ?></div>
                </div>

            </form>
        </div>
    </div>
</div>
