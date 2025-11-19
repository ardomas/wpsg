<?php
// admin/views/dashboard.php

if ( ! defined('ABSPATH') ) exit;
?>

<div class="wrap wpsg-dashboard">
    <h1>WPSG Dashboard</h1>

    <p>Selamat datang di panel administrasi WPSG.</p>

    <div class="wpsg-cards">

        <a class="wpsg-card" href="<?php echo admin_url('../wpsg-admin/articles'); ?>">
            <h3>Articles</h3>
            <p>Kelola artikel.</p>
        </a>

        <a class="wpsg-card" href="<?php echo admin_url('../wpsg-admin/annuoncement'); ?>">
            <h3>Announcement</h3>
            <p>Kelola pengumuman.</p>
        </a>

        <a class="wpsg-card" href="<?php echo admin_url('../wpsg-admin/profile'); ?>">
            <h3>Profile</h3>
            <p>Edit profil situs.</p>
        </a>

        <a class="wpsg-card" href="#">
            <h3>Social Media</h3>
            <p>Integrasi akun sosial.</p>
        </a>

        <a class="wpsg-card" href="#">
            <h3>Membership</h3>
            <p>Kelola keanggotaan.</p>
        </a>

    </div>
</div>

<style>
.wpsg-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
    margin-top: 20px;
}
.wpsg-card {
    display: block;
    padding: 20px;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.wpsg-card:hover {
    border-color: #2271b1;
    box-shadow: 0 2px 4px rgba(0,0,0,0.10);
}
</style>
