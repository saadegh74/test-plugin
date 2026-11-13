<?php
/**
 * Override Default Footer file
 * 
 * The following file overrides the default footer file to load the Footer in a similar way
 * Elementor does.
 *
 * @since 1.5.1
 */

?>

<?php 
    // Custom action to hook content before footer
    do_action( 'pp_footer_before' );
?>
<?php 
    // Custom action to hook custom footer template to footer.php
    do_action( 'pp_footer' ); 
?>
<?php wp_footer(); ?>

</body>
</html> 