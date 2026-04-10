<?php

$id = isset( $_GET['id'] ) && !empty( $_GET['id'] ) ? wpsg_decrypt( $_GET['id'] ) : 0;

if( $id == 0  ){
    $title = 'Tambah data aktivitas';
} else {
    $title = 'Edit data aktivitas';
}

?>
<div class="row">
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
                        'url_params'=>[ 'sid' => $_GET['sid'], 's1' => $_GET['s1'] ?? null ], 
                        'class'=>'btn-process',
                        'text'=>'Kembali (Batal)', 
                        'icon'=>'fas fa-reply fa-fw'
                    ]);
                ?></div>
            </div>
            <div class="form">
                <form method="post" action="<?php echo admin_url("admin-post.php"); ?>">

                    <div class="row my-3"><?php

                        $can_edit_data = true;
                        require __DIR__ . '/activity-dataform.php';

                    ?></div>
                </form>
            </div>
        </div>
    </div>
</div>
