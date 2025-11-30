# WPSG Membership Module --- Refactor TODO List

*Technical Audit & Cleanup Before Form Development*

## 1. Persons Module (`persons`)

### 1.1 WPSG_PersonsData (wpsg/includes/data/class-wpsg-persons-data.php)

-   [x] Rapikan struktur class.
-   [ ] Normalisasi nama properti agar konsisten (snake_case).
-   [ ] Pastikan semua metode memiliki PHPDoc.
-   [ ] Review logic parsing data --- pastikan tidak ada pemrosesan
    berlebihan.
-   [ ] Pastikan tidak ada kode mati (unused code).
-   [ ] Siapkan struktur untuk validasi dasar (jika diperlukan nanti
    oleh service).

### 1.2 WPSG_PersonsRepository (wpsg/includes/repository/class-wpsg-persons-repository.php)

-   [ ] Periksa konsistensi nama metode (get_list, find, insert, update,
    delete).
-   [ ] Rapikan query dan pemanggilan `$wpdb`.
-   [ ] Pastikan properti tabel didefinisikan dalam satu tempat.
-   [ ] Buat standar response (array/object) agar service mudah
    menggunakannya.
-   [ ] Periksa apakah semua CRUD sudah dipakai oleh MembershipService
    (jika tidak, tandai untuk cleanup).

------------------------------------------------------------------------

## 2. Memberships (Pendukung)

### 2.1 WPSG_MembershipsRepository (wpsg/includes/repository/class-wpsg-memberships-repository.php)

-   [ ] Samakan pola coding dengan PersonsRepository.
-   [ ] Normalisasi seluruh nama kolom & property.
-   [ ] Pastikan mapping DB → object konsisten.
-   [ ] Tambahkan PHPDoc pada setiap method.
-   [ ] Pisahkan fungsi query kompleks ke method khusus.
-   [ ] Pastikan keamanan SQL --- gunakan `$wpdb->prepare()`.

### 2.2 WPSG_MembershipsService (wpsg/includes/services/class-wpsg-memberships-service.php)

-   [ ] Rapikan dependency injection (persons repo, memberships repo).
-   [ ] Pusatkan seluruh business logic membership di sini.
-   [ ] Pastikan tidak ada logic UI di service.
-   [ ] Buat standar error/success response.
-   [ ] Validasi entity (person_id, membership_type, tanggal mulai,
    tanggal selesai).
-   [ ] Cek apakah service sudah memanfaatkan seluruh repository dengan
    benar.

------------------------------------------------------------------------

## 3. Memberships (UI Utama)

### 3.1 WPSG_Memberships (UI list) --- file: `wpsg/modules/memberships.php`

-   [ ] Rapikan struktur class.
-   [ ] Pisahkan fungsi rendering table → gunakan helper.
-   [ ] Gunakan service sepenuhnya, jangan langsung akses repo.
-   [ ] Siapkan jalur menuju modul Add/Edit Form.
-   [ ] Pastikan naming convention standar: `render_list()`,
    `render_actions()`, dll.
-   [ ] Bersihkan kode lama/dadakan yang tidak dipakai lagi.

------------------------------------------------------------------------

## 4. General Cleanup (Linting & Structure)

-   [ ] Semua class harus memakai prefix **WPSG\_** (sudah benar).
-   [ ] Cek duplikasi fungsi di beberapa file.
-   [ ] Standarisasi lokasi file:\
    `data/`, `repository/`, `services/`, `modules/`.
-   [ ] Siapkan struktur `helpers/` jika nanti dibutuhkan.
-   [ ] Cek konsistensi autoload (composer / manual include).
-   [ ] Tambahkan komentar header pada setiap file untuk identitas
    modul.
-   [ ] Rapikan spacing, indentasi, dan blok code.

------------------------------------------------------------------------

## 5. Persiapan Sebelum Masuk ke Form Add/Edit

Checklist pre-condition sebelum masuk form: - \[ \] Repository sudah
bersih dan stabil. - \[ \] Service sudah solid dan tidak bercampur
dengan UI. - \[ \] Validasi dasar (minimal level) sudah dibuat. - \[ \]
Struktur data dipastikan konsisten (person, membership). - \[ \] UI list
sudah memakai service sepenuhnya. - \[ \] Tidak ada naming yang
berubah-ubah (snake_case semuanya).

------------------------------------------------------------------------

Jika semua sudah beres → lanjut ke **pembuatan Form Add/Edit
Membership**.
