<?php
/**
 * Gutenslider Dynamic Render Callback *
 *
 * @since   3.0.0
 * @package Gutenslider
 */

if ( ! function_exists( 'eedee_gutenslider_dynamic_render_callback' ) ) {
	/**
	 * Register Gutenberg block template that is rendered on server side
	 *
	 * @param Array  $attr are the attributes from the block.
	 * @param String $inner_content the content of gutensliders innerBlocks.
	 * @link  https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
	 * @uses  {save} the content returned from the save function of Gutenslider block.
	 * @since 1.16.0
	 */
	function eedee_gutenslider_dynamic_render_callback( $attr, $inner_content ) {

		include 'attributes.php';

		$POSITION_CLASSNAMES = array(
			'top left' => 'is-position-top-left',
			'top center' => 'is-position-top-center',
			'top right' => 'is-position-top-right',
			'center left' => 'is-position-center-left',
			'center center' => 'is-position-center-center',
			'center' => 'is-position-center-center',
			'center right' => 'is-position-center-right',
			'bottom left' => 'is-position-bottom-left',
			'bottom center' => 'is-position-bottom-center',
			'bottom right' => 'is-position-bottom-right',
		);

		$id = '';

		if ( isset( $attr['gsBlockId'] ) ) {
			$id = sprintf(
				'gutenslider-%1$s',
				$attr[ 'gsBlockId' ]
			);
		}

		$class = sprintf(
			'wp-block-eedee-block-gutenslider content-%1$s',
			$attr['contentMode']
		);
		if ( isset( $attr['align'] ) ) {
			$class .= ' align' . $attr['align'];
		}
		if ( isset( $attr['adaptiveHeight'] ) && $attr['adaptiveHeight'] ) {
			$class .= ' adaptive-height';
		}
		if ( isset( $attr['isFullScreen'] ) && $attr['isFullScreen'] ) {
			$class .= ' is-full';
		}
		if ( isset( $attr['isFullScreen'] )
			&& ! $attr['isFullScreen']
			&& isset( $attr['parallax'] )
			&& $attr['parallax'] ) {
				$class .= ' is-full';
		}
		if ( isset( $attr['hasParallax'] ) && $attr['hasParallax'] ) {
			$class .= ' has-parallax';
		}
		if ( isset( $attr['arrowStyle'] ) && $attr['arrowStyle'] ) {
			$class .= ' ' . $attr['arrowStyle'];
		}
		if ( isset( $attr['arrowPosition'] ) ) {
			$class .= ' ' . $attr['arrowPosition'];
		}
		if ( isset( $attr['arrowMixBlendMode'] ) ) {
			$class .= ' arrow-mb-' . $attr['arrowMixBlendMode'];
		}
		if ( isset( $attr['dotStyle'] ) && $attr['dotStyle'] ) {
			$class .= ' ' . $attr['dotStyle'];
		}
		if ( isset( $attr['className'] ) ) {
			$class .= ' ' . $attr['className'];
		}
		if ( isset( $attr['contentPosition'] ) && $attr['contentPosition'] !== '' ) {
			$class .= sprintf( ' %1$s', $POSITION_CLASSNAMES[ $attr['contentPosition'] ] );
		}
		if ( isset( $attr['visibleOnDesktop'] ) && ! $attr['visibleOnDesktop'] ) {
			$class .= ' ed-desktop-hidden';
		}
		if ( isset( $attr['visibleOnTablet'] ) && ! $attr['visibleOnTablet'] ) {
			$class .= ' ed-tablet-hidden';
		}
		if ( isset( $attr['visibleOnMobile'] ) && ! $attr['visibleOnMobile'] ) {
			$class .= ' ed-mobile-hidden';
		}

		$bg_image = null;
		if ( isset( $attr['bgImageId'] ) ) {
			$bg_image = wp_get_attachment_image_src( $attr['bgImageId'], 'medium' )[0];
		}

		$arrow_bg_color = isset( $attr['arrowBgColor'] ) ? $attr['arrowBgColor'] : 'transparent';
		$arrow_bg_hover_color = isset( $attr['arrowBgColor'] ) ? $attr['arrowBgColor'] : 'transparent';

		$component_style = sprintf(
			'--gutenslider-min-height: %1$s;'
			. '--gutenslider-arrow-size: %2$spx;'
			. '--gutenslider-dot-size: %3$spx;'
			. '--gutenslider-arrow-color: %4$s;'
			. '--gutenslider-dot-color: %5$s;'
			. '--gutenslider-padding-y-mobile: %6$s%12$s;'
			. '--gutenslider-padding-x-mobile: %7$s%13$s;'
			. '--gutenslider-padding-y-tablet: %8$s%12$s;'
			. '--gutenslider-padding-x-tablet: %9$s%13$s;'
			. '--gutenslider-padding-y-desktop: %10$s%12$s;'
			. '--gutenslider-padding-x-desktop: %11$s%13$s;'
			. '--gutenslider-bg-image: url(%14$s);'
			. '--gutenslider-min-height-md: %15$s;'
			. '--gutenslider-min-height-sm: %16$s;'
			. '--gutenslider-padding-x: %17$s;'
			. '--gutenslider-padding-x-md: %18$s;'
			. '--gutenslider-padding-x-sm: %19$s;'
			. '--gutenslider-padding-y: %20$s;'
			. '--gutenslider-padding-y-md: %21$s;'
			. '--gutenslider-padding-y-sm: %22$s;'
			. '--gutenslider-arrow-size-md: %23$spx;'
			. '--gutenslider-arrow-size-sm: %24$spx;'
			. '--gutenslider-dot-size-md: %25$spx;'
			. '--gutenslider-dot-size-sm: %26$spx;'
			. '--gutenslider-arrow-bg-color: %27$s;'
			. '--gutenslider-arrow-hover-color: %28$s;'
			. '--gutenslider-arrow-bg-hover-color: %29$s;'
			. '--gutenslider-arrow-x-offset: %30$spx;'
			. '--gutenslider-arrow-x-offset-md: %31$spx;'
			. '--gutenslider-arrow-x-offset-sm: %32$spx;'
			. '--gutenslider-arrow-y-offset: %33$spx;'
			. '--gutenslider-arrow-y-offset-md: %34$spx;'
			. '--gutenslider-arrow-y-offset-sm: %35$spx;'
			. '--gutenslider-arrow-x-spacing: %36$spx;',
			$attr['sliderHeight'],
			$attr['arrowSize'],
			$attr['dotSize'],
			$attr['arrowColor'],
			$attr['dotColor'],
			$attr['spacingYMobile'],
			$attr['spacingXMobile'],
			$attr['spacingYTablet'],
			$attr['spacingXTablet'],
			$attr['spacingYDesktop'],
			$attr['spacingXDesktop'],
			$attr['spacingYUnit'],
			$attr['spacingXUnit'],
			$bg_image,
			$attr['sliderHeightMd'],
			$attr['sliderHeightSm'],
			$attr['paddingX'],
			$attr['paddingXMd'],
			$attr['paddingXSm'],
			$attr['paddingY'],
			$attr['paddingYMd'],
			$attr['paddingYSm'],
			$attr['arrowSizeMd'],
			$attr['arrowSizeSm'],
			$attr['dotSizeMd'],
			$attr['dotSizeSm'],
			$attr['arrowBgColor'],
			$attr['arrowHoverColor'],
			$attr['arrowBgHoverColor'],
			$attr['arrowXOffset'],
			$attr['arrowXOffsetMd'],
			$attr['arrowXOffsetSm'],
			$attr['arrowYOffset'],
			$attr['arrowYOffsetMd'],
			$attr['arrowYOffsetSm'],
			$attr['arrowXSpacing']
		);

		$overlay_style = '';
		if ( isset( $attr['rgbaBackground'] ) && $attr['rgbaBackground'] ) {
			$overlay_style .= 'background: ' . esc_attr( $attr['rgbaBackground'] ) . ';';
		}

		$content_classes = sprintf(
			'wp-block-eedee-gutenslider__content mb-%1$s co-%2$s',
			$attr['mixBlendMode'],
			$attr['contentOpacity']
		);

		$prev_arrow = $eedee_gutenslider_arrows[ $attr['arrowStyle'] ][ 'prev' ];
		$next_arrow = $eedee_gutenslider_arrows[ $attr['arrowStyle'] ][ 'next' ];

		$slider_settings = array(
			'lazyload'         => 'ondemand',
			'infinite'         => true,
			'pauseOnFocus'     => true,
			'pauseOnHover'     => true,
			'dots'             => $attr['dots'],
			'arrows'           => $attr['arrows'],
			'autoplaySpeed'    => $attr['duration'] * 1000,
			'speed'            => $attr['fadeSpeed'] * 1000,
			'autoplay'         => $attr['autoplay'],
			'fade'             => $attr['fadeMode'],
			'pauseOnFocus'     => $attr['pauseOnFocus'],
			'pauseOnHover'     => $attr['pauseOnHover'],
			'pauseOnDotsHover' => $attr['pauseOnDotsHover'],
			'slidesToShow'     => $attr['fadeMode'] ? 1 : $attr['slidesToShow'],
			'slidesToScroll'   => $attr['fadeMode'] ? 1 : $attr['slidesToScroll'],
			'infinite'         => $attr['loop'],
			'adaptiveHeight'   => $attr['adaptiveHeight'],
			'responsive'       => array(
				array(
					'breakpoint' => 960,
					'settings'   => array(
						'arrows'         => $attr['arrowsMd'],
						'dots'           => $attr['dotsMd'],
						'slidesToShow'   => $attr['fadeMode'] ? 1 : $attr['slidesToShowMd'],
						'slidesToScroll' => $attr['fadeMode'] ? 1 : $attr['slidesToScrollMd'],
					),
				),
				array(
					'breakpoint' => 600,
					'settings'   => array(
						'arrows'         => $attr['arrowsSm'],
						'dots'           => $attr['dotsSm'],
						'slidesToShow'   => $attr['fadeMode'] ? 1 : $attr['slidesToShowSm'],
						'slidesToScroll' => $attr['fadeMode'] ? 1 : $attr['slidesToScrollSm'],
					),
				),
			),
		);

		$slider_settings = json_encode( $slider_settings );

		$additional_attributes = '';
		if ( isset( $attr['parallaxDirection'] ) ) {
			$additional_attributes .= sprintf(
				'data-parallax-direction="%1$s"',
				esc_attr( $attr['parallaxDirection'] )
			);
		}
		if ( isset( $attr['parallaxAmount'] ) ) {
			$additional_attributes .= sprintf(
				' data-parallax-amount="%1$s"',
				esc_attr( $attr['parallaxAmount'] )
			);
		}

		// if the content mode is fixed, we need to print the content twice
		// and hide it in css, that is because wp gutenberg does not allow multiple
		// inner blocks by the time of writing
		// @fix @todo there will be another way soon.
		if ( 'fixed' === $attr['contentMode'] ) {
			return sprintf(
				'<div id="%8$s" class="%1$s" style="%2$s" %9$s>'
				. '<div class="slider-overlay" style="%7$s"></div>'
				. '<div class="slick-slider" data-slick=\'%5$s\'>%3$s</div>'
				. '<div class="%6$s" style="">%4$s</div>'
				. '<button class="slick-arrow slick-prev eedee-gutenslider-nav eedee-gutenslider-prev">%10$s</button>'
				. '<button class="slick-arrow slick-next eedee-gutenslider-nav eedee-gutenslider-next">%11$s</button>'
				. '</div>',
				esc_attr( $class ),
				$component_style,
				$inner_content,
				$inner_content,
				$slider_settings,
				$content_classes,
				$overlay_style,
				esc_attr( $id ),
				$additional_attributes,
				$prev_arrow,
				$next_arrow
			);
		}

		return sprintf(
			'<div id="%6$s" class="%1$s" style="%2$s" %5$s>'
			. '<div class="slick-slider" data-slick=\'%4$s\'>%3$s</div>'
			. '<button class="slick-arrow slick-prev eedee-gutenslider-nav eedee-gutenslider-prev">%7$s</button>'
			. '<button class="slick-arrow slick-next eedee-gutenslider-nav eedee-gutenslider-next">%8$s</button>'
			. '</div>',
			esc_attr( $class ),
			$component_style,
			$inner_content,
			$slider_settings,
			$additional_attributes,
			esc_attr( $id ),
			$prev_arrow,
			$next_arrow
		);
	}
}
