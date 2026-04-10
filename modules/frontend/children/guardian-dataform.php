<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !isset( $can_edit_data ) ){
    $can_edit_data = false;
}

if( $can_edit_data ){
    $read_or_write = '';
    $select_search = '';
} else {
    $read_or_write = ' readonly="readonly"';
    $select_search = ' d-none';
}

?>
        <form method="post" action="<?php echo admin_url("admin-post.php"); ?>">

            <div class="d-none">
                <input type="hidden" name="sid" id="sid" value="<?php echo esc_attr( $_GET['sid'] ); ?>">
                <input type="hidden" name="cid" id="cid" value="<?php echo esc_attr( $_GET['cid'] ); ?>"/>
                <input type="hidden" name="vid" id="vid" value="<?php echo esc_attr( $code_key ); ?>"/>

                <code id="data-persons"><?php echo json_encode($persons); ?></code>

                <code id="data-father"><?php echo json_encode($guardians['father']); ?></code>
                <code id="data-mother"><?php echo json_encode($guardians['mother']); ?></code>
                <code id="data-guardian"><?php echo json_encode($guardians['guardian']); ?></code>

            </div>

            <input type="hidden" name="action" value="wpsg_save_guardian_as_person_data">
            <input type="hidden" name="sid" value="<?php echo esc_attr($_GET['sid']); ?>">
            <input type="hidden" id="child_id" name="child_id" value="<?php echo esc_attr( $child_id ); ?>"/>

            <?php
                if( $can_edit_data ){
                    wp_nonce_field('wpsg_save_guardian_as_person_data','wpsg_guardian_nonce');
                }
            ?>

            <div class="row">
                <div class="col-12 col-sm-4 col-md-2 mb-3">
                    <label class="form-label">Hubungan</label>
                        <select class="form-control" aria-label="" name="relation_type" id="relation_type">
                            <option value="father" <?php echo $rid==wpsg_encode_keys( [$user_id, 'father'] ) ? 'selected' : ''; ?>>Ayah</option>
                            <option value="mother" <?php echo $rid==wpsg_encode_keys( [$user_id, 'mother'] ) ? 'selected' : ''; ?>>Ibu</option>
                            <option value="guardian" <?php echo $rid==wpsg_encode_keys( [$user_id, 'guardian'] ) ? 'selected' : ''; ?>>Wali</option>
                        </select>
                </div>
                <div class="col-12 col-sm-8 col-md-4 mb-3<?php echo $select_search; ?>">
                    <label class="form-label">Daftar Person</label>
                        <select class="form-control" aria-label="" name="person_id" id="person_id">
                            <option value="">-- Pilih --</option>
                        </select>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-sm-12 col-md-6 mb-3">
                    <label class="form-label" for="name">Nama Lengkap</label>
                    <input class="form-control" <?php echo $read_or_write; ?> name="name" id="name" contenteditable="true"/>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" <?php echo $read_or_write; ?> name="email" class="form-control" id="email"/>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-3">
                    <label class="form-label" for="phone">Phone</label>
                    <input type="tel" <?php echo $read_or_write; ?> name="phone" class="form-control" id="phone"/>
                </div>
                <div class="col-12 col-sm-12 col-md-6 mb-3">
                    <label class="form-label" for="occupation">Pekerjaan</label>
                    <input type="text" <?php echo $read_or_write; ?> name="occupation" class="form-control" id="occupation"/>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-4 mb-3">
                    <label class="form-label" for="birth_place">Tempat Lahir</label>
                    <input type="text" <?php echo $read_or_write; ?> name="birth_place" class="form-control" id="birth_place"/>
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <label class="form-label" for="birth_date">Tanggal Lahir</label>
                    <input type="date" <?php echo $read_or_write; ?> name="birth_date" class="form-control" id="birth_date"/>
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <label class="form-label" for="gender">Gender</label>
                    <select class="form-select" <?php echo $read_or_write; ?> name="gender" id="gender">
                        <option value="">-- unset --</option>
                        <option value="M">Laki-laki</option>
                        <option value="F">Perempuan</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-3">
                    <label class="form-label" for="blood_type">Gol. Darah</label>
                    <select class="form-select" <?php echo $read_or_write; ?> name="blood_type" id="blood_type">
                        <option value="">-- unset --</option>
                        <option value="O">O</option>
                        <option value="A">A</option>
                        <option value="B">AB</option>
                        <option value="AB">AB</option>
                    </select>
                </div>
            </div>

<?php

if( $can_edit_data ){

?>

            <div class="mt-4">
                <div class="row">
                    <div class="col-6 text-start">
                        <button type="button" 
                            class="btn btn-cancel" 
                            id="btn-delete" 
                            data-url="<?php 
                                echo esc_url( remove_query_arg( ['action','act','id'] ) . 
                                    '&act=' . wpsg_encode_keys([$user->ID,'guardian-delete']) ); 
                            ?>">
                            <i class="fas fa-trash-alt fa-fw"></i>
                            <span class="d-none d-md-inline">Hapus</span>
                        </button>
                    </div>
                    <div class="col-6 text-end">
                        <button type="submit" class="btn btn-process">
                            <i class="fas fa-floppy-disk fa-fw"></i>
                            <span class="d-none d-md-inline">Simpan</span>                            
                        </button>
                    </div>
                </div>
            </div>

<?php

}

?>

            <script type="text/javascript" lang="javascript">
                document.addEventListener('DOMContentLoaded', function(){
                    //
                    let sid = document.getElementById('sid').value;
                    let cid = document.getElementById('cid').value;
                    let vid = document.getElementById('vid').value;
                    //
                    if( cid!=vid ){
                        window.alert('something wrong...!!!');
                        window.location = window.origin + '/<?php echo fe_get_app_url(); ?>?sid='+sid;
                    }

                    /*
                    var obj_person = new Object;
                    if( document.getElementById('data-persons') != null ){
                        obj_person = document.getElementById('data-persons');
                    }
                    console.log( obj_person );
                    /* */

                    const relationTypeEl = document.getElementById('relation_type');
                    const childIdEl  = document.getElementById('child_id');
                    const personIdEl = document.getElementById('person_id');
                    const persons    = JSON.parse(document.getElementById('data-persons').textContent);
                    const guardians  = {
                        'father': JSON.parse(document.getElementById('data-father').textContent),
                        'mother': JSON.parse(document.getElementById('data-mother').textContent),
                        'guardian': JSON.parse(document.getElementById('data-guardian').textContent),
                    };
                    console.log( persons   );
                    console.log( guardians );
                    var elm_types = {
                        'name'          : {'type':'input', 'default':'' },
                        'occupation'    : {'type':'input', 'default':'' },
                        'birth_place'   : {'type':'input', 'default':'' },
                        'birth_date'    : {'type':'input', 'default':'' },
                        'gender'        : {'type':'select','default':'', 'options':{'':'-- unset --','M':'Laki-laki','F':'Perempuan'} },
                        'blood_type'    : {'type':'select','default':'', 'options':{'':'-- unset --', 'O':'O', 'A':'A', 'B':'B', 'AB':'AB'}},
                        'email'         : {'type':'input', 'default':'' },
                        'phone'         : {'type':'input', 'default':'' },
                    };
                    const btnDelete = document.getElementById('btn-delete');

                    var populate_data_persons = function(gender=null){
                        personIdEl.innerHTML = '<option value="" selected="true">-- Input Data Baru --</option>';
                        // console.log('populate_data_persons');
                        // console.log( persons );
                        persons.forEach( function(person){
                            if( gender==null ) {
                                const optionEl = document.createElement('option');
                                optionEl.value = person.id;
                                optionEl.textContent = person.name;
                                personIdEl.appendChild( optionEl );
                            } else {
                                if( person.gender==gender ){
                                    const optionEl = document.createElement('option');
                                    optionEl.value = person.id;
                                    optionEl.textContent = person.name;
                                    personIdEl.appendChild( optionEl );
                                }
                            }
                        });
                    }

                    var set_gender_by_relation_type = function(){
                        const selectedType = relationTypeEl.value;
                        const genderEl = document.getElementById('gender');
                        // genderEl.disabled = false;
                        if( ['father','mother'].includes( selectedType ) ) {
                            // console.log('masuk');
                            if( selectedType=='father' ) {
                                genderEl.value = 'M';
                            } else {
                                genderEl.value = 'F';
                            }
                            genderEl.disabled = true;
                        } else {
                            genderEl.disabled = false;
                        }
                    };

                    var populate_data_form = function(person_id=null){
                        const selectedPersonId = personIdEl.value;
                        let selectedPerson = null;
                        if( selectedPersonId && selectedPersonId!='' ){
                            persons.forEach( function(person){
                                if( person.id==selectedPersonId ){
                                    selectedPerson = person;
                                }
                            });
                        }
                        //
                        if( selectedPerson ){
                            for( let key of Object.keys( elm_types ) ){
                                elm_info = elm_types[key];
                                if( elm_info.type=='input' ){
                                    document.getElementById( key ).value = selectedPerson[key] || elm_info.default;
                                } else if( elm_info.type=='select' ){
                                    document.getElementById( key ).value = selectedPerson[key] || elm_info.default;
                                }
                            }
                        } else {
                            for( let key of Object.keys( elm_types ) ){
                                elm_info = elm_types[key];
                                if( elm_info.type=='input' ){
                                    document.getElementById( key ).value = elm_info.default;
                                } else if( elm_info.type=='select' ){
                                    document.getElementById( key ).value = elm_info.default;
                                }
                            };
                        }
                        set_gender_by_relation_type();
                    }

                    var change_relation_type = function(){
                        const selectedType = relationTypeEl.value;
                        const genderEl = document.getElementById('gender');
                        //
                        genderEl.disabled = false;
                        if( ['father','mother'].includes( selectedType ) ) {
                            // console.log('masuk');
                            populate_data_persons( selectedType=='father' ? 'M' : 'F' );
                            //
                            if( selectedType=='father' ) {
                                genderEl.value = 'M';
                            } else {
                                genderEl.value = 'F';
                            }
                            genderEl.disabled = true;
                        } else {
                            populate_data_persons();
                        }
                        //
                        personIdEl.value = '';
                        currentGuardian = guardians[selectedType];
                        console.log( currentGuardian );
                        //
                        if( currentGuardian && currentGuardian!=[] ){
                            // console.log( 'masuk current guardian' );
                            currentData = currentGuardian;
                            personIdEl.value = currentData.related_person_id;
                            populate_data_form( currentData.related_person_id );
                            // document.getElementById('name').value = currentData.name || '';
                            // document.getElementById('gender').value = currentData.gender || '';
                        } else {
                            // console.log( 'tidak ada current guardian' );
                            populate_data_form();
                        }
                        // console.log( relationTypeEl.value );
                    };

                    var delete_guardian = function(){
                        console.log( childIdEl );
                        console.log( personIdEl );
                        console.log( relationTypeEl );
                        if( window.confirm('Are you sure, You want to delete this relation?') ){
                            if( childIdEl.value!=0 && personIdEl.value!=0 ){
                                let data_url = btnDelete.getAttribute('data-url') 
                                             + '&child_id=' + childIdEl.value 
                                             + '&parent_id=' + personIdEl.value 
                                             + '&relation_type=' + relationTypeEl.value;
                                // console.log( data_url );
                                window.location = data_url;
                            } else {
                                window.alert('tidak ada data');
                            }
                        } else {
                            // do nothing
                        }
                    }

                    change_relation_type();

                    personIdEl.addEventListener('change', function(){ populate_data_form(); });
                    relationTypeEl.addEventListener('change', function(){ change_relation_type(); });
                    btnDelete.addEventListener('click', function(){ delete_guardian(); });

                });
            </script>

        </form>
