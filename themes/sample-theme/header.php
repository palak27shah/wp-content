<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Sample_Theme
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'sample-theme' ); ?></a>

	<header id="masthead" class="site-header">
	<div class="grid-container">
			<div class="grid-x">
				<div class="cell small-2">
		<div class="site-branding">
			<?php
			the_custom_logo();
				?>
				</div><!-- .site-branding -->
				</div>
				<div class="cell small-8">
					<nav id="site-navigation" class="main-navigation">
					<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e( 'Primary Menu', 'sampletheme' ); ?></button>
				<?php
				if (has_nav_menu( 'menu-primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'menu-1',
						'menu_id'        => 'primary-menu',
					)
				);
			}
				?>

			<div class="cell small-2">
				<?php echo get_search_form(); ?>
			</div>
				
			</nav>
				</div>
			</div>
		</div>
	</header><!-- #masthead -->
	<div class="site-content grid-container">