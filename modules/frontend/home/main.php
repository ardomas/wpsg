<?php
/**
 * modules/frontend/content.php
 * Partial content used by shortcode and optional front-page override.
 */

if (! defined('ABSPATH')) {
    exit;
}

$x = wpsg_site_abbreviation();

$service_person   = new WPSG_PersonsService();
$service_children = new WPSG_ChildrenService();

$user_name = esc_html( $user->display_name ?: $user->user_login );

$person_base = $service_person->get_by_user_id( $user->ID );
$person_full = [];
if( $person_base!=[] ){
    $person_full = $service_person->get_person( $person_base['id'] );
    switch( strtolower( trim( $person_full['gender'] ) ) ){
        case 'm':
            $user_name = 'Pak';
            break;
        case 'f':
            $user_name = 'Bu';
            break;
        default:
            $user_name = 'Kak';
    }
    $user_name .= ' ' . $person_base['name'];
}

$children = $service_children->get_children();

?>

<section class="wpsg-main">

    <div class="wpsg-panel" style="margin: 10px 0;">
        <div class="wpsg-grid">

            <div class="wpsg-panel">

                <h3>Halo, <?php echo esc_html( $user_name ); ?></h3>
                <p>Ringkasan singkat hari ini:</p>

                <?php if( $person_base!=[] && in_array( 'guardian', $person_full['roles']) ): ?>
                    <h3>Anak Anda</h3>
                    <?php if (!empty($children)): ?>
                        <ul class="wpsg-list">
                            <?php foreach ($children as $c): ?>
                                <li><?php echo esc_html( $c->name ?? $c['name'] ?? '—' ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Tidak ada anak terdaftar untuk akun ini.</p>
                    <?php endif; ?>
                <?php endif; ?>

                <h3>Kegiatan Hari Ini</h3>
                <?php if (!empty($activities)): ?>
                    <ul class="wpsg-list">
                        <?php foreach ($activities as $a): ?>
                            <li><?php echo esc_html( $a->title ?? $a['title'] ?? '—' ); ?> — <?php echo esc_html( $a->time ?? '' ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Tidak ada aktivitas tercatat untuk hari ini.</p>
                <?php endif; ?>
            </div>

            <aside class="wpsg-aside">
                <h3>Pengumuman</h3>
                <?php if (!empty($announcements)): ?>
                    <ul class="wpsg-list">
                        <?php foreach ($announcements as $ann): ?>
                            <li><?php echo esc_html( wp_trim_words( $ann->content ?? $ann['content'] ?? '', 15 ) ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Tidak ada pengumuman.</p>
                <?php endif; ?>
            </aside>

        </div>

    </div>

</section>

<!--
<section class="wpsg-ctas">
    <div class="wpsg-container wpsg-text-center">
        <p>Butuh petunjuk? <a href="<?php echo esc_url( admin_url('admin.php?page=wpsg-settings') ); ?>">Buka pengaturan WPSG</a></p>
    </div>
</section>
-->