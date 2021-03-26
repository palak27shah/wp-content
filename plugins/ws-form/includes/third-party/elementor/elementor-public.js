(function($) {

	'use strict';

	$( window ).on( 'elementor/frontend/init', function() {

		elementorFrontend.hooks.addAction( 'frontend/element_ready/ws-form.default', function($scope, $) {

			wsf_form_init(true);
		});
	});

})(jQuery);