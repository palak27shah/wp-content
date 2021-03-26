<?php
/**
 * Sample Theme Theme Customizer
 *
 * @package Sample_Theme
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function sample_theme_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'sample_theme_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'sample_theme_customize_partial_blogdescription',
			)
		);
	}

	// $wp_customize->add_panel( 
	// 		'social-media', 
	// 	array(
	// 		'priority'       => 10,
	// 		'capability'     => 'edit_theme_options',
	// 		'theme_supports' => '',
	// 		'title'          => 'Social Media',
	// 		'description'    => 'Add social links',
	// 	) 
	// );

	$wp_customize->add_section(
		'social_media',
		array(
			'title' => 'Facebook Link',
			'capability' => 'edit_theme_options',
			// 'panel' => 'socail_media',
		)
	);

	$wp_customize->add_section( 
		'facebook_url',
		array(
			'default' => '',
			'transport' => 'refresh',
		)
	);

	$wp_customize->add_control(
		'facebook_url',
		array(
			'label' => 'Facebook URL',
			'section' => 'Social Media Section',
			'settings' => 'facebook_url',
		)
	);

	$wp_customize->add_section( 
		'twitter_url',
		array(
			'default' => '',
			'transport' => 'refresh',
		)
	);

	$wp_customize->add_control(
		'twitter_url',
		array(
			'label' => 'Twitter URL',
			'section' => 'Social Media Section',
			'settings' => 'twitter_url',
		)
	);
}

add_action( 'customize_register', 'sample_theme_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function sample_theme_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function sample_theme_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function sample_theme_customize_preview_js() {
	wp_enqueue_script( 'sample-theme-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), _S_VERSION, true );
}
add_action( 'customize_preview_init', 'sample_theme_customize_preview_js' );
