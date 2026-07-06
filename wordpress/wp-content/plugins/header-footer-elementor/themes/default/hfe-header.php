<?php
/**
 * Header file in case of the elementor way
 *
 * @package header-footer-elementor
 * @since 1.2.0
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php do_action( 'wp_body_open' ); ?>
<?php
/**
 * Output a skip-to-main-content link for keyboard and screen reader users.
 *
 * HFE's default theme compatibility replaces the theme's header.php entirely,
 * which drops any skip link the theme rendered there (WCAG 2.1 — 2.4.1 Bypass
 * Blocks). Re-add one here so keyboard navigation is preserved.
 *
 * @since 2.8.8
 */
$hfe_skip_link_target = apply_filters( 'hfe_skip_link_target', '#content' );
$hfe_skip_link_text   = apply_filters( 'hfe_skip_link_text', __( 'Skip to main content', 'header-footer-elementor' ) );

if ( ! empty( $hfe_skip_link_target ) && ! empty( $hfe_skip_link_text ) ) {
	// Self-contained styling so the link is hidden off-screen on every theme and
	// only becomes visible when focused — independent of the theme's own
	// screen-reader-text CSS, which off-list themes may not define.
	?>
	<style id="hfe-skip-link-style">
		.hfe-skip-link{position:absolute;left:-9999px;top:0;z-index:100000;padding:0.75em 1.5em;background:#fff;color:#0073aa;font-size:14px;text-decoration:underline;border-radius:0 0 3px 0;}
		.hfe-skip-link:focus{left:0;outline:2px solid #0073aa;outline-offset:-2px;}
	</style>
	<?php
	printf(
		'<a class="hfe-skip-link" href="%1$s">%2$s</a>',
		esc_url( $hfe_skip_link_target ),
		esc_html( $hfe_skip_link_text )
	);
}
?>
<div id="page" class="hfeed site">

<?php do_action( 'hfe_header' ); ?>
