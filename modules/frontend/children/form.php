<?php
/**
 * modules/frontend/settings/main.php
 */

if( !fe_check_default_requirement() ){
    wp_redirect('/app');
}

$father_id = 0;
$mother_id = 0;
$person_id = 0;
if( isset( $_GET['cid'] ) && $_GET['cid'] != '' ){
    $person_id = wpsg_decrypt($_GET['cid']) * 1;
}

$user   = wp_get_current_user();
$p_user = get_person_by_user_id( $user->ID );

// $person_service   = new WPSG_PersonsService();
$children_service = new WPSG_ChildrenService();
$relations        = new WPSG_PersonRelationsRepository();

$can_edit_data = ( ($user && $user->roles && ($user->roles[0] != 'subscriber')) || ( $p_user && !in_array( $p_user['role'], ['guardian','child'] ) ) );
if( $can_edit_data ){
    $read_or_write = '';
} else {
    $read_or_write = ' readonly="readonly"';
}

$child_fields = [
    'id'          => 0,
    'name'        => '',
    'nickname'    => '',
    'status'      => 'active',
    'birth_place' => '',
    'birth_date'  => current_time( 'Y-m-d' ),
    'gender'      => '',
    'blood_type'  => '',
    'address'     => '',
    'city'        => '',
    'province'    => '',
];
$data = $child_fields;
$guardian_fields = [
    'id'          => 0,
    'user_id'     => null,
    'name'        => '',
    'nickname'    => '',
    'email'       => '',
    'phone'       => '',
    'status'      => 'active',
    'birth_place' => '',
    'birth_date'  => '',
    'gender'      => '',
    'blood_type'  => '',
    'occupation'  => '',
    'company'     => '',
    'worktime'    => '',
    'phone_office'=> '',
    'address_office' => '',  
    'city_office'    => '',
    'province_office'=> '',
];
$father = [];
$mother = [];
foreach( $guardian_fields as $key => $val ){
    $father[$key] = $val;
    $mother[$key] = $val;
}

$parents   = [];
$parents['father'] = $guardian_fields;
$parents['mother'] = $guardian_fields;
if( $person_id != 0 ){
    $person = $children_service->get_child( absint($person_id) );
    if( $person ){
        foreach( $person as $key=>$val ){
            // if( isset( $person[$key] ) ){
                $data[$key] = $person[$key];
            // }
        }
        // $data['id']          = $person['id'] ?? 0;
        // $data['name']        = $person['name'] ?? '';
        // $data['nickname']    = $person['nickname'] ?? '';
        // $data['status']      = $person['status'] ?? 'active';
        // $data['birth_place'] = $person['birth_place'] ?? '';
        // $data['birth_date']  = $person['birth_date'] ?? '';
        // $data['gender']      = $person['gender'] ?? '';
        // $data['blood_type']  = $person['blood_type'] ?? '';
        // $data['address']     = $person['address'] ?? '';
        // $data['roles']       = $person['roles'] ?? [];
    }

    if( isset( $person['parents'] ) ){
        //
        if( isset( $person['parents']['father'] ) ){
            foreach( $father as $key => $val ){
                if( isset( $person['parents']['father'][$key] ) ){
                    $father[$key] = $person['parents']['father'][$key];
                }
            }
        }
        if( isset( $person['parents']['mother'] ) ){
            foreach( $mother as $key => $val ){
                if( isset( $person['parents']['mother'][$key] ) ){
                    $mother[$key] = $person['parents']['mother'][$key];
                }
            }
        }
        $parents['father'] = $father;
        $parents['mother'] = $mother;
        //
    }

}

$guardians = [];
$guardians_idx = [];
$guardians_all = $children_service->get_guardians();
// $init_parents  = $relations->get_relations_of_person( $person_id );
foreach( $guardians_all as $guardian ){
    $init_person_id = $guardian['id'];
    $gender = $guardian['gender'];
    if( !isset( $guardians[$gender] ) ){
        $guardians[$gender] = [];
    }
    $guardians[$gender][] = $guardian;
    $guardians_idx[$init_person_id] = $guardian;
}

/*
echo '<xmp>';
print_r( $person );
echo '</xmp><xmp>';
print_r( $data );
echo '</xmp><xmp>';
print_r( $guardians );
echo '</xmp><xmp>';
print_r( $parents );
echo '</xmp>';
/* */

$url_upper = esc_url( remove_query_arg( ['act','id','cid'] ) );
$parent_block = $person_id == 0 ? 'd-none' : 'd-block';

?><div class="form">
    <div class="container">
        <div class="row mb-3 px-2">
            <div class="col-10 col-sm-8 text-start">
                <div class="wpsg-page-header">
                    <h3 class="wpsg-page-title">
                        <?php echo ($person_id==0) ? 'Edit Data' : 'Tambah Data'; ?>
                    </h3>
                </div>
            </div>
            <div class="col-2 col-sm-4 text-end">
                <a class="btn btn-process" href="<?php echo $url_upper; ?>">
                    <i class="fa fa-reply fa-fw"></i>
                    <span class="d-none d-sm-inline">Kembali</span>                            
                </a>
            </div>
        </div>

        <div class="d-none">
            <input type="hidden" id="person_id" name="person_id" value="<?php echo $person_id; ?>"/>
            <input type="hidden" id="father_id" name="father_id" value="<?php echo $father_id; ?>"/>
            <input type="hidden" id="mother_id" name="mother_id" value="<?php echo $mother_id; ?>"/>
        </div>

        <div id="search-form-dialog" class="search-form-dialog" style="display:none;">
            <div id="search-form-content" class="wpsg-dialog-form search-form-content container">
                <div id="search-form-content-header" class="row fw-bold mb-2">
                    <div class="col-12 text-left" id="search-form-content-title"></div>
                </div>
                <div id="search-form-content-filter" class="row mb-2">
                    <div class="col-12">
                        <div class="input-group">
                            <span class="input-group-text" data-process="btn-search"><i class="fa fa-search"></i></span>
                            <input type="text" class="form-control" id="search-form-text-input" placeholder="Text filter"/>
                            <span class="input-group-text btn btn-process" data-process="btn-clear-filter"><i class="fa fa-times"></i></span>
                        </div>
                    </div>
                </div>
                <div class="row mt-3 mb-2">
                    <div class="col-12">
                        <div class="container wpsg-page-content wpsg-grid-border wpsg-grid-hover" id="search-form-content-body" style="height: 320px; overflow-y: scroll;"></div>
                    </div>
                </div>
                <div id="search-form-content-footer" class="row mb-2"></div>
            </div>
        </div>

        <style>
            .search-form-dialog {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 9999;
            }
            .search-form-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                /* background-color: #fff; */
                padding: 20px;
                border-radius: 5px;
                box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
            }
        </style>
        <script type="text/javascript" lang="javascript">
            class person_search_form {

                constructor( options={} ){
                    this.controller = new AbortController();
                    this.options = options;
                    this.options.on_row_click = ((e)=>{ this.row_click(e) });
                    this.choice  = null;
                    if( typeof(this.options.choice)!='undefined' ){
                        this.choice = this.options.choice;
                    }
                    // this.create();
                    this.init();
                }

                /*
                create(){
                    let form_dialog_content = document.createElement( 'div' );
                    let form_header = document.createElement( 'div' );
                    let form_filter = document.createElement( 'div' );
                    let form_body   = document.createElement( 'div' );
                    let form_footer = document.createElement( 'div' );
                    //
                    form_dialog_content.setAttribute( 'id', 'search-form-content' );
                    form_dialog_content.className = 'wpsg-dialog-form search-form-content container';
                    //
                    form_header.setAttribute( 'id', 'search-form-content-header' );
                    form_header.className = 'row fw-bold mb-2';
                        let form_header_title = document.createElement( 'div' );
                        form_header_title.setAttribute( 'id', 'search-form-content-title' );
                        form_header_title.className = 'col-12 text-left';
                        form_header.appendChild( form_header_title );
                    //
                    form_filter.setAttribute( 'id', 'search-form-content-filter' );
                    form_filter.className = 'row';
                        let form_filter_col = document.createElement( 'div' );
                        form_filter_col.className = 'col-12';
                            let form_filter_input = document.createElement( 'input' );
                            form_filter_input.setAttribute( 'id', 'search-form-text-input' );
                            form_filter_input.className = 'form-control';
                            form_filter_input.setAttribute( 'type', 'text' );
                            form_filter_input.setAttribute( 'placeholder', 'Text filter' );
                            form_filter_col.appendChild( form_filter_input );
                        form_filter.appendChild( form_filter_col );
                    //
                    form_body.setAttribute( 'id', 'search-form-content-body' );
                    form_body.className = 'row wpsg-page-content wpsg-grid-border wpsg-grid-hover';
                    form_body.style.height = '320px';
                    form_body.style.overflowY = 'scroll';
                    //
                    form_footer.setAttribute( 'id', 'search-form-content-footer' );
                    form_footer.className = 'row';
                    //
                    let form_dialog = document.createElement( 'div' );
                    form_dialog.setAttribute( 'id', 'search-form-dialog' );
                    form_dialog.className = 'search-form-dialog';
                    //
                    form_dialog_content.appendChild( form_header );
                    form_dialog_content.appendChild( form_filter );
                    form_dialog_content.appendChild( form_body );
                    form_dialog_content.appendChild( form_footer );
                    //
                    this.main_form = document.createElement( 'div' );
                    this.main_form.setAttribute( 'id', 'search-form-dialog' );
                    this.main_form.className = 'search-form-dialog';
                    this.main_form.style.display = 'none';
                    document.body.appendChild( form_dialog );
                }
                */

                destroy(){
                    if( typeof( this.main_form )!='undefined' ){
                        clearSubElements(this.main_form);
                        this.main_form.remove();
                    }
                    this.controller.abort();
                }

                generate_button(opt){
                    let elm_btn = document.createElement( 'button' );
                    let elm_ico = document.createElement( 'i' );
                    let elm_txt = document.createElement( 'span' );
                    elm_btn.setAttribute( 'type', 'button' );
                    elm_btn.setAttribute( 'id', opt.id );
                    elm_btn.setAttribute( 'class', opt.className );
                    elm_ico.setAttribute( 'class', opt.icon );
                    elm_txt.setAttribute( 'class', 'd-none d-sm-inline' );
                    elm_txt.innerHTML = opt.text;
                    elm_btn.appendChild( elm_ico );
                    elm_btn.appendChild( elm_txt );
                    if( opt.onclick ){
                        if( typeof( opt.onclick )=='function' ){
                            elm_btn.addEventListener('click',opt.onclick);
                        }                        
                    }
                    return elm_btn;
                }

                generate_footer(){
                    let dlg_footer = document.getElementById( 'search-form-content-footer' );
                    clearSubElements(dlg_footer);
                    let div_row = document.createElement('div');
                    let div_col = document.createElement('div');
                    div_row.className = 'row g-2';
                    div_col.className = 'col-12 gap-2 text-end';
                    div_col.appendChild(this.generate_button({
                        id: 'btn-search-form-cancel',
                        className: 'btn btn-cancel ms-1',
                        icon: 'fa fa-times fa-fw',
                        text: 'Batal',
                        onclick: ()=>{ this.hide(); }
                    }));
                    div_col.appendChild(this.generate_button({
                        id: 'btn-search-form-accept',
                        className: 'btn btn-submit ms-1',
                        icon: 'fa fa-check fa-fw',
                        text: 'Pilih',
                        onclick: ()=>{ this.submit(); }
                    }));
                    div_row.appendChild(div_col);
                    dlg_footer.appendChild(div_row);
                }

                row_hide(row){ row.classList.add('d-none'); }
                row_show(row){ row.classList.remove('d-none'); }
                set_selected_row(){
                    let div_area = document.getElementById( 'search-form-content-body' );
                    let sub_area = div_area.querySelectorAll('[data_id]');
                    console.log(sub_area);
                    sub_area.forEach((obj)=>{
                        console.log( obj.classList );
                        // let classList = Object.values(obj.classList);
                        // console.log( typeof( classList ) );
                        if( Object.values(obj.classList).includes('wpsg-grid-data-active') ){
                            obj.classList.remove('wpsg-grid-data-active');
                        }
                        if( obj.getAttribute('data_id')==this.choice ){
                            obj.classList.add('wpsg-grid-data-active');
                            console.log( 'in' );
                        } else {
                            console.log( 'out' );
                        }
                    });
                    console.log(sub_area );
                    console.log( this.choice );
                }
                row_click(e){
                    let div_row = e.target.parentNode;
                    // e.target.closest('.wpsg-grid-data').getAttribute('data_id');
                    this.choice = 1 * e.target.closest('.wpsg-grid-data').getAttribute('data_id');
                    this.set_selected_row();
                    // console.log(e.target.parentNode);
                }
                render_one_row(item){
                    // console.log(item);
                    // let div_area = document.getElementById( 'search-form-content-body' );
                    let div_row = document.createElement('div');
                    div_row.setAttribute('name','row_data');
                    div_row.setAttribute('data_id',item.id);
                    div_row.className = 'row mb-1 py-1 px-3 wpsg-row-data wpsg-grid-data';
                    if( this.choice == item.id ){
                        div_row.className = 'row mb-1 py-1 px-3 wpsg-row-data wpsg-grid-data wpsg-grid-data-active';
                    }
                    let div_col_1 = document.createElement('div');
                    let div_col_2 = document.createElement('div');
                    let div_col_3 = document.createElement('div');
                    div_col_1.className = 'col-12 col-sm-12 col-md-6';
                    div_col_2.className = 'col-12 col-sm-6 col-md-3';
                    div_col_3.className = 'col-12 col-sm-6 col-md-3';
                    div_col_1.innerHTML = item.name;
                    div_col_2.innerHTML = item.email;
                    div_col_3.innerHTML = item.phone;
                    div_row.appendChild(div_col_1);
                    div_row.appendChild(div_col_2);
                    div_row.appendChild(div_col_3);
                    return div_row;
                    // div_area.appendChild(div_row);
                }

                render(){
                    let div_area = document.getElementById( 'search-form-content-body' );
                    clearSubElements(div_area);
                    if( this.options ){
                        let options = this.options;
                        if( options.data ){
                            options.data.forEach( (item) => {
                                let div_row = this.render_one_row(item);
                                if( options.on_row_click != undefined ){
                                    if( typeof( options.on_row_click )=='function'){
                                        div_row.addEventListener('click',options.on_row_click);
                                    }
                                }
                                div_area.appendChild( div_row );
                            })
                            // let search_form_content_body = document.getElementById( 'search-form-content-body' );
                            // search_form_content_body.innerHTML = JSON.stringify( options.data );
                        }
                    }
                }
                show(){
                    let search_form_dialog = document.getElementById( 'search-form-dialog' );
                    let dlg_header = document.getElementById( 'search-form-content-title' );
                    search_form_dialog.style.display = 'block';
                    search_form_dialog.addEventListener('keypress',(e)=>{
                        // e.preventDefault();
                        console.log(e);
                    });
                    dlg_header.innerHTML = this.options.title ?? 'Tidak ada judul';
                    this.generate_footer();
                    this.render();
                }
                hide(){
                    let search_form_dialog = document.getElementById( 'search-form-dialog' );
                    search_form_dialog.style.display = 'none';
                    this.destroy();
                }
                submit(){
                    if( typeof(this.options.callback)=='function' ){
                        this.options.callback(this.choice);
                    }
                    this.hide();
                }
                generate_listener(){
                    let elms = document.querySelectorAll('[data-process]');
                    console.log( elms );
                    elms.forEach((elm)=>{
                        console.log(elm);
                    });
                }
                init(){
                    // this.create();
                    this.show();
                    this.generate_listener();
                }
            }
        </script>

        <div class="row mb-3" id="child_data_area">
            <div class="wpsg-page-content wpsg-form wpsg-boxed col-12 py-2">

                <div class="row mb-2 wpsg-grid-border-bottom py-2 px-3 fw-bold">
                    <div class="col-12">DATA ANAK</div>
                </div>
                <div class="row px-2">
                    <div class="mb-2 col-12 col-sm-12 col-md-6">
                        <label class="form-label">Nama Anak</label>
                        <input type="text" <?php echo $read_or_write; ?>
                            id="name"
                            name="name"
                            class="form-control"
                            required/>
                    </div>
                    <div class="mb-2 col-6 col-sm-6 col-md-3">
                        <label class="form-label">Panggilan</label>
                        <input type="text" <?php echo $read_or_write; ?>
                            id="nickname"
                            name="nickname"
                            class="form-control"
                            required/>
                    </div>
                    <div class="mb-2 col-6 col-sm-6 col-md-3">
                        <label class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="active" <?php selected( $data['status'], 'active' ); ?>>Aktif</option>
                            <option value="incactive" <?php selected( $data['status'], 'inactive' ); ?>>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="row px-2">
                    <div class="mb-2 col-12 col-sm-6 col-md-3">
                        <label class="form-label" for="birth_place">Tempat Lahir</label>
                        <input type="text" <?php echo $read_or_write; ?>
                            id="birth_place"
                            name="birth_place"
                            class="form-control"
                            value="<?php echo esc_attr( $data['birth_place'] ); ?>">
                    </div>
                    <div class="mb-2 col-12 col-sm-6 col-md-3">
                        <label class="form-label" for="birth_date">Tanggal Lahir</label>
                        <input type="date" <?php echo $read_or_write; ?>
                            id="birth_date"
                            name="birth_date"
                            class="form-control"
                            value="<?php echo esc_attr( $data['birth_date'] ); ?>">
                    </div>
                    <div class="mb-2 col-12 col-sm-6 col-md-3">
                        <label class="form-label" for="gender">Jenis Kelamin</label>
                        <select id="gender" name="gender" <?php echo $read_or_write; ?> class="form-select">
                            <option value="">— Pilih —</option>
                            <option value="M" <?php selected( $data['gender'], 'M' ); ?>>Laki-laki</option>
                            <option value="F" <?php selected( $data['gender'], 'F' ); ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-2 col-12 col-sm-6 col-md-3">
                        <label class="form-label">Golongan Darah</label>
                        <select id="blood_type" name="blood_type" <?php echo $read_or_write; ?> class="form-select">
                            <option value="">— Pilih —</option>
                            <option value="O" <?php selected( $data['blood_type'], 'O' ); ?>>O</option>
                            <option value="A" <?php selected( $data['blood_type'], 'A' ); ?>>A</option>
                            <option value="B" <?php selected( $data['blood_type'], 'B' ); ?>>B</option>
                            <option value="AB" <?php selected( $data['blood_type'], 'AB' ); ?>>AB</option>
                        </select>
                    </div>
                </div>

                <div class="row px-2">
                    <div class="mb-2 col-12">
                        <label class="form-label">Alamat</label>
                        <textarea id="address" name="address" <?php echo $read_or_write; ?> class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="row px-2">
                    <div class="mb-2 col-12 col-sm-6">
                        <label class="form-label">Kabupaten/Kota</label>
                        <input type="text" id="city" name="city" <?php echo $read_or_write; ?> class="form-control"/>
                    </div>
                    <div class="mb-2 col-12 col-sm-6">
                        <label class="form-label">Provinsi</label>
                        <input type="text" id="province" name="province" <?php echo $read_or_write; ?> class="form-control"/>
                    </div>
                </div>

                <div class="row mt-3 px-2 fw-bold">
                    <div class="mb-2 col-12 text-center" id="child_process_msg">&nbsp;</div>
                    <div class="mb-2 col-6 text-start">
                        <button type="button" id="button-delete-child" class="btn btn-cancel" disabled>
                            <i class="fa fa-trash-alt fa-fw"></i>
                            <span class="d-none d-sm-inline">Hapus</span>
                        </button>
                    </div>
                    <div class="mb-2 col-6 text-end">
                        <button type="button" id="button-submit-child" class="btn btn-submit" disabled>
                            <i class="fa fa-floppy-disk fa-fw"></i>
                            <span class="d-none d-sm-inline">Simpan</span>
                        </button>
                    </div>
                </div>

            </div>
            <script type="text/javascript" lang="javascript">

                class child_sub_form {
                    //
                    constructor() {
                        this.controller = new AbortController();
                        this.fieldnames = ['person_id','name','nickname','status','birth_place','birth_date','gender','blood_type','address','city','province'];
                        this.chk_fields = ['name','address'];
                        this.blank_data = <?php echo json_encode( $child_fields ); ?>;
                        // this.init();
                    }
                    //
                    show_mesg(msg){
                        let obj_msg = document.getElementById('child_process_msg');
                        obj_msg.innerHTML = msg;
                        setTimeout(() => {
                            obj_msg.innerHTML = '&nbsp;';
                        },2500);
                    }
                    toggle_buttons(bool_val=false){
                        document.getElementById('button-delete-child').disabled = (document.getElementById('person_id').value=='' || document.getElementById('person_id').value==null || document.getElementById('person_id').value==0) ? true : !bool_val;
                        document.getElementById('button-submit-child').disabled = !bool_val;
                    }
                    check_inputs(){
                        let btn_flag = false;
                        this.chk_fields.forEach( (fieldname) => {
                            let elm = document.getElementById( fieldname );
                            if( elm.value.trim()!='' && elm.value!=null ){
                                btn_flag = true;
                            }
                        });
                        this.toggle_buttons( btn_flag );
                    }
                    render( data ) {
                        // let data = <?php echo json_encode( $data ); ?>;
                        document.getElementById('person_id').value = data.id;
                        document.getElementById('name').value = data.name;
                        document.getElementById('nickname').value = data.nickname;
                        document.getElementById('status').value = data.status;
                        document.getElementById('birth_place').value = data.birth_place;
                        document.getElementById('birth_date').value = data.birth_date;
                        document.getElementById('gender').value = data.gender;
                        document.getElementById('blood_type').value = data.blood_type;
                        document.getElementById('address').value = data.address;
                        document.getElementById('city').value = data.city;
                        document.getElementById('province').value = data.province;
                        this.check_inputs();
                    }
                    //
                    person_id_changed(){
                        let obj_person_id = document.getElementById('person_id');
                        let father_data_area = document.getElementById('father_data_area');
                        let mother_data_area = document.getElementById('mother_data_area');
                        let person_id = obj_person_id.value;
                        if( person_id=='0' || person_id=='' ){
                            father_data_area.className = 'row mb-3 d-none';
                            mother_data_area.className = 'row mb-3 d-none';
                        } else {
                            father_data_area.className = 'row mb-3 d-block';
                            mother_data_area.className = 'row mb-3 d-block';
                        }
                    }
                    delete() {
                        if( window.confirm('Apakah anda yakin ingin menghapus data anak?') ){
                            let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                            let person_id = document.getElementById('person_id').value;
                            jQuery.post(ajaxUrl, {
                                action: 'wpsg.fe-children.delete_child',
                                nonce: '<?php echo wp_create_nonce('fe-children.delete_child'); ?>',
                                data: { 'id': person_id }
                            }).then((response) => {
                                if( response.success ){
                                    window.location = '<?php echo $url_upper; ?>';
                                }
                            })
                            // deaktivasi anak
                            // ambil data guardians
                            // hapus hubungan dengan guardians
                            // hapus data ayah, jika data ayah tidak memiliki hubungan dengan anak lain (soft delete)
                            // hapus data ibu, jika data ibu tidak memiliki hubungan dengan anak lain (soft delete)
                            // hapus anak (soft-delete)
                        }
                    }
                    submit() {
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        let child_data = {
                            'name': document.getElementById('name').value,
                            'nickname': document.getElementById('nickname').value,
                            'status': document.getElementById('status').value,
                            'birth_place': document.getElementById('birth_place').value,
                            'birth_date': document.getElementById('birth_date').value,
                            'gender': document.getElementById('gender').value,
                            'blood_type': document.getElementById('blood_type').value,
                            'address': document.getElementById('address').value,
                            'city': document.getElementById('city').value,
                            'province': document.getElementById('province').value
                        };
                        if( document.getElementById('person_id').value > 0 ){
                            child_data['id'] = document.getElementById('person_id').value;
                        }
                        // let init_data = {
                        //     'person': child_data,
                        //     'site_id': <?php echo wpsg_get_network_id(); ?>
                        // }
                        jQuery.post(ajaxUrl, {
                            action: 'wpsg.fe-children.submit_child',
                            nonce: '<?php echo wp_create_nonce('fe-children.submit_child'); ?>',
                            data: child_data
                        }).then((response) => {
                            console.log(response);
                            if( response.success ){
                                let person_id = response.data;
                                document.getElementById('person_id').value = person_id;
                                this.person_id_changed();
                                this.show_mesg('Data berhasil disimpan.');
                            } else {
                                this.show_mesg('Data gagal disimpan.');
                            }
                        });
                        // simpan data anak
                    }
                    destroy(){
                        this.controller.abort();
                    }
                    //
                    register_action(){
                        this.fieldnames.forEach((name) => {
                            document.getElementById(name).addEventListener('keyup',this.check_inputs.bind(this));
                        })
                        document.getElementById('button-delete-child').addEventListener('click',this.delete.bind(this));
                        document.getElementById('button-submit-child').addEventListener('click',this.submit.bind(this));
                    }
                    fetch(id){
                        let ajaxUrl  = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        let child_id = document.getElementById('person_id').value;
                        return jQuery.post(ajaxUrl, {
                            action: 'wpsg.fe-children.fetch_child',
                            nonce: '<?php echo wp_create_nonce('fe-children.fetch_child'); ?>',
                            data: { 'child_id': child_id }
                        }).then((response) => {
                            console.log( response );
                            if( response.success ){
                                if( typeof( response.data.id )=='undefined' ){
                                    let blank_data = this.blank_data;
                                    Object.keys(blank_data).forEach((key) => {
                                        response.data[key] = blank_data[key];
                                    });
                                }
                                let person  = response.data;
                                console.log(person);
                                let parents = person.parents;
                                this.render( person );
                            }
                        })
                    }
                    //
                    init() {
                        console.log('child form ready!');
                        return this.fetch().then(()=>{
                            // console.log(response);
                            this.register_action();
                        });
                        // this.render();
                        // do something
                    }
                }

            </script>
        </div>
        <div class="row mb-3 <?php echo $parent_block; ?>" id="father_data_area">
            <div class="wpsg-page-content wpsg-form wpsg-boxed col-12 py-2">
                <div class="row mb-3 wpsg-grid-border-bottom py-2 px-3 fw-bold">DATA AYAH</div>
                <div class="wpsg-form">

                    <!-- hidden input -->
                    <input type="hidden" id="gender_father" name="gender_father" value="M"/>

                    <!-- Input data -->
                    <div class="row px-2">
                        <div class="mb-2 col-12 col-sm-12 col-md-12 col-lg-5">
                            <label class="form-label" for="name_father">Nama Lengkap</label>
                            <div class="input-group">
                                <input class="form-control" type="text" 
                                    id="name_father" name="name_father" 
                                    list="data_father" 
                                    placeholder="Nama Lengkap Ayah">
                                <span class="input-group-text btn btn-process" id="btn_father_clear" title="Clear data"><i class="fa fa-user-times fa-fw"></i></span>
                                <span class="input-group-text btn btn-process" id="btn_father_search" title="Search data"><i class="fa fa-search fa-fw"></i></span>
                            </div>
                            <input type="hidden" id="id-input-father" name="data_id_father"/>
                            <datalist id="data_father"><?php
                                foreach( $guardians['M'] as $item ){
                                    ?><option data-id="<?php echo $item['id']; ?>" value="<?php echo $item['name']; ?>"><?php
                                }
                            ?></datalist>
                        </div>
                        <div class="mb-2 col-12 col-sm-5 col-md-5 col-lg-3">
                            <label class="form-label" for="email_father">Email</label>
                            <input type="email" id="email_father" name="email_father" class="form-control" value="<?php echo esc_attr( $father['email'] ); ?>" placeholder="daddy@email.com"/>
                        </div>
                        <div class="mb-2 col-8 col-sm-5 col-md-5 col-lg-3">
                            <label class="form-label" for="phone_father">Mobile</label>
                            <input type="tel" id="phone_father" name="phone_father" class="form-control" value="<?php echo esc_attr( $father['phone'] ); ?>" placeholder="08123456789" pattern="[0-9]{3,15}" oninput="this.value = this.value.replace(/(?!^\+)[^0-9-]/g, '');"/>
                        </div>
                        <div class="mb-2 col-4 col-sm-2 col-md-2 col-lg-1">
                            <label class="form-label" for="status_father">Status</label>
                            <span class="form-control">
                                <i class="fa fa-minus fa-fw"></i>
                            </span>
                        </div>
                    </div>

                    <div class="row px-2">
                        <div class="mb-2 col-12 col-sm-12 col-md-5">
                            <label class="form-label" for="birth_place_father">Tempat Lahir</label>
                            <input type="text" id="birth_place_father" name="birth_place_father" class="form-control" value="<?php echo esc_attr( $father['birth_place'] ); ?>" placeholder="Tempat Lahir Ayah"/>
                        </div>
                        <div class="mb-2 col-12 col-sm-6 col-md-4">
                            <label class="form-label" for="birth_date_father">Tanggal Lahir</label>
                            <input type="date" id="birth_date_father" name="birth_date_father" class="form-control" value="<?php echo esc_attr( $father['birth_date'] ); ?>"/>
                        </div>
                        <div class="mb-2 col-12 col-sm-6 col-md-3">
                            <label class="form-label" for="blood_type_father">Gol.Darah</label>
                            <select name="blood_type_father" id="blood_type_father" class="form-select">
                                <option value="-">-</option>
                                <option value="O">O</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="AB">AB</option>
                            </select>
                        </div>
                    </div>

                    <div class="row px-2">
                        <div class="mb-2 col-12 col-sm-6 col-md-4 col-lg-3">
                            <label class="form-label" for="occ_father">Pekerjaan</label>
                            <input type="text" class="form-control" name="occ_father" id="occ_father" value="<?php echo esc_attr( $father['occupation'] ); ?>" placeholder="Pekerjaan"/>
                        </div>
                        <div class="mb-2 col-12 col-sm-6 col-md-8 col-lg-4">
                            <!-- <label class="form-label d-block d-sm-block d-md-none" for="job_father">Inst./Perush.</label> -->
                            <label class="form-label" for="company_father">Nama Instansi/Perusahaan</label>
                            <input type="text" class="form-control" name="company_father" id="company_father" value="<?php echo esc_attr( $father['company'] ); ?>" placeholder="Nama Instansi/Perusahaan"/>
                        </div>
                        <div class="mb-2 col-12 col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label" for="worktime_office_father">Jam Kerja</label>
                            <input type="text" class="form-control text-center" name="worktime_father" id="worktime_father" value="<?php echo esc_attr( $father['worktime'] ); ?>" placeholder="08:00 - 17:00"/>
                        </div>
                        <div class="mb-2 col-12 col-sm-6 col-md-4 col-lg-3">
                            <label class="form-label" for="phone_office_father">Telp.Kantor</label>
                            <input type="text" class="form-control" name="phone_office_father" id="phone_office_father" value="<?php echo esc_attr($father['phone_office']); ?>" pattern="[0-9]{3,15}" placeholder="Telp. kantor" oninput="this.value = this.value.replace(/(?!^\+)[^0-9-]/g, '');"/>
                        </div>
                        <div class="mb-2 col-12">
                            <label class="form-label" for="address_office_father">Alamat Instansi/Perusahaan</label>
                            <textarea class="form-control" name="address_office_father" id="address_office_father" placeholder="Alamat Instansi/Perusahaan"><?php
                                echo esc_attr( $father['address_office'] );
                            ?></textarea>
                        </div>
                        <div class="mb-2 col-12 col-sm-6">
                            <label class="form-label">Kabupaten/Kota</label>
                            <input type="text" id="city_office_father" name="city_office_father" <?php echo $read_or_write; ?> class="form-control"/>
                        </div>
                        <div class="mb-2 col-12 col-sm-6">
                            <label class="form-label">Provinsi</label>
                            <input type="text" id="province_office_father" name="province_office_father" <?php echo $read_or_write; ?> class="form-control"/>
                        </div>

                    </div>

                </div>
                <div class="row mt-3 px-2 fw-bold">
                    <div class="mb-2 col-12 text-center" id="father_process_msg">&nbsp;</div>
                    <div class="mb-2 col-6 text-start">
                        <button type="button" id="button-delete-father" class="btn btn-cancel" disabled>
                            <i class="fa fa-trash-alt fa-fw"></i>
                            <span class="d-none d-sm-inline">Hapus</span>
                        </button>
                    </div>
                    <div class="mb-2 col-6 text-end">
                        <button type="button" id="button-submit-father" class="btn btn-submit" disabled>
                            <i class="fa fa-floppy-disk fa-fw"></i>
                            <span class="d-none d-sm-inline">Simpan</span>
                        </button>
                    </div>
                </div>
            </div>
            <script type="text/javascript" lang="javascript">

                class father_sub_form {
                    constructor() {
                        this.controller = new AbortController();
                        this.fieldnames = ['father_id','name_father','email_father','phone_father','birth_place_father','birth_date_father','gender_father','religion_father','occ_father','company_father','worktime_father','phone_office_father','address_office_father','city_office_father','province_office_father'];
                        this.chk_fields = ['name_father', 'email_father', 'phone_father'];
                        this.init();
                    }
                    show_mesg(msg){
                        let obj_msg = document.getElementById('father_process_msg');
                        obj_msg.innerHTML = msg;
                        setTimeout(() => {
                            obj_msg.innerHTML = '&nbsp;';
                        },2500);
                    }
                    toggle_buttons(flag=false){
                        let elm_father_id = document.getElementById('father_id');
                        let btn_delete = document.getElementById('button-delete-father');
                        let btn_submit = document.getElementById('button-submit-father');
                        btn_delete.disabled = ( elm_father_id.value == '' || elm_father_id.value == '0' ) ? true : !flag;
                        btn_submit.disabled = !flag;
                    }
                    check_inputs(){
                        let btn_flag = true;
                        this.chk_fields.forEach((field)=>{
                            let elm = document.getElementById(field);
                            btn_flag = btn_flag && ( elm.value.trim() != '' && elm.value != null );
                        });
                        this.toggle_buttons(btn_flag);
                    }
                    delete(){
                        if( window.confirm('Apakah anda yakin, Anda ingin menghapus data ayah?') ){
                            let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                            let person_id = document.getElementById('person_id').value;
                            let mother_id = document.getElementById('father_id').value;
                            // hapus hubungan dengan child
                            let init_data = {
                                person_id: person_id,
                                related_person_id: mother_id,
                                relation_type: 'father'
                            };
                            // console.log( init_data );
                            jQuery.post(ajaxUrl, {
                                action: 'wpsg.fe-children.delete_guardian',
                                nonce: '<?php echo wp_create_nonce('fe-children.delete_guardian'); ?>',
                                data: init_data
                            }, (response) => {
                                console.log( response );
                                let obj_msg = document.getElementById('father_process_msg');
                                obj_msg.innerHTML = response.message;
                                setTimeout(() => {
                                    obj_msg.innerHTML = '&nbsp;';
                                },2500);
                            });
                        }
                    }
                    submit(){
                        // simpan data person
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        let init_data = {
                            id: document.getElementById('father_id').value,
                            name: document.getElementById('name_father').value,
                            email: document.getElementById('email_father').value,
                            phone: document.getElementById('phone_father').value,
                            birth_place: document.getElementById('birth_place_father').value,
                            birth_date: document.getElementById('birth_date_father').value,
                            gender: document.getElementById('gender_father').value,
                            blood_type: document.getElementById('blood_type_father').value,
                            address: document.getElementById('address').value,
                            city: document.getElementById('city').value,
                            province: document.getElementById('province').value,
                            occupation: document.getElementById('occ_father').value,
                            company: document.getElementById('company_father').value,
                            worktime: document.getElementById('worktime_father').value,
                            phone_office: document.getElementById('phone_office_father').value,
                            address_office: document.getElementById('address_office_father').value,
                            city_office: document.getElementById('city_office_father').value,
                            province_office: document.getElementById('province_office_father').value
                        };
                        if( document.getElementById('father_id').value != '' ){
                            init_data['id'] = document.getElementById('father_id').value ?? 0;
                        }
                        let submit_data = {
                            person: init_data,
                            role: 'guardian',
                            child_id: document.getElementById('person_id').value,
                            relation_type: 'father'
                        };
                        console.log( submit_data );
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg.fe-children.submit_guardian',
                            nonce : '<?php echo wp_create_nonce('fe-children.submit_guardian'); ?>',
                            data: submit_data,
                        }).then((response)=>{
                            console.log( response );
                            if( response.success ){
                                this.show_mesg('Data ayah berhasil disimpan');
                                this.register_action();
                            } else {
                                this.show_mesg('Data ayah gagal disimpan');
                            }
                        });
                    }
                    render( data ){
                        document.getElementById('father_id').value = data.id ?? 0;
                        document.getElementById('name_father').value = data.name;
                        document.getElementById('email_father').value = data.email;
                        document.getElementById('phone_father').value = data.phone;
                        document.getElementById('birth_place_father').value = data.birth_place;
                        document.getElementById('birth_date_father').value = data.birth_date;
                        document.getElementById('gender_father').value = 'M';
                        document.getElementById('blood_type_father').value = data.blood_type;
                        document.getElementById('occ_father').value = data.occupation;
                        document.getElementById('company_father').value = data.company;
                        document.getElementById('worktime_father').value = data.worktime;
                        document.getElementById('phone_office_father').value = data.phone_office;
                        document.getElementById('address_office_father').value = data.address_office;
                        document.getElementById('city_office_father').value = data.city_office;
                        document.getElementById('province_office_father').value = data.province_office;
                        //
                        this.check_inputs();
                    }
                    clear(){
                        document.getElementById('father_id').value = 0;
                        this.render(guardian_blank);
                    }
                    search(){
                        console.log( guardians );
                        if( this.dlg_search == undefined ){
                            this.dlg_search = new person_search_form({
                                data: guardians['M'],
                                title: 'Cari Data Ayah',
                                key: 'id',
                                list: 'name',
                                choice: document.getElementById('father_id').value,
                                callback: (e)=>{
                                    // console.log('this is result for father');
                                    // console.log(e);
                                    if( e != document. getElementById('father_id').value ){
                                        // console.log( 'harus berubah' );
                                        this.new_id = e;
                                        document. getElementById('father_id').value = e;
                                        this.render( guardians_alloc[e] );
                                        // console.log( guardians_alloc[e] );
                                    }
                                    // console.log( document. getElementById('father_id').value );
                                }
                            });
                        } else {
                            this.dlg_search.show();
                        }
                    }
                    register_action(){
                        this.chk_fields.forEach((fld)=>{
                            let elm = document.getElementById( fld );
                            elm.addEventListener('keyup',this.check_inputs.bind(this));
                        });
                        document.getElementById('btn_father_clear').addEventListener('click',this.clear.bind(this));
                        document.getElementById('btn_father_search').addEventListener('click',this.search.bind(this));
                        document.getElementById('button-delete-father').addEventListener('click',this.delete.bind(this));
                        document.getElementById('button-submit-father').addEventListener('click',this.submit.bind(this));
                    }
                    destroy(){
                        this.controller.abort();
                    }
                    init(){
                        const displayInput = document.getElementById('name_father');
                        const dataList = document.getElementById('data_father');
                        const idInput  = document.getElementById('id-input-father');
                        const data = <?php echo json_encode( $parents['father'] ); ?>;
                        this.old_id = data.id ?? 0;
                        this.new_id = this.old_id;
                        this.render( data );
                        displayInput.addEventListener('input', function() {
                            const selectedOption = Array.from(dataList.options).find(option => option.value === this.value);
                            if (selectedOption) {
                                // Jika cocok dengan pilihan di list, ambil data-id
                                const selectedId = selectedOption.getAttribute('data-id');
                                idInput.value = selectedId;
                                console.log("ID yang dipilih:", selectedId);
                            } else {
                                // Jika user mengetik manual yang tidak ada di list
                                idInput.value = "";
                            }
                        });
                        this.register_action();
                    }
                }

            </script>
        </div>
        <div class="row mb-3 <?php echo $parent_block; ?>" id="mother_data_area">
            <div class="wpsg-page-content wpsg-form wpsg-boxed col-12 py-2">                
                <div class="row mb-3 wpsg-grid-border-bottom py-2 px-3 fw-bold">DATA IBU</div>
                <div class="wpsg-form">

                    <!-- hidden input -->
                    <input type="hidden" id="gender_mother" name="gender_mother" value="F"/>

                    <div class="row px-2">
                        <div class="mb-2 col-12 col-sm-12 col-md-12 col-lg-5">
                            <label class="form-label" for="name_mother">Nama Lengkap</label>
                            <div class="input-group">
                                <input class="form-control" type="text" 
                                    id="name_mother" name="name_mother"
                                    value="<?php echo esc_attr( $mother['name'] ); ?>"
                                    list="data_mother" placeholder="Nama Lengkap Ibu">
                                <span class="input-group-text btn btn-process" id="btn_clear_mother" title="Clear data"><i class="fa fa-user-times fa-fw"></i></span>
                                <span class="input-group-text btn btn-process" id="btn_search_mother" title="Search data"><i class="fa fa-search fa-fw"></i></span>
                            </div>
                            <input type="hidden" id="id-input-mother" name="data_id_mother"/>
                            <datalist id="data_mother"><?php
                                foreach( $guardians['F'] as $item ){
                                    ?><option data-id="<?php echo $item['id']; ?>" value="<?php echo $item['name']; ?>"><?php
                                }
                            ?></datalist>
                        </div>
                        <div class="mb-2 col-12 col-sm-5 col-md-5 col-lg-3">
                            <label class="form-label" for="email_mother">Email</label>
                            <input type="email" id="email_mother" name="email_mother" class="form-control" value="<?php echo esc_attr( $mother['email'] ); ?>" placeholder="mommy@email.com"/>
                        </div>
                        <div class="mb-2 col-8 col-sm-5 col-md-5 col-lg-3">
                            <label class="form-label" for="phone_mother">Mobile</label>
                            <input type="tel" id="phone_mother" name="phone_mother" class="form-control" value="<?php echo esc_attr( $mother['phone'] ); ?>" placeholder="08123456789" pattern="[0-9]{3,15}" oninput="this.value = this.value.replace(/(?!^\+)[^0-9-]/g, '');"/>
                        </div>
                        <div class="mb-2 col-4 col-sm-2 col-md-2 col-lg-1">
                            <label class="form-label" for="status_mother">Status</label>
                            <span class="form-control">
                                <i class="fa fa-minus fa-fw"></i>
                            </span>
                        </div>
                    </div>

                    <div class="row px-2">
                        <div class="mb-2 col-12 col-sm-12 col-md-5">
                            <label class="form-label" for="birth_place_mother">Tempat Lahir</label>
                            <input type="text" id="birth_place_mother" name="birth_place_mother" value="<?php echo esc_attr( $mother['birth_place'] ); ?>" class="form-control" placeholder="Tempat Lahir Ibu"/>
                        </div>
                        <div class="mb-2 col-12 col-sm-6 col-md-4">
                            <label class="form-label" for="birth_date_mother">Tanggal Lahir</label>
                            <input type="date" id="birth_date_mother" name="birth_date_mother" value="<?php echo esc_attr( $mother['birth_date'] ); ?>" class="form-control"/>
                        </div>
                        <div class="mb-2 col-12 col-sm-6 col-md-3">
                            <label class="form-label" for="blood_type_mother">Gol.Darah</label>
                            <select name="blood_type_mother" id="blood_type_mother" class="form-select">
                                <option value="-">-</option>
                                <option value="O">O</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="AB">AB</option>
                            </select>
                        </div>
                    </div>

                    <div class="row px-2">
                        <div class="mb-2 col-12 col-sm-6 col-md-4 col-lg-3">
                            <label class="form-label" for="occ_mother">Pekerjaan</label>
                            <input type="text" class="form-control" name="occ_mother" id="occ_mother" value="<?php echo esc_attr( $mother['occupation'] ); ?>" placeholder="Pekerjaan"/>
                        </div>
                        <div class="mb-2 col-12 col-sm-6 col-md-8 col-lg-4">
                            <!-- <label class="form-label d-block d-sm-block d-md-none" for="job_father">Inst./Perush.</label> -->
                            <label class="form-label" for="company_mother">Nama Instansi/Perusahaan</label>
                            <input type="text" class="form-control" name="company_mother" id="company_mother" value="<?php echo esc_attr( $mother['company'] ); ?>" placeholder="Nama Instansi/Perusahaan"/>
                        </div>
                        <div class="mb-2 col-12 col-sm-6 col-md-4 col-lg-2">
                            <label class="form-label" for="worktime_mother">Jam Kerja</label>
                            <input type="text" class="form-control text-center" name="worktime_mother" id="worktime_mother" value="<?php echo esc_attr( $mother['worktime'] ); ?>" placeholder="08:00 - 17:00"/>
                        </div>
                        <div class="mb-2 col-12 col-sm-6 col-md-4 col-lg-3">
                            <label class="form-label" for="phone_office_mother">Telp.Kantor</label>
                            <input type="text" class="form-control" name="phone_office_mother" id="phone_office_mother" value="<?php echo esc_attr( $mother['phone_office'] ); ?>" pattern="[0-9]{3,15}" placeholder="Telp. kantor" oninput="this.value = this.value.replace(/(?!^\+)[^0-9-]/g, '');"/>
                        </div>
                        <div class="mb-2 col-12">
                            <label class="form-label" for="address_office_mother">Alamat Instansi/Perusahaan</label>
                            <textarea class="form-control" name="address_office_mother" id="address_office_mother" placeholder="Alamat Instansi/Perusahaan"><?php
                                echo esc_attr( $mother['address_office'] );
                            ?></textarea>
                        </div>
                        <div class="mb-2 col-12 col-sm-6">
                            <label class="form-label">Kabupaten/Kota</label>
                            <input type="text" id="city_office_mother" name="city_office_mother" <?php echo $read_or_write; ?> class="form-control"/>
                        </div>
                        <div class="mb-2 col-12 col-sm-6">
                            <label class="form-label">Provinsi</label>
                            <input type="text" id="province_office_mother" name="province_office_mother" <?php echo $read_or_write; ?> class="form-control"/>
                        </div>

                    </div>

                </div>
                <div class="row mt-3 px-2 fw-bold">
                    <div class="mb-2 col-12 text-center" id="mother_process_msg">&nbsp;</div>
                    <div class="mb-2 col-6 text-start">
                        <button type="button" id="button-delete-mother" class="btn btn-cancel" disabled>
                            <i class="fa fa-trash-alt fa-fw"></i>
                            <span class="d-none d-sm-inline">Hapus</span>
                        </button>
                    </div>
                    <div class="mb-2 col-6 text-end">
                        <button type="button" id="button-submit-mother" class="btn btn-submit" disabled>
                            <i class="fa fa-floppy-disk fa-fw"></i>
                            <span class="d-none d-sm-inline">Simpan</span>
                        </button>
                    </div>
                </div>
            </div>
            <script type="text/javascript" lang="javascript">

                class mother_sub_form {
                    constructor() {
                        this.controller = new AbortController();
                        this.fieldnames = [ 'name_mother', 'email_mother', 'phone_mother', 'occ_mother', 'company_mother', 'worktime_mother', 'phone_office_mother', 'address_office_mother', 'city_office_mother', 'province_office_mother' ];
                        this.chk_fields = [ 'name_mother', 'email_mother', 'phone_mother' ];
                        this.init();
                    }
                    show_mesg(msg){
                        let obj_msg = document.getElementById('mother_process_msg');
                        obj_msg.innerHTML = msg;
                        setTimeout(() => {
                            obj_msg.innerHTML = '&nbsp;';
                        },2500);
                    }
                    toggle_buttons(flag=false){
                        let elm_mother_id = document.getElementById('mother_id');
                        let btn_delete = document.getElementById('button-delete-mother');
                        let btn_submit = document.getElementById('button-submit-mother');
                        btn_delete.disabled = ( elm_mother_id.value == '' || elm_mother_id.value == '0' ) ? true : !flag;
                        btn_submit.disabled = !flag;
                    }
                    check_inputs(){
                        let btn_flag = true;
                        this.chk_fields.forEach((field)=>{
                            let elm = document.getElementById(field);
                            btn_flag = btn_flag && ( elm.value.trim() != '' && elm.value != null );
                        });
                        this.toggle_buttons(btn_flag);
                    }
                    delete() {
                        if( window.confirm('Apakah anda yakin ingin menghapus data ibu?') ){
                            let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                            let person_id = document.getElementById('person_id').value;
                            let mother_id = document.getElementById('mother_id').value;
                            // hapus hubungan dengan child
                            let init_data = {
                                person_id: person_id,
                                related_person_id: mother_id,
                                relation_type: 'mother'
                            };
                            // console.log( init_data );
                            /* */
                            jQuery.post(ajaxUrl, {
                                action: 'wpsg.fe-children.delete_guardian',
                                nonce: '<?php echo wp_create_nonce('fe-children.delete_guardian'); ?>',
                                data: init_data
                            }, (response) => {
                                console.log( response );
                                let obj_msg = document.getElementById('mother_process_msg');
                                obj_msg.innerHTML = response.message;
                                setTimeout(() => {
                                    obj_msg.innerHTML = '&nbsp;';
                                },2500);
                            });
                            /* */
                            // hapus data ibu (soft-delete), jika sudah tidak ada hubungan dengan anak yang lain
                        }
                    }
                    submit(){
                        let ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
                        let init_data = {
                            name: document.getElementById('name_mother').value,
                            email: document.getElementById('email_mother').value,
                            phone: document.getElementById('phone_mother').value,
                            birth_place: document.getElementById('birth_place_mother').value,
                            birth_date: document.getElementById('birth_date_mother').value,
                            gender: document.getElementById('gender_mother').value,
                            blood_type: document.getElementById('blood_type_mother').value,
                            address: document.getElementById('address').value,
                            city: document.getElementById('city').value,
                            province: document.getElementById('province').value,
                            occupation: document.getElementById('occ_mother').value,
                            company: document.getElementById('company_mother').value,
                            worktime: document.getElementById('worktime_mother').value,
                            phone_office: document.getElementById('phone_office_mother').value,
                            address_office: document.getElementById('address_office_mother').value,
                            city_office: document.getElementById('city_office_mother').value,
                            province_office: document.getElementById('province_office_mother').value
                        };
                        if( document.getElementById('mother_id').value != '' ){
                            init_data['id'] = document.getElementById('mother_id').value;
                        }
                        let submit_data = {
                            person: init_data,
                            role: 'guardian',
                            child_id: document.getElementById('person_id').value,
                            relation_type: 'mother'
                        };
                        console.log( submit_data );
                        return jQuery.post( ajaxUrl, {
                            action: 'wpsg.fe-children.submit_guardian',
                            nonce : '<?php echo wp_create_nonce('fe-children.submit_guardian'); ?>',
                            data: submit_data,
                        }).then((response)=>{
                            console.log( response );
                            if( response.success ){
                                this.show_mesg('Data ibu berhasil disimpan');
                                this.register_action();
                            } else {
                                this.show_mesg('Data ibu gagal disimpan');
                            }
                        });
                    }
                    render(data){
                        document.getElementById('mother_id').value = data.id ?? 0;
                        document.getElementById('name_mother').value = data.name;
                        document.getElementById('email_mother').value = data.email;
                        document.getElementById('phone_mother').value = data.phone;
                        document.getElementById('birth_place_mother').value = data.birth_place;
                        document.getElementById('birth_date_mother').value = data.birth_date;
                        document.getElementById('gender_mother').value = 'F';
                        document.getElementById('blood_type_mother').value = data.blood_type;
                        document.getElementById('occ_mother').value = data.occupation;
                        document.getElementById('company_mother').value = data.company;
                        document.getElementById('worktime_mother').value = data.worktime;
                        document.getElementById('phone_office_mother').value = data.phone_office;
                        document.getElementById('address_office_mother').value = data.address_office;
                        document.getElementById('city_office_mother').value = data.city_office;
                        document.getElementById('province_office_mother').value = data.province_office;
                        //
                        this.check_inputs();
                    }
                    clear(){
                        document.getElementById('mother_id').value = 0;
                        this.render(guardian_blank);
                    }
                    search(){
                        // console.log( guardians );
                        if( this.dlg_search == undefined ){
                            this.dlg_search = new person_search_form({
                                data: guardians['F'],
                                title: 'Cari Data Ibu',
                                key: 'id',
                                list: 'name',
                                choice: document.getElementById('mother_id').value,
                                callback: (e)=>{
                                    if( e != document. getElementById('mother_id').value ){
                                        this.new_id = e;
                                        document. getElementById('mother_id').value = e;
                                        this.render( guardians_alloc[e] );
                                    }
                                }
                            });
                        } else {
                            this.dlg_search.show();
                        }
                    }
                    register_action(){
                        this.fieldnames.forEach((field)=>{
                            document.getElementById(field).addEventListener('keyup',this.check_inputs.bind(this));
                        });
                        document.getElementById('btn_clear_mother').addEventListener('click',this.clear.bind(this));
                        document.getElementById('btn_search_mother').addEventListener('click',this.search.bind(this));
                        document.getElementById('button-delete-mother').addEventListener('click',this.delete.bind(this));
                        document.getElementById('button-submit-mother').addEventListener('click',this.submit.bind(this));
                    }
                    destroy(){
                        this.controller.abort();
                    }
                    init(){
                        const displayInput = document.getElementById('name_mother');
                        const dataList = document.getElementById('data_mother');
                        const idInput  = document.getElementById('id-input-mother');
                        const data = <?php echo json_encode( $parents['mother'] ); ?>;
                        this.old_id = data.id ?? 0;
                        this.new_id = this.old_id;
                        this.render( data );
                        displayInput.addEventListener('input', function() {
                            const selectedOption = Array.from(dataList.options).find(option => option.value === this.value);
                            if (selectedOption) {
                                // Jika cocok dengan pilihan di list, ambil data-id
                                const selectedId = selectedOption.getAttribute('data-id');
                                idInput.value = selectedId;
                                console.log("ID yang dipilih:", selectedId);
                            } else {
                                // Jika user mengetik manual yang tidak ada di list
                                idInput.value = "";
                            }
                        });
                        this.register_action();
                    }
                }

            </script>
        </div>

        <div class="row mb-3 px-2">
            <div class="col-12 text-end">
                <a class="btn btn-process" href="<?php echo $url_upper; ?>">
                    <i class="fa fa-reply fa-fw"></i>
                    <span class="d-none d-sm-inline">Kembali</span>                            
                </a>
            </div>
        </div>

    </div>

    <script type="text/javascript" lang="javascript">

        var guardians = <?php echo json_encode( $guardians ); ?>;
        var guardians_alloc = <?php echo json_encode( $guardians_idx ); ?>;
        var guardian_blank  = <?php echo json_encode( $guardian_fields ); ?>;

        class main_child_form {
            constructor() {
                this.controller = new AbortController();
                this.init();
            }

            destroy(){
                this.child_form.destroy();
                this.father_form.destroy();
                this.mother_form.destroy();
                this.controller.abort();
            }
            init(){
                this.child_form  = new child_sub_form();
                this.child_form.init().then(()=>{
                    // console.log(test);
                    this.father_form = new father_sub_form();
                    this.mother_form = new mother_sub_form();
                });
            }
        }

        (()=>{
            var main_page = new Object;
            document.addEventListener('DOMContentLoaded',()=>{ 
                main_page = new main_child_form();
            });
        })();

    </script>

</div>