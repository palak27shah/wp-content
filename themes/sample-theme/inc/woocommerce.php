<?php
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 2 );

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );

add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 0 );

function sampletheme_woocommerce_single_product_summary_subtitle() {
    echo '<b style="color: red">Look at the amazing price!!!!</b>';
}
add_action( 'woocommerce_single_product_summary', 'sampletheme_woocommerce_single_product_summary_subtitle', 4);


/**
 * Show cart contents / total Ajax
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment' );

function woocommerce_header_add_to_cart_fragment( $fragments ) {
	global $woocommerce;

	ob_start();

	?>
	<a class="cart-customlocation" href="<?php echo esc_url(wc_get_cart_url()); ?>" title="<?php _e('View your shopping cart', 'woothemes'); ?>"><?php echo sprintf(_n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'woothemes'), $woocommerce->cart->cart_contents_count);?></a>
	<?php
	$fragments['a.cart-customlocation'] = ob_get_clean();
	return $fragments;
}
