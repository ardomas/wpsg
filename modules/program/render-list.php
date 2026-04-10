<?php
/**
 * Program List Block Render
 *
 * @var array $attributes
 */

if ( ! class_exists( 'WPSG_Programs' ) ) {
    return '';
}

$view_mode = $attributes['view_mode'] ?? 'list';
$limit     = (int) ( $attributes['limit'] ?? 5 );

// $module = new WPSG_Programs();

$programs = $this->get_list([
    'limit' => $limit,
]);

if ( empty( $programs ) ) {
    return '';
}

ob_start();
?>
<div class="wpsg-program-list wpsg-view-<?php echo esc_attr( $view_mode ); ?>">
    <?php foreach ( $programs as $program ): ?>
        <div class="wpsg-program-item">
            <h3 class="wpsg-program-title">
                <?php echo esc_html( $program['title'] ?? '' ); ?>
            </h3>

            <?php if ( ! empty( $program['excerpt'] ) ): ?>
                <p class="wpsg-program-excerpt">
                    <?php echo esc_html( $program['excerpt'] ); ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php
return ob_get_clean();
