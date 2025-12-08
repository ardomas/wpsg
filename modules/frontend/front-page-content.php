<?php
/**
 * modules/frontend/front-page-content.php
 * Partial content used by shortcode and optional front-page override.
 */

if (! defined('ABSPATH')) {
    exit;
}

/** @var array $data */
if (! isset($data) || ! is_array($data)) {
    $data = [];
}

$user = isset($data['user']) ? $data['user'] : wp_get_current_user();
$children = isset($data['children']) ? $data['children'] : [];
$activities = isset($data['activities']) ? $data['activities'] : [];
$announcements = isset($data['announcements']) ? $data['announcements'] : [];
?>

<div id="wpsg-front-page" class="wpsg-front-page-wrapper">
    <section class="wpsg-hero">
        <div class="wpsg-container">
            <h1 class="wpsg-title">WPSG — Wonder Pieces in Small Gear</h1>
            <p class="wpsg-subtitle">Sistem manajemen daycare: anak, kehadiran, jadwal, dan laporan.</p>

            <div class="wpsg-ctas">
                <a class="wpsg-btn" href="<?php echo esc_url( home_url('/wpsg/children') ); ?>">Daftar Anak</a>
                <a class="wpsg-btn wpsg-btn-outline" href="<?php echo esc_url( home_url('/wpsg/attendance') ); ?>">Kehadiran</a>
            </div>
        </div>
    </section>

    <section class="wpsg-main">
        <div class="wpsg-container">
            <div class="wpsg-grid">
                <div class="wpsg-panel">
                    <h2>Halo, <?php echo esc_html( $user->display_name ?: $user->user_login ); ?></h2>
                    <p>Ringkasan singkat hari ini:</p>

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

    <section class="wpsg-footer-cta">
        <div class="wpsg-container">
            <p>Butuh petunjuk? <a href="<?php echo esc_url( admin_url('admin.php?page=wpsg-settings') ); ?>">Buka pengaturan WPSG</a></p>
        </div>
    </section>
</div>
