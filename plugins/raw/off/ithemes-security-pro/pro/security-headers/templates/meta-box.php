<?php
/**
 * @var string $action
 * @var string $x_frame_options_name
 * @var string $x_frame_options
 * @var list<string> $x_frame_options_select_options
 */
?>

    <label>
        <?= esc_html__( 'X-Frame-Options', 'it-l10n-ithemes-security-pro' ) ?>
        <select name="<?= esc_attr( $x_frame_options_name ) ?>" value="<?= esc_attr( $x_frame_options ) ?>">
            <?php foreach ( $x_frame_options_select_options as $option ) : ?>
                <option value="<?= esc_attr( $option ) ?>" <?php selected( $x_frame_options, $option ) ?>>
                    <?= esc_html( $option ) ?>
                </option>
            <?php endforeach ?>
        </select>
    </label>
    <p><?= esc_html__( 'Enabling this setting can block your site from being embedded via iframes on other sites — make sure this is what you intend.', 'it-l10n-ithemes-security-pro' ) ?></p>

<?php
wp_nonce_field( $action, "{$action}_nonce" );
