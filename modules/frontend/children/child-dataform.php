<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !isset( $can_edit_data ) ){
    $can_edit_data = false;
}

if( $can_edit_data ){
    $read_or_write = '';
} else {
    $read_or_write = ' readonly="readonly"';
}

?>

                <div class="row mb-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label">Nama Anak</label>
                            <input type="text" <?php echo $read_or_write; ?>
                                name="name"
                                class="form-control"
                                value="<?php echo esc_attr( $data['name'] ); ?>"
                                required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?php selected( $data['status'], 'active' ); ?>>Aktif</option>
                                <option value="incactive" <?php selected( $data['status'], 'inactive' ); ?>>Nonaktif</option>
                            </select>
                        </div>
                </div>

                <div class="row mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label" for="birth_place">Tempat Lahir</label>
                            <input type="text" <?php echo $read_or_write; ?>
                                name="birth_place"
                                class="form-control"
                                value="<?php echo esc_attr( $data['birth_place'] ); ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label" for="birth_date">Tanggal Lahir</label>
                            <input type="date" <?php echo $read_or_write; ?>
                                name="birth_date"
                                class="form-control"
                                value="<?php echo esc_attr( $data['birth_date'] ); ?>">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label" for="gender">Jenis Kelamin</label>
                            <select name="gender" <?php echo $read_or_write; ?> class="form-select">
                                <option value="">— Pilih —</option>
                                <option value="M" <?php selected( $data['gender'], 'M' ); ?>>Laki-laki</option>
                                <option value="F" <?php selected( $data['gender'], 'F' ); ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label">Golongan Darah</label>
                            <select name="blood_type" <?php echo $read_or_write; ?> class="form-select">
                                <option value="">— Pilih —</option>
                                <option value="A" <?php selected( $data['blood_type'], 'A' ); ?>>A</option>
                                <option value="B" <?php selected( $data['blood_type'], 'B' ); ?>>B</option>
                                <option value="AB" <?php selected( $data['blood_type'], 'AB' ); ?>>AB</option>
                                <option value="O" <?php selected( $data['blood_type'], 'O' ); ?>>O</option>
                            </select>
                        </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" <?php echo $read_or_write; ?> class="form-control" rows="3"><?php echo esc_textarea( $data['address'] ?? '' ); ?></textarea>
                        </div>
                    </div>
                </div>
