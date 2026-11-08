<?php
/**
 * @var list<array{type: string, message: string}> $notices
 */
?>

<?php foreach ( $notices as $notice ) : ?>
    <div class="notice notice-<?= esc_attr( $notice['type'] ?? '' ) ?>">
        <p><?= esc_html( $notice['message'] ?? '' ) ?></p>
    </div>
<?php endforeach ?>
