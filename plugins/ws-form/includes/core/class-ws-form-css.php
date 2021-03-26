<?php

	class WS_Form_CSS {

		public function __construct() {

			// Initial build
			$css_compile = WS_Form_Common::option_get('css_compile', false);
			$css_public_layout = WS_Form_Common::option_get('css_public_layout', '');
			if($css_compile && empty($css_public_layout)) {

				self::build_public_css();
			}

			// Actions that recompile CSS
			add_action('wsf_activate', array($this, 'build_public_css'));
			add_action('wsf_settings_update', array($this, 'build_public_css'));
			add_filter('customize_save_response', function($response) {

				self::build_public_css();

				return $response;
			});
		}

		// Build public CSS files
		public function build_public_css() {

			$css_compile = WS_Form_Common::option_get('css_compile', false);
			$css_inline = WS_Form_Common::option_get('css_inline', false);

			if($css_compile) {

				// Build file upload directory
				if(!$css_inline) {

					$upload_dir = WS_Form_Common::upload_dir_create(WS_FORM_CSS_FILE_PATH);
					if($upload_dir['error']) { self::db_throw_error($upload_dir['error']); }
					$file_upload_dir = $upload_dir['dir'];
				}

				// Build CSS (Layout)
				$css_layout = self::get_public(false, true);
				if(!$css_inline) {

					file_put_contents($file_upload_dir . '/public.layout.css', $css_layout);
				}
				WS_Form_Common::option_set('css_public_layout', $css_layout);

				// Build CSS (Skin)
				$css_skin = self::get_skin(false, true);
				if(!$css_inline) {

					file_put_contents($file_upload_dir . '/public.skin.css', $css_skin);
				}
				WS_Form_Common::option_set('css_public_skin', $css_skin);

				// Build CSS (Layout - Minimized)
				$css_layout_minimized = self::minify($css_layout);
				$css_layout = null;
				if(!$css_inline) {

					file_put_contents($file_upload_dir . '/public.layout.min.css', $css_layout_minimized);
				}
				WS_Form_Common::option_set('css_public_layout_min', $css_layout_minimized);
				$css_layout_minimized = null;

				// Build CSS (Skin - Minimized)
				$css_skin_minimized = self::minify($css_skin);
				$css_skin = null;
				if(!$css_inline) {

					file_put_contents($file_upload_dir . '/public.skin.min.css', $css_skin_minimized);
				}
				WS_Form_Common::option_set('css_public_skin_min', $css_skin_minimized);
				$css_skin_minimized = null;
			}
		}

		// Admin
		public function get_admin() {

			// Get form column count
			$columns = intval(WS_Form_Common::option_get('framework_column_count', 0));
			if($columns == 0) { self::db_throw_error(__('Invalid framework column count', 'ws-form')); }

			// Read frameworks
			$frameworks = WS_Form_Config::get_frameworks();

			// Get framework ID
			$framework_id = WS_Form_Common::option_get('framework', 'ws-form');

			// Get framework
			$framework = $frameworks['types'][$framework_id];

			// Get column class mask
			$column_class = $framework['columns']['column_css_selector'];

			// Get current framework breakpoints
			$breakpoints_outer = $framework['breakpoints'];
			$breakpoints_inner = $framework['breakpoints'];

			// Build CSS
			$css_return = ".wsf-group:before {\n\tbackground-image: repeating-linear-gradient(to right, #E5E5E5, #E5E5E5 calc((100% / $columns) - 12px), transparent calc((100% / $columns) - 12px), transparent calc(100% / $columns));\n\tbackground-size: calc(100% + 12px) 100%;\n}\n\n";
			$css_return .= ".wsf-section > .wsf-section-inner:before {\n\tbackground-image: repeating-linear-gradient(to right, #E5E5E5, #E5E5E5 calc((100% / $columns) - 12px), transparent calc((100% / $columns) - 12px), transparent calc(100% / $columns));\n\tbackground-size: calc(100% - 12px) 100%;\n\tbackground-position-x: 12px;\n}\n\n";

			// Grid
			$css_return .= ".wsf-sections, .wsf-fields {\n";

			$css_return .= "\tdisplay: -webkit-box;\n";
			$css_return .= "\tdisplay: -ms-flexbox;\n";
			$css_return .= "\tdisplay: flex;\n";
			$css_return .= "\t-ms-flex-wrap: wrap;\n";
			$css_return .= "\tflex-wrap: wrap;\n";

			$css_return .= "}\n\n";

			$breakpoint_outer_index = 0;
			foreach($breakpoints_outer as $key_outer => $breakpoint_outer) {

				// Get outer breakpoint ID and name
				$breakpoint_outer_id = $breakpoint_outer['id'];
				$breakpoint_outer_name = $breakpoint_outer['name'];

				// Output comment
				$css_return .= WS_Form_Common::comment_css($breakpoint_outer_name);

				// Add classes for breakpoint widths to resize admin
				if(WS_Form_Common::option_get('helper_breakpoint_width', false)) {

					// Output max-width statements
					if($breakpoint_outer_index != (count($breakpoints_outer) - 1)) {

						if(!isset($breakpoint_outer['admin_max_width'])) {

							self::db_throw_error(__('Admin max width not defined: ' . $breakpoint_outer_id, 'ws-form'));

						} else {

							$breakpoint_outer_max_width = $breakpoint_outer['admin_max_width'];
						}

						$css_return .= "#wsf-form[data-breakpoint=\"" . $breakpoint_outer_id . "\"] { max-width: " . $breakpoint_outer_max_width . "px; }\n\n";
					}
				}

				// Check for breakpoint specific CSS selector
				if(isset($breakpoint_outer['column_css_selector'])) {

					$column_class_single = $breakpoint_outer['column_css_selector'];

				} else {

					$column_class_single = $column_class;
				}

				// Columns - Run through each column
				for($column_index = 1; $column_index <= $columns; $column_index++) {

					// Create CSS for each column and each breakpoint
					$breakpoint_inner_index = 1;
					foreach($breakpoints_inner as $key_inner => $breakpoint_inner) {

						// Get inner breakpoint ID
						$breakpoint_inner_id = $breakpoint_inner['id'];

						// Build mask values for parser
						$mask_values = ['id' => $breakpoint_outer_id, 'size' => $column_index];

						// COLUMN

						// Get single class
						$class_single = WS_Form_Common::mask_parse($column_class_single, $mask_values);

						// Build CSS selectors
						$css_return .= "#wsf-form[data-breakpoint=\"" . $breakpoint_inner_id . '"] ' . $class_single;

						// Get key of top breakpoint (we'll remove this for the next run)
						if($breakpoint_inner_index == 1) { $breakpoint_inner_key_to_delete = $key_inner; }

						if($breakpoint_inner_index == count($breakpoints_inner)) {

							$column_width_percentage = ($column_index / $columns) * 100;

							$css_return .= " {";

							$css_return .= "\n\t-webkit-box-flex: 0;";
							$css_return .= "\n\t-ms-flex: 0 0 " . $column_width_percentage . "%;";
							$css_return .= "\n\tflex: 0 0 " . $column_width_percentage . "%;";
							$css_return .= "\n\tmax-width: " . $column_width_percentage . "%;";

							$css_return .= "\n}\n\n";

						} else {

							// Add comma (not at last inner breakpoint yet)
							$css_return .= ",\n";
						}

						$breakpoint_inner_index++;
					}
				}

				// Take top key off the inner breakpoints
				unset($breakpoints_inner[$breakpoint_inner_key_to_delete]);

				$breakpoint_outer_index++;
			}

			// Offsets - Run through each column
			$offset_class = $framework['columns']['offset_css_selector'];

			// Get current framework breakpoints
			$breakpoints_outer = $framework['breakpoints'];
			$breakpoints_inner = $framework['breakpoints'];

			foreach($breakpoints_outer as $key_outer => $breakpoint_outer) {

				// Get outer breakpoint ID and name
				$breakpoint_outer_id = $breakpoint_outer['id'];
				$breakpoint_outer_name = $breakpoint_outer['name'];

				// Check for breakpoint specific CSS selector
				if(isset($breakpoint_outer['offset_css_selector'])) {

					$offset_class_single = $breakpoint_outer['offset_css_selector'];

				} else {

					$offset_class_single = $offset_class;
				}

				// Output comment
				$css_return .= WS_Form_Common::comment_css($breakpoint_outer_name . ' - Offsets');

				for($column_index = 0; $column_index < $columns; $column_index++) {

					// Create CSS for each column and each breakpoint
					$breakpoint_inner_index = 1;
					foreach($breakpoints_inner as $key_inner => $breakpoint_inner) {

						// Get inner breakpoint ID
						$breakpoint_inner_id = $breakpoint_inner['id'];

						// Build mask values for parser
						$mask_values = ['id' => $breakpoint_outer_id, 'offset' => $column_index];

						// Get single offset
						$offset_single = WS_Form_Common::mask_parse($offset_class_single, $mask_values);

						// Get key of top breakpoint (we'll remove this for the next run)
						if($breakpoint_inner_index == 1) { $breakpoint_inner_key_to_delete = $key_inner; }

						// Build CSS selectors
						$css_return .= "#wsf-form[data-breakpoint=\"" . $breakpoint_inner_id . '"] ' . $offset_single;

						// Get key of top breakpoint (we'll remove this for the next run)
						if($breakpoint_inner_index == 1) { $breakpoint_inner_key_to_delete = $key_inner; }

						if($breakpoint_inner_index == count($breakpoints_inner)) {

							$column_width_percentage = ($column_index / $columns) * 100;

							// Build offset CSS
							$css_return .= " {";

							$css_return .= "\n\tbackground-size: " . $column_width_percentage . "%;";
							$css_return .= "\n\tmargin-" . (is_rtl() ? 'right' : 'left') . ": " . $column_width_percentage . "%;";

							$css_return .= "\n}\n\n";

						} else {

							// Add comma (not at last inner breakpoint yet)
							$css_return .= ",\n";
						}

						$breakpoint_inner_index++;
					}
				}

				// Take top key off the inner breakpoints
				unset($breakpoints_inner[$breakpoint_inner_key_to_delete]);
			}

			// Apply filters
			$css_return = apply_filters('wsf_get_admin', $css_return);

			return self::minify($css_return);
		}

		// Public
		public function get_public($css_minify = null, $force_build = false) {

			// Build CSS
			$css_return = '';

			// Minify
			if(is_null($css_minify)) {

				$css_minify = !SCRIPT_DEBUG;
			}

			// Initial build of compiled files
			$css_compile = WS_Form_Common::option_get('css_compile', false);
			if($css_compile && !$force_build) {

				if($css_minify) {

					$css_return = WS_Form_Common::option_get('css_public_layout_min');

				} else {

					$css_return = WS_Form_Common::option_get('css_public_layout');
				}

			} else {

				// Read frameworks
				$frameworks = WS_Form_Config::get_frameworks();

				// Get framework ID
				$framework_id = WS_Form_Common::option_get('framework', 'ws-form');

				// Get framework
				$framework = $frameworks['types'][$framework_id];

				// Get column class mask
				$column_class = $framework['columns']['column_css_selector'];

				// Get form column count
				$columns = intval(WS_Form_Common::option_get('framework_column_count', 0));
				if($columns == 0) { self::db_throw_error(__('Invalid framework column count', 'ws-form')); }

				$grid_spacing = 0;
				$grid_spacing_unit = 'px';

				// Invalid Feedback
				$css_return .= ".wsf-invalid-feedback,\n";
				$css_return .= "[data-select-min-max], \n";
				$css_return .= "[data-checkbox-min-max] {\n";
				$css_return .= "\tdisplay: none;\n";
				$css_return .= "}\n\n";

				$css_return .= ".wsf-validated .wsf-field:invalid ~ .wsf-invalid-feedback,\n";
				$css_return .= ".wsf-validated .wsf-field.wsf-invalid ~ .wsf-invalid-feedback,\n";
				$css_return .= ".wsf-validated [data-select-min-max]:invalid ~ .wsf-invalid-feedback,\n";
				$css_return .= ".wsf-validated [data-checkbox-min-max]:invalid ~ .wsf-invalid-feedback {\n";
				$css_return .= "\tdisplay: block;\n";
				$css_return .= "}\n\n";

				// Grid
				$css_return .= ".wsf-grid {\n";

				$css_return .= "\tdisplay: -webkit-box;\n";
				$css_return .= "\tdisplay: -ms-flexbox;\n";
				$css_return .= "\tdisplay: flex;\n";
				$css_return .= "\t-ms-flex-wrap: wrap;\n";
				$css_return .= "\tflex-wrap: wrap;\n";

				if($grid_spacing > 0) {

					$css_return .= "\tmargin-left: " . (($grid_spacing / 2) * -1) . $grid_spacing_unit . " !important;\n";
					$css_return .= "\tmargin-right: " . (($grid_spacing / 2) * -1) . $grid_spacing_unit . " !important;\n";
				}

				$css_return .= "}\n\n";

				// Tile
				$css_return .= ".wsf-tile {\n";
				$css_return .= "\tposition: relative;\n";
				$css_return .= "\twidth: 100%;\n";
				$css_return .= "\tbox-sizing: border-box;\n";

				if($grid_spacing > 0) {
					$css_return .= "\tpadding-left: " . ($grid_spacing / 2) . $grid_spacing_unit . " !important;\n";
					$css_return .= "\tpadding-right: " . ($grid_spacing / 2) . $grid_spacing_unit . " !important;\n";
				}
				$css_return .= "}\n\n";

				// Breakpoint CSS
				foreach($framework['breakpoints'] as $key => $breakpoint) {

					// Get outer breakpoint ID and name
					$breakpoint_id = $breakpoint['id'];
					$breakpoint_name = $breakpoint['name'];
					if(isset($breakpoint['min_width'])) {
						$breakpoint_min_width = $breakpoint['min_width'];
					} else {
						$breakpoint_min_width = 0;
					}

					// Output comment
					$css_return .= WS_Form_Common::comment_css($breakpoint_name);

					// Output media query
					$css_indent = '';
					if($breakpoint_min_width > 0) {

						$css_return .= "@media (min-width: " . $breakpoint_min_width . "px) {\n\n";
						$css_indent = "\t";
					}

					// Check for breakpoint specific CSS selector
					if(isset($breakpoint['column_css_selector'])) {

						$column_class_single = $breakpoint['column_css_selector'];

					} else {

						$column_class_single = $column_class;
					}

					// Run through each column
					for($column_index = 1; $column_index <= $columns; $column_index++) {

						// Build mask values for parser
						$mask_values = ['id' => $breakpoint_id, 'size' => $column_index];

						// Get single class
						$class_single = WS_Form_Common::mask_parse($column_class_single, $mask_values);

						// Build CSS selectors
						$css_return .= $css_indent . $class_single;

						$column_width_percentage = round(($column_index / $columns) * 100, 6);

						$css_return .= " {";

						$css_return .= "\n" . $css_indent . "\t-webkit-box-flex: 0 !important;";
						$css_return .= "\n" . $css_indent . "\t-ms-flex: 0 0 " . $column_width_percentage . "% !important;";
						$css_return .= "\n" . $css_indent . "\tflex: 0 0 " . $column_width_percentage . "% !important;";
						$css_return .= "\n" . $css_indent . "\tmax-width: " . $column_width_percentage . "% !important;";

						$css_return .= "\n" . $css_indent . "}\n\n";
					}

					// Close media query
					if($breakpoint_min_width > 0) {

						$css_return .= "}\n\n";
					}
				}

				// Offsets - Run through each column
				$offset_class = $framework['columns']['offset_css_selector'];

				// Breakpoint CSS
				foreach($framework['breakpoints'] as $key => $breakpoint) {

					// Get outer breakpoint ID and name
					$breakpoint_id = $breakpoint['id'];
					$breakpoint_name = $breakpoint['name'];
					if(isset($breakpoint['min_width'])) {
						$breakpoint_min_width = $breakpoint['min_width'];
					} else {
						$breakpoint_min_width = 0;
					}

					// Output comment
					$css_return .= WS_Form_Common::comment_css($breakpoint_name . ' - Offsets');

					// Output media query
					$css_indent = '';
					if($breakpoint_min_width > 0) {

						$css_return .= "@media (min-width: " . $breakpoint_min_width . "px) {\n\n";
						$css_indent = "\t";
					}

					// Check for breakpoint specific CSS selector
					if(isset($breakpoint['offset_css_selector'])) {

						$offset_class_single = $breakpoint['offset_css_selector'];

					} else {

						$offset_class_single = $offset_class;
					}

					for($column_index = 0; $column_index <= $columns; $column_index++) {

						// Build mask values for parser
						$mask_values = ['id' => $breakpoint_id, 'offset' => $column_index];

						// Get single offset
						$offset_single = WS_Form_Common::mask_parse($offset_class_single, $mask_values);

						$column_width_percentage = ($column_index / $columns) * 100;

						// Build CSS selectors
						$css_return .= $css_indent . $offset_single . " {\n";

						// Build offset CSS
						$css_return .= $css_indent . "\tmargin-" . (is_rtl() ? 'right' : 'left') . ": " . $column_width_percentage . "% !important;\n";

						$css_return .= $css_indent . "}\n\n";
					}

					// Close media query
					if($breakpoint_min_width > 0) {

						$css_return .= "}\n\n";
					}
				}

				$css_return .= ".wsf-bottom {\n";
				$css_return .= "\talign-self: flex-end !important;\n";
				$css_return .= "}\n\n";

				$css_return .= ".wsf-top {\n";
				$css_return .= "\talign-self: flex-start !important;\n";
				$css_return .= "}\n\n";

				$css_return .= ".wsf-middle {\n";
				$css_return .= "\talign-self: center !important;\n";
				$css_return .= "}\n\n";

				// Apply filters
				$css_return = apply_filters('wsf_get_public', $css_return);

				// Minify
				if(is_null($css_minify)) {

					$css_minify = !SCRIPT_DEBUG;
				}

				$css_return = $css_minify ? self::minify($css_return) : $css_return;
			}

			return $css_return;
		}

		// Skin
		public function render_skin() {

			// Customizer
			$enable_cache = !(WS_Form_Common::get_query_var('customize_theme') !== '');

			$customize_groups = WS_Form_Config::get_customize();

			// Set skin variables
			foreach($customize_groups as $customize_group) {

				foreach($customize_group['fields'] as $meta_key => $config) {

					$variable = str_replace('skin_', '', $meta_key);

					$default = isset($config['default']) ? $config['default'] : '';

					$type = isset($config['type']) ? $config['type'] : '';

					$$variable = WS_Form_Common::option_get($meta_key, $default, false, $enable_cache, true);
				}
			}

			// Advanced
			$unit_of_measurement = 'px';

			// Spacing
			$spacing = 20;
			$spacing_extra_large = 80;
			$spacing_large = 40;
			$spacing_small = 10;
			$spacing_extra_small = 5;

			// Forms
			$form_background_color = $color_default_inverted;
			$form_border = $border; // true | false
			$form_border_color = $color_default_lighter;
			$form_border_style = $border_style;
			$form_border_width = $border_width;
			$form_border_radius = $border_radius;
			$form_box_shadow = true;
			$form_box_shadow_width = 2;
			$form_checked_color = $color_primary;
			$form_color = $color_default;
			$form_disabled_background_color = $color_default_lightest;
			$form_disabled_border_color = $form_border_color;
			$form_disabled_color = $color_default_light;
			$form_error_background_color = $form_background_color;
			$form_error_border_color = $color_danger;
			$form_error_color = $form_color;
			$form_focus = true; // true | false
			$form_focus_background_color = $form_background_color;
			$form_focus_border_color = $color_primary;
			$form_focus_color = $form_color;
			$form_font_size = $font_size;
			$form_font_size_large = $font_size_large;
			$form_font_size_small = $font_size_small;
			$form_help_color = $color_default_light;
			$form_invalid_feedback_color = $color_danger;
			$form_hover = false; // true | false
			$form_hover_background_color = $form_background_color;
			$form_hover_border_color = $color_primary;
			$form_hover_color = $form_color;
			$form_label_color = $form_color;
			$form_placeholder_color = $color_default_light;
			$form_spacing_horizontal = $spacing_small;
			$form_spacing_vertical = ($spacing_small * .85);
			$form_transition = $transition; // true | false
			$form_transition_timing_function = $transition_timing_function;
			$form_transition_speed = $transition_speed . 'ms ' . $form_transition_timing_function;
			$input_height = ((round($form_font_size * $line_height) + ($form_spacing_vertical * 2)) + ($form_border_width * 2));
			$checkbox_size = round($form_font_size * $line_height);
			$radio_size = round($form_font_size * $line_height);
			$color_size = $input_height;
?>
.wsf-form {
	box-sizing: border-box;
	color: <?php self::e($color_default); ?>;
	font-family: <?php self::e($font_family); ?>;
	font-size: <?php self::e($font_size . $unit_of_measurement); ?>;
	font-weight: <?php self::e($font_weight); ?>;
	line-height: <?php self::e($line_height); ?>;
	-webkit-tap-highlight-color: transparent;
	text-size-adjust: 100%;
}

.wsf-form *, .wsf-form *:before, .wsf-form *:after {
	box-sizing: inherit;
}

.wsf-section,
.wsf-fieldset {
	border: none;
	margin: 0;
	min-width: 0;
	padding: 0;
}

.wsf-section > legend,
.wsf-fieldset > legend {
	border: 0;
	font-family: <?php self::e($font_family); ?>;
	font-size: <?php self::e($form_font_size_large . $unit_of_measurement); ?>;
	font-weight: <?php self::e($font_weight); ?>;
	line-height: <?php self::e($line_height); ?>;
	margin-bottom: <?php self::e($spacing_small . $unit_of_measurement); ?>;
	padding: 0;
}

.wsf-form ul.wsf-group-tabs {
	border-bottom: <?php self::e($border_width . $unit_of_measurement . ' ' . $border_style . ' ' . $color_default_lighter); ?>;
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	list-style: none;
	margin-bottom: <?php self::e($spacing . $unit_of_measurement); ?>;
	margin-left: -<?php self::e($spacing_extra_small . $unit_of_measurement); ?>;
	margin-right: -<?php self::e($spacing_extra_small . $unit_of_measurement); ?>;
	margin-top: 0;
	padding-left: 0;
	position: relative;
}

.wsf-form ul.wsf-group-tabs > li {
	box-sizing: border-box;
	margin-bottom: -<?php self::e($border_width . $unit_of_measurement); ?>;
	outline: none;
	padding: 0 <?php self::e($spacing_extra_small . $unit_of_measurement); ?>;
	position: relative;
}

.wsf-form ul.wsf-group-tabs > li > a {
	background-color: transparent;
	border: <?php self::e(($border_width . $unit_of_measurement . ' ' . $border_style) . ' transparent'); ?>;
<?php if ($border_radius > 0) { ?>
	border-top-left-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
	border-top-right-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
<?php } ?>
	box-shadow: none;
	color: <?php self::e($color_default); ?>;
	cursor: pointer;
	display: block;
	font-family: <?php self::e($font_family); ?>;
	font-size: <?php self::e($font_size . $unit_of_measurement); ?>;
	font-weight: <?php self::e($font_weight); ?>;
	line-height: <?php self::e($line_height); ?>;
	padding: 11px <?php self::e($spacing . $unit_of_measurement); ?>;
	text-align: center;
	text-decoration: none;
<?php if ($transition) { ?>
	transition: background-color <?php self::e($transition_speed . 'ms ' . $transition_timing_function); ?>, border-color <?php self::e($transition_speed . 'ms ' . $transition_timing_function); ?>;
<?php } ?>
	white-space: nowrap;
}

.wsf-form ul.wsf-group-tabs > li > a:focus {
	outline: 0;
}

.wsf-form ul.wsf-group-tabs > li.wsf-tab-active {
	z-index: 1;
}

.wsf-form ul.wsf-group-tabs > li.wsf-tab-active > a {
	background-color: <?php self::e($color_default_inverted); ?>;
	border-color: <?php self::e($color_default_lighter); ?>;
	border-bottom-color: transparent;
	color: <?php self::e($color_default); ?>;
	cursor: default;
}

.wsf-form ul.wsf-group-tabs > li > a.wsf-tab-disabled {
	color: <?php self::e($color_default_light); ?>;
	cursor: not-allowed;
	pointer-events: none;
}

.wsf-grid {
	margin-left: -<?php self::e(($grid_gutter / 2) . $unit_of_measurement); ?>;
	margin-right: -<?php self::e(($grid_gutter / 2) . $unit_of_measurement); ?>;
}

.wsf-tile {
	padding-left: <?php self::e(($grid_gutter / 2) . $unit_of_measurement); ?>;
	padding-right: <?php self::e(($grid_gutter / 2) . $unit_of_measurement); ?>;
}

.wsf-field-wrapper {
	margin-bottom: <?php self::e($grid_gutter . $unit_of_measurement); ?>;
}

.wsf-field-wrapper[data-type='texteditor'],
.wsf-field-wrapper[data-type='html'],
.wsf-field-wrapper[data-type='divider'],
.wsf-field-wrapper[data-type='message'] {
	margin-bottom: 0;
}

.wsf-inline {
	display: inline-flex;
	flex-direction: column;
	-webkit-margin-end: <?php self::e($spacing_small . $unit_of_measurement); ?>;
	margin-inline-end: <?php self::e($spacing_small . $unit_of_measurement); ?>;
}

.wsf-label-wrapper label.wsf-label {
	padding: <?php self::e(($form_spacing_vertical + $form_border_width) . $unit_of_measurement); ?> 0;
	margin-bottom: 0;
}

label.wsf-label {
	display: block;
<?php if ($form_label_color != $color_default) { ?>
	color: <?php self::e($form_label_color); ?>;
<?php } ?>
	font-family: <?php self::e($font_family); ?>;
	font-size: <?php self::e($form_font_size . $unit_of_measurement); ?>;
	font-weight: <?php self::e($font_weight); ?>;
	line-height: <?php self::e($line_height); ?>;
	margin-bottom: <?php self::e($spacing_extra_small . $unit_of_measurement); ?>;
	user-select: none;
}

.wsf-field + label.wsf-label,
.wsf-input-group-append + label.wsf-label {
	margin-bottom: 0;
	margin-top: <?php self::e($spacing_extra_small . $unit_of_measurement); ?>;
}

.wsf-invalid-feedback {
	color: <?php self::e($form_invalid_feedback_color); ?>;
	font-size: <?php self::e($form_font_size_small . $unit_of_measurement); ?>;
	line-height: <?php self::e($line_height); ?>;
	margin-top: <?php self::e($spacing_extra_small . $unit_of_measurement); ?>;
}

.wsf-help {
	color: <?php self::e($form_help_color); ?>;
	display: block;
	font-size: <?php self::e($form_font_size_small . $unit_of_measurement); ?>;
	line-height: <?php self::e($line_height); ?>;
	margin-top: <?php self::e($spacing_extra_small . $unit_of_measurement); ?>;
}

.wsf-input-group {
	align-items: stretch;
	display: flex;
	flex-wrap: wrap;
	width: 100%;
}

.wsf-input-group > .wsf-field,
.wsf-input-group > select.wsf-field ~ .select2-container,
.wsf-input-group > input[type=text].wsf-field ~ .dropzone,
.wsf-input-group > input[type=text].wsf-field ~ canvas {
	flex: 1 1 auto;
	min-width: 0;
	position: relative;
	width: 1% !important;
}

<?php if ($form_border_radius > 0) { ?>
.wsf-input-group-has-prepend > .wsf-field,
.wsf-input-group-has-prepend > select.wsf-field ~ .select2-container .select2-selection--single,
.wsf-input-group-has-prepend > select.wsf-field ~ .select2-container .select2-selection--multiple,
.wsf-input-group-has-prepend > .dropzone {
	border-top-left-radius: 0 !important;
	border-bottom-left-radius: 0 !important;
}

.wsf-input-group-has-append > .wsf-field,
.wsf-input-group-has-append > select.wsf-field ~ .select2-container .select2-selection--single,
.wsf-input-group-has-append > select.wsf-field ~ .select2-container .select2-selection--multiple,
.wsf-input-group-has-append > .dropzone {
	border-top-right-radius: 0 !important;
	border-bottom-right-radius: 0 !important;
}
<?php } ?>

.wsf-input-group-prepend,
.wsf-input-group-append {
	align-items: center;
	background-color: <?php self::e($color_default_lightest); ?>;
<?php if ($form_border) { ?>
	border: <?php self::e($form_border_width . $unit_of_measurement . ' ' . $form_border_style . ' ' . $form_border_color); ?>;
<?php } ?>
	color: <?php self::e($form_color); ?>;
	display: flex;
	font-family: <?php self::e($font_family); ?>;
	font-size: <?php self::e($form_font_size . $unit_of_measurement); ?>;
	font-weight: <?php self::e($font_weight); ?>;
	line-height: <?php self::e($line_height); ?>;
	padding: <?php self::e($form_spacing_vertical . $unit_of_measurement . ' ' . $form_spacing_horizontal . $unit_of_measurement); ?>;
}

.wsf-input-group-prepend {
<?php if ($form_border_radius > 0) { ?>
	border-top-left-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
	border-bottom-left-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
<?php } ?>
<?php if ($form_border) { ?>
	-webkit-border-end: none;
	border-inline-end: none;
<?php } ?>
}

.wsf-input-group-append {
<?php if ($form_border_radius > 0) { ?>
	border-top-right-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
	border-bottom-right-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
<?php } ?>
<?php if ($form_border) { ?>
	-webkit-border-start: none;
	border-inline-start: none;
<?php } ?>
}

.wsf-input-group > label.wsf-label,
.wsf-input-group > .wsf-invalid-feedback,
.wsf-input-group > .wsf-help {
	width: 100%;
}

input[type=email].wsf-field,
input[type=number].wsf-field,
input[type=tel].wsf-field,
input[type=text].wsf-field,
input[type=url].wsf-field,
select.wsf-field,
textarea.wsf-field {
	-webkit-appearance: none;
	background-color: <?php self::e($form_background_color); ?>;
<?php if ($form_border) { ?>
	border: <?php self::e($form_border_width . $unit_of_measurement . ' ' . $form_border_style . ' ' . $form_border_color); ?>;
<?php } else { ?>
	border: none;
<?php } ?>
	border-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
	color: <?php self::e($form_color); ?>;
	font-family: <?php self::e($font_family); ?>;
	font-size: <?php self::e($form_font_size . $unit_of_measurement); ?>;
	font-weight: <?php self::e($font_weight); ?>;
	line-height: <?php self::e($line_height); ?>;
	margin: 0;
	padding: <?php self::e($form_spacing_vertical . $unit_of_measurement . ' ' . $form_spacing_horizontal . $unit_of_measurement); ?>;
	touch-action: manipulation;
<?php if ($form_transition) { ?>
	transition: background-color <?php self::e($form_transition_speed); ?>, background-image <?php self::e($form_transition_speed); ?>, border-color <?php self::e($form_transition_speed); ?>, box-shadow <?php self::e($form_transition_speed); ?>;
<?php } ?>
	width: 100%;
}

input[type=email].wsf-field,
input[type=number].wsf-field,
input[type=tel].wsf-field,
input[type=text].wsf-field,
input[type=url].wsf-field,
select.wsf-field:not([multiple]):not([size]) {
	height: <?php self::e($input_height . $unit_of_measurement); ?>;
}

input[type=email].wsf-field::placeholder,
input[type=number].wsf-field::placeholder,
input[type=tel].wsf-field::placeholder,
input[type=text].wsf-field::placeholder,
input[type=url].wsf-field::placeholder,
select.wsf-field::placeholder,
textarea.wsf-field::placeholder {
	color: <?php self::e($form_placeholder_color); ?>;
	opacity: 1;
}

<?php if ($form_hover) { ?>
input[type=email].wsf-field:hover:enabled,
input[type=number].wsf-field:hover:enabled,
input[type=tel].wsf-field:hover:enabled,
input[type=text].wsf-field:hover:enabled,
input[type=url].wsf-field:hover:enabled,
select.wsf-field:hover:enabled,
textarea.wsf-field:hover:enabled {
<?php if ($form_hover_background_color != $form_background_color) { ?>
	background-color: <?php self::e($form_hover_background_color); ?>;
<?php } ?>
<?php if ($form_hover_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_hover_border_color); ?>;
<?php } ?>
<?php if ($form_hover_color != $form_color) { ?>
	color: <?php self::e($form_hover_color); ?>;
<?php } ?>
}
<?php } ?>

input[type=email].wsf-field:focus,
input[type=number].wsf-field:focus,
input[type=tel].wsf-field:focus,
input[type=text].wsf-field:focus,
input[type=url].wsf-field:focus,
select.wsf-field:focus,
textarea.wsf-field:focus {
<?php if ($form_focus) { ?>
<?php if ($form_focus_background_color != $form_background_color) { ?>
	background-color: <?php self::e($form_focus_background_color); ?>;
<?php } ?>
<?php if ($form_focus_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_focus_border_color); ?>;
<?php } ?>
<?php if ($form_box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($color_primary, 85)); ?>;
<?php } ?>
<?php if ($form_focus_color != $form_color) { ?>
	color: <?php self::e($form_focus_color); ?>;
<?php } ?>
<?php } ?>
	outline: 0;
}

input[type=email].wsf-field:disabled,
input[type=number].wsf-field:disabled,
input[type=tel].wsf-field:disabled,
input[type=text].wsf-field:disabled,
input[type=url].wsf-field:disabled,
select.wsf-field:disabled,
textarea.wsf-field:disabled {
<?php if ($form_disabled_background_color != $form_background_color) { ?>
	background-color: <?php self::e($form_disabled_background_color); ?>;
<?php } ?>
<?php if ($form_border) { ?>
<?php if ($form_disabled_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_disabled_border_color); ?>;
<?php } ?>
<?php } ?>
<?php if ($form_disabled_color != $form_color) { ?>
	color: <?php self::e($form_disabled_color); ?>;
	-webkit-text-fill-color: <?php self::e($form_disabled_color); ?>;
<?php } else { ?>
	-webkit-text-fill-color: <?php self::e($form_color); ?>;
<?php } ?>
	cursor: not-allowed;
	opacity: 1;
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
}

input[type=email].wsf-field::-moz-focus-inner,
input[type=number].wsf-field::-moz-focus-inner,
input[type=tel].wsf-field::-moz-focus-inner,
input[type=text].wsf-field::-moz-focus-inner,
input[type=url].wsf-field::-moz-focus-inner,
select.wsf-field::-moz-focus-inner,
textarea.wsf-field::-moz-focus-inner {
	border: 0;
	padding: 0;
}

/* Number */
input[type=number].wsf-field::-webkit-inner-spin-button,
input[type=number].wsf-field::-webkit-outer-spin-button {
	height: auto;
}

/* Text Area */
textarea.wsf-field {
	min-height: <?php self::e($input_height . $unit_of_measurement); ?>;
	overflow: auto;
	resize: vertical;
}

textarea.wsf-field[data-textarea-type='tinymce'] {
	border-top-left-radius: 0;
	border-top-right-radius: 0;
}

[data-type='textarea'] .wp-editor-tabs {
	box-sizing: content-box;
}

[data-type='textarea'] .mce-btn.mce-active button,
[data-type='textarea'] .mce-btn.mce-active:hover button,
[data-type='textarea'] .mce-btn.mce-active i,
[data-type='textarea'] .mce-btn.mce-active:hover i {
	color: #000;
}

/* Select */
select.wsf-field:not([multiple]):not([size]) {
	background-image: url('data:image/svg+xml,<svg%20width%3D"10"%20height%3D"5"%20viewBox%3D"169%20177%2010%205"%20xmlns%3D"http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg"><path%20fill%3D"<?php echo urlencode($form_color); ?>"%20fill-rule%3D"evenodd"%20d%3D"M174%20182l5-5h-10"%2F><%2Fsvg>');
	background-position: right <?php self::e($spacing_small . $unit_of_measurement); ?> center;
	background-repeat: no-repeat;
	background-size: <?php self::e($spacing_small . $unit_of_measurement . ' ' . $spacing_extra_small . $unit_of_measurement); ?>;
	-webkit-padding-end: <?php self::e((($form_spacing_horizontal * 2) + $spacing_small) . $unit_of_measurement); ?>;
	padding-inline-end: <?php self::e((($form_spacing_horizontal * 2) + $spacing_small) . $unit_of_measurement); ?>;
}

select.wsf-field:not([multiple]):not([size])::-ms-expand {
	display: none;
}

select.wsf-field option {
	font-weight: <?php self::e($font_weight); ?>;
}

<?php if ($form_hover) { ?>
<?php if ($form_hover_color != $form_color) { ?>
	select.wsf-field:not([multiple]):not([size]):hover {
		background-image: url('data:image/svg+xml,<svg%20width%3D"10"%20height%3D"5"%20viewBox%3D"169%20177%2010%205"%20xmlns%3D"http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg"><path%20fill%3D"<?php echo urlencode($form_hover_color); ?>"%20fill-rule%3D"evenodd"%20d%3D"M174%20182l5-5h-10"%2F><%2Fsvg>');
	}
<?php } ?>
<?php } ?>

<?php if ($form_focus) { ?>
<?php if ($form_focus_color != $form_color) { ?>
select.wsf-field:not([multiple]):not([size]):focus {
	background-image: url('data:image/svg+xml,<svg%20width%3D"10"%20height%3D"5"%20viewBox%3D"169%20177%2010%205"%20xmlns%3D"http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg"><path%20fill%3D"<?php echo urlencode($form_focus_color); ?>"%20fill-rule%3D"evenodd"%20d%3D"M174%20182l5-5h-10"%2F><%2Fsvg>');
}
<?php } ?>
<?php } ?>

select.wsf-field:not([multiple]):not([size]):-moz-focusring {
	color: transparent;
	text-shadow: 0 0 0 #000;
}

select.wsf-field:not([multiple]):not([size]):disabled {
<?php if ($form_disabled_color != $form_color) { ?>
	border-color: <?php self::e($form_disabled_border_color); ?>;
	background-image: url('data:image/svg+xml,<svg%20width%3D"10"%20height%3D"5"%20viewBox%3D"169%20177%2010%205"%20xmlns%3D"http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg"><path%20fill%3D"<?php echo urlencode($form_disabled_color); ?>"%20fill-rule%3D"evenodd"%20d%3D"M174%20182l5-5h-10"%2F><%2Fsvg>');
<?php } ?>
}

select.wsf-field optgroup {
	font-weight: bold;
}

<?php if ($form_disabled_color != $form_color) { ?>
select.wsf-field option:disabled {
	color: <?php self::e($form_disabled_color); ?>;
}
<?php } ?>


/* Checkbox */
input[type=checkbox].wsf-field {
	bottom: auto;
	height: <?php self::e($checkbox_size . $unit_of_measurement); ?>;
	left: auto;
	margin: 0;
	opacity: 0;
	position: absolute;
	right: auto;
	top: auto;
	width: <?php self::e($checkbox_size . $unit_of_measurement); ?>;
}

input[type=checkbox].wsf-field + label.wsf-label {
	display: inline-block;
	margin: 0 0 <?php self::e($spacing_small . $unit_of_measurement); ?>;
	-webkit-padding-start: <?php self::e(($checkbox_size + $spacing_extra_small) . $unit_of_measurement); ?>;
	padding-inline-start: <?php self::e(($checkbox_size + $spacing_extra_small) . $unit_of_measurement); ?>;
	position: relative;
<?php if ($form_transition) { ?>
	transition: color <?php self::e($form_transition_speed); ?>;
<?php } ?>
}

input[type=checkbox].wsf-field + label.wsf-label:before {
	background-color: <?php self::e($form_background_color); ?>;
<?php if ($form_border) { ?>
	border: <?php self::e($form_border_width . $unit_of_measurement . ' ' . $form_border_style . ' ' . $form_border_color); ?>;
<?php } ?>
<?php if ($form_border_radius > 0) { ?>
	border-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
<?php } ?>
	content: '';
	cursor: pointer;
	display: inline-block;
	height: <?php self::e($checkbox_size . $unit_of_measurement); ?>;
	left: 0;
	position: absolute;
<?php if ($form_transition) { ?>
	transition: background-color <?php self::e($form_transition_speed); ?>, border-color <?php self::e($form_transition_speed); ?>, box-shadow <?php self::e($form_transition_speed); ?>;
<?php } ?>
	vertical-align: top;
	width: <?php self::e($checkbox_size . $unit_of_measurement); ?>;
}

input[type=checkbox].wsf-field + label.wsf-label:after {
	content: '';
	cursor: pointer;
	display: inline-block;
	height: <?php self::e($checkbox_size . $unit_of_measurement); ?>;
	left: 0;
	position: absolute;
	top: 0;
	vertical-align: top;
	width: <?php self::e($checkbox_size . $unit_of_measurement); ?>;
}

input[type=checkbox].wsf-field + label.wsf-label + .wsf-invalid-feedback {
	margin-bottom: <?php self::e($spacing_small . $unit_of_measurement); ?>;
	margin-top: -<?php self::e($spacing_extra_small . $unit_of_measurement); ?>;
}

<?php if ($form_hover) { ?>
input[type=checkbox].wsf-field:enabled:hover + label.wsf-label:before {
<?php if ($form_hover_background_color != $form_background_color) { ?>
	background-color: <?php self::e($form_hover_background_color); ?>;
<?php } ?>
<?php if ($form_hover_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_hover_border_colo); ?>
<?php } ?>
}
<?php } ?>

<?php if ($form_focus) { ?>
input[type=checkbox].wsf-field:focus + label.wsf-label:before {
<?php if ($form_focus_background_color != $form_background_color) { ?>
	background-color: <?php self::e($form_focus_background_color); ?>;
<?php } ?>
<?php if ($form_focus_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_focus_border_color); ?>;
<?php } ?>
<?php if ($form_box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($color_primary, 85)); ?>;
<?php } ?>
}
<?php } ?>

input[type=checkbox].wsf-field:disabled + label.wsf-label {
<?php if ($form_disabled_color != $form_color) { ?>
	color: <?php self::e($form_disabled_color); ?>;
<?php } ?>
}

input[type=checkbox].wsf-field:disabled + label.wsf-label:before {
<?php if ($form_disabled_background_color != $form_background_color) { ?>
	background-color: <?php self::e($form_disabled_background_color); ?>;
<?php } ?>
<?php if ($form_border) { ?>
<?php if ($form_disabled_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_disabled_border_color); ?>;
<?php } ?>
<?php } ?>
	cursor: not-allowed;
}

input[type=checkbox].wsf-field:disabled + label.wsf-label:after {
	cursor: not-allowed;
}

input[type=checkbox].wsf-field:checked + label.wsf-label:before {
	background-color: <?php self::e($form_checked_color); ?>;
	border-color: <?php self::e($form_checked_color); ?>;
}

input[type=checkbox].wsf-field:checked + label.wsf-label:after {
	background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='<?php echo urlencode($form_background_color); ?>' d='M6.564.75l-3.59 3.612-1.538-1.55L0 4.26l2.974 2.99L8 2.193z'/%3e%3c/svg%3e");
	background-position: 50%;
	background-size: 50%;
	background-repeat: no-repeat;
}

input[type=checkbox].wsf-field:checked:disabled + label.wsf-label:before {
	opacity: .5;
}

/* Radio */
input[type=radio].wsf-field {
	bottom: auto;
	height: <?php self::e($radio_size . $unit_of_measurement); ?>;
	left: auto;
	margin: 0;
	opacity: 0;
	position: absolute;
	right: auto;
	top: auto;
	width: <?php self::e($radio_size . $unit_of_measurement); ?>;
}

input[type=radio].wsf-field + label.wsf-label {
	display: inline-block;
	margin: 0 0 <?php self::e($spacing_small . $unit_of_measurement); ?>;
	-webkit-padding-start: <?php self::e(($radio_size + $spacing_extra_small) . $unit_of_measurement); ?>;
	padding-inline-start: <?php self::e(($radio_size + $spacing_extra_small) . $unit_of_measurement); ?>;
	position: relative;
<?php if ($form_transition) { ?>
	transition: color <?php self::e($form_transition_speed); ?>;
<?php } ?>
}

input[type=radio].wsf-field + label.wsf-label:before {
	background-color: <?php self::e($form_background_color); ?>;
<?php if ($form_border) { ?>
	border: <?php self::e($form_border_width . $unit_of_measurement . ' ' . $form_border_style . ' ' . $form_border_color); ?>;
<?php } ?>
	border-radius: 50%;
	content: '';
	cursor: pointer;
	display: inline-block;
	height: <?php self::e($radio_size . $unit_of_measurement); ?>;
	left: 0;
	position: absolute;
<?php if ($form_transition) { ?>
	transition: background-color <?php self::e($form_transition_speed); ?>, border-color <?php self::e($form_transition_speed); ?>, box-shadow <?php self::e($form_transition_speed); ?>;
<?php } ?>
	vertical-align: top;
	width: <?php self::e($radio_size . $unit_of_measurement); ?>;
}

input[type=radio].wsf-field + label.wsf-label:after {
	content: '';
	cursor: pointer;
	display: inline-block;
	height: <?php self::e($checkbox_size . $unit_of_measurement); ?>;
	left: 0;
	position: absolute;
	top: 0;
	vertical-align: top;
	width: <?php self::e($checkbox_size . $unit_of_measurement); ?>;
}

input[type=radio].wsf-field + label.wsf-label + .wsf-invalid-feedback {
	margin-bottom: <?php self::e($spacing_small . $unit_of_measurement); ?>;
	margin-top: -<?php self::e($spacing_extra_small . $unit_of_measurement); ?>;
}

<?php if ($form_hover) { ?>
input[type=radio].wsf-field:enabled:hover + label.wsf-label:before {
<?php if ($form_hover_background_color != $form_background_color) { ?>
	background-color: <?php self::e($form_hover_background_color); ?>;
<?php } ?>
<?php if ($form_hover_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_hover_border_colo); ?>
<?php } ?>
}
<?php } ?>

<?php if ($form_focus) { ?>
input[type=radio].wsf-field:focus + label.wsf-label:before {
<?php if ($form_focus_background_color != $form_background_color) { ?>
	background-color: <?php self::e($form_focus_background_color); ?>;
<?php } ?>
<?php if ($form_focus_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_focus_border_color); ?>;
<?php } ?>
<?php if ($form_box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($color_primary, 85)); ?>;
<?php } ?>
}
<?php } ?>

input[type=radio].wsf-field:disabled + label.wsf-label {
<?php if ($form_disabled_color != $form_color) { ?>
	color: <?php self::e($form_disabled_color); ?>;
<?php } ?>
}

input[type=radio].wsf-field:disabled + label.wsf-label:before {
<?php if ($form_disabled_background_color != $form_background_color) { ?>
	background-color: <?php self::e($form_disabled_background_color); ?>;
<?php } ?>
<?php if ($form_border) { ?>
<?php if ($form_disabled_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_disabled_border_color); ?>;
<?php } ?>
<?php } ?>
	cursor: not-allowed;
}

input[type=radio].wsf-field:disabled + label.wsf-label:after {
	cursor: not-allowed;
}

input[type=radio].wsf-field:checked + label.wsf-label:before {
	background-color: <?php self::e($form_checked_color); ?>;
	border-color: <?php self::e($form_checked_color); ?>;
}

input[type=radio].wsf-field:checked + label.wsf-label:after {
	background-image: url('data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="-4 -4 8 8"%3e%3ccircle r="2" fill="<?php echo urlencode($form_background_color); ?>"/%3e%3c/svg%3e');
	background-position: 50%;
	background-size: contain;
	background-repeat: no-repeat;
}

input[type=radio].wsf-field:checked:disabled + label.wsf-label:before {
	opacity: .5;
}

input[type=checkbox].wsf-field.wsf-switch,
input[type=radio].wsf-field.wsf-switch {
	width: <?php self::e((($checkbox_size * 2) - ($form_border_width * 4)) . $unit_of_measurement); ?>;
}

input[type=checkbox].wsf-field.wsf-switch + label.wsf-label,
input[type=radio].wsf-field.wsf-switch + label.wsf-label {
	-webkit-padding-start: <?php self::e((($checkbox_size * 2) - ($form_border_width * 4)  + $spacing_extra_small) . $unit_of_measurement); ?>;
	padding-inline-start: <?php self::e((($checkbox_size * 2) - ($form_border_width * 4)  + $spacing_extra_small) . $unit_of_measurement); ?>;
	position: relative;
}

input[type=checkbox].wsf-field.wsf-switch + label.wsf-label:before,
input[type=radio].wsf-field.wsf-switch + label.wsf-label:before {
	border-radius: <?php self::e(($checkbox_size / 2) + ($form_border_width * 2) . $unit_of_measurement); ?>;
	position: absolute;
	width: <?php self::e((($checkbox_size * 2) - ($form_border_width * 4)) . $unit_of_measurement); ?>;
}

input[type=checkbox].wsf-field.wsf-switch + label.wsf-label:after,
input[type=radio].wsf-field.wsf-switch + label.wsf-label:after {
	background-color: <?php self::e($form_border_color); ?>;
	border-radius: 50%;
	height: <?php self::e(($checkbox_size - ($form_border_width * 4)) . $unit_of_measurement); ?>;
	left: <?php self::e(($form_border_width * 2). $unit_of_measurement); ?>;
	top: <?php self::e(($form_border_width * 2). $unit_of_measurement); ?>;
<?php if ($form_transition) { ?>
	transition: background-color <?php self::e($form_transition_speed); ?>, border-color <?php self::e($form_transition_speed); ?>, left <?php self::e($form_transition_speed); ?>;
<?php } ?>
	width: <?php self::e(($checkbox_size - ($form_border_width * 4)) . $unit_of_measurement); ?>;
}

<?php if ($form_hover) { ?>
input[type=checkbox].wsf-field.wsf-switch:enabled:hover + label.wsf-label:after,
input[type=radio].wsf-field.wsf-switch:enabled:hover + label.wsf-label:after {
<?php if ($form_hover_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_hover_border_colo); ?>
<?php } ?>
}
<?php } ?>

<?php if ($form_focus) { ?>
input[type=checkbox].wsf-field.wsf-switch:focus + label.wsf-label:after,
input[type=radio].wsf-field.wsf-switch:focus + label.wsf-label:after {
<?php if ($form_focus_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_focus_border_color); ?>;
<?php } ?>
}
<?php } ?>

input[type=checkbox].wsf-field.wsf-switch:disabled + label.wsf-label:after,
input[type=radio].wsf-field.wsf-switch:disabled + label.wsf-label:after {
<?php if ($form_border) { ?>
<?php if ($form_disabled_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_disabled_border_color); ?>;
<?php } ?>
<?php } ?>
}

input[type=checkbox].wsf-field.wsf-switch:checked + label.wsf-label:before,
input[type=radio].wsf-field.wsf-switch:checked + label.wsf-label:before {
	background-color: <?php self::e($form_checked_color); ?>;
}

input[type=checkbox].wsf-field.wsf-switch:checked + label.wsf-label:after,
input[type=radio].wsf-field.wsf-switch:checked + label.wsf-label:after {
	background-color: <?php self::e($form_background_color); ?>;
	background-image: none;
	border-color: <?php self::e($form_background_color); ?>;
	left: <?php self::e(($checkbox_size - ($form_border_width * 2)) . $unit_of_measurement); ?>
}

input[type=checkbox].wsf-field.wsf-button + label.wsf-label,
input[type=radio].wsf-field.wsf-button + label.wsf-label {
  	background-color: <?php self::e($color_default_lighter); ?>;
<?php if ($form_border) { ?>
  	border: <?php self::e($form_border_width . $unit_of_measurement . ' ' . $form_border_style . ' ' . $form_border_color); ?>;
<?php } else { ?>
  	border: none;
<?php } ?>
<?php if ($form_border_radius > 0) { ?>
  	border-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
<?php } ?>
  	color: <?php self::e($form_color); ?>;
  	cursor: pointer;
  	display: inline-block;
  	font-family: <?php self::e($font_family); ?>;
  	font-size: <?php self::e($form_font_size . $unit_of_measurement); ?>;
  	font-weight: <?php self::e($font_weight); ?>;
  	height: <?php self::e($input_height . $unit_of_measurement); ?>;
  	line-height: <?php self::e($line_height); ?>;
  	padding: <?php self::e($form_spacing_vertical . $unit_of_measurement . ' ' . $form_spacing_horizontal . $unit_of_measurement); ?>;
  	margin: 0 0 <?php self::e(($grid_gutter / 2) . $unit_of_measurement); ?>;
  	text-align: center;
  	text-decoration: none;
  	touch-action: manipulation;
<?php if ($form_transition) { ?>
  	transition: background-color <?php self::e($form_transition_speed); ?>, border-color <?php self::e($form_transition_speed); ?>, box-shadow <?php self::e($form_transition_speed); ?>, color <?php self::e($form_transition_speed); ?>;
<?php } ?>
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
  	vertical-align: middle;
}

input[type=checkbox].wsf-field.wsf-button + label.wsf-label:after,
input[type=radio].wsf-field.wsf-button + label.wsf-label:after {
	display: none;
}

input[type=checkbox].wsf-field.wsf-button.wsf-button-full + label.wsf-label,
input[type=radio].wsf-field.wsf-button.wsf-button-full + label.wsf-label {
	display: block;
}

input[type=checkbox].wsf-field.wsf-button + label.wsf-label:before,
input[type=radio].wsf-field.wsf-button + label.wsf-label:before {
	display: none;
}

input[type=checkbox].wsf-field.wsf-button:disabled + label.wsf-label,
input[type=radio].wsf-field.wsf-button:disabled + label.wsf-label {
	cursor: not-allowed;
	opacity: .5;
}

input[type=checkbox].wsf-field.wsf-button:checked + label.wsf-label,
input[type=radio].wsf-field.wsf-button:checked + label.wsf-label {
	background-color: <?php self::e($color_primary); ?>;
	border-color: <?php self::e($color_primary); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}

input[type=checkbox].wsf-field.wsf-color,
input[type=radio].wsf-field.wsf-color {
	height: <?php self::e($color_size . $unit_of_measurement); ?>;
	width: <?php self::e($color_size . $unit_of_measurement); ?>;
}

input[type=checkbox].wsf-field.wsf-color + label.wsf-label,
input[type=radio].wsf-field.wsf-color + label.wsf-label {
	margin-left: 0;
	padding-left: 0;
	position: relative;
}

input[type=checkbox].wsf-field.wsf-color + label.wsf-label:before,
input[type=radio].wsf-field.wsf-color + label.wsf-label:before {
	display: none;
}

input[type=checkbox].wsf-field.wsf-color + label.wsf-label:after,
input[type=radio].wsf-field.wsf-color + label.wsf-label:after {
	display: none;
}

input[type=checkbox].wsf-field.wsf-color + label.wsf-label > span,
input[type=radio].wsf-field.wsf-color + label.wsf-label > span {
<?php if ($form_border) { ?>
	border: <?php self::e($form_border_width . $unit_of_measurement . ' ' . $form_border_style . ' ' . $form_border_color); ?>;
<?php } ?>
<?php if ($form_border_radius > 0) { ?>
	border-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
<?php } ?>
	cursor: pointer;
	display: inline-block;
	height: <?php self::e($color_size . $unit_of_measurement); ?>;
<?php if ($form_transition) { ?>
	transition: border-color <?php self::e($form_transition_speed); ?>, box-shadow <?php self::e($form_transition_speed); ?>;
<?php } ?>
	vertical-align: middle;
	width: <?php self::e($color_size . $unit_of_measurement); ?>;
}

input[type=checkbox].wsf-field.wsf-color.wsf-circle + label.wsf-label > span,
input[type=radio].wsf-field.wsf-color.wsf-circle + label.wsf-label > span {
	border-radius: 50%;
}

<?php if ($form_hover) { ?>
input[type=checkbox].wsf-field.wsf-color:enabled:hover + label.wsf-label > span,
input[type=radio].wsf-field.wsf-color:enabled:hover + label.wsf-label > span {
<?php if ($form_hover_background_color != $form_background_color) { ?>
	background-color: <?php self::e($form_hover_background_color); ?>;
<?php } ?>
<?php if ($form_hover_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_hover_border_colo); ?>
<?php } ?>
}
<?php } ?>

<?php if ($form_focus) { ?>
input[type=checkbox].wsf-field.wsf-color:focus + label.wsf-label > span,
input[type=radio].wsf-field.wsf-color:focus + label.wsf-label > span {
<?php if ($form_focus_background_color != $form_background_color) { ?>
	background-color: <?php self::e($form_focus_background_color); ?>;
<?php } ?>
<?php if ($form_focus_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_focus_border_color); ?>;
<?php } ?>
<?php if ($form_box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($color_primary, 85)); ?>;
<?php } ?>
}
<?php } ?>

input[type=checkbox].wsf-field.wsf-color:disabled + label.wsf-label > span,
input[type=radio].wsf-field.wsf-color:disabled + label.wsf-label > span {
	cursor: not-allowed;
	opacity: .5;
}

input[type=checkbox].wsf-field.wsf-color:checked + label.wsf-label > span,
input[type=radio].wsf-field.wsf-color:checked + label.wsf-label > span {
	border-color: <?php self::e($form_checked_color); ?>;
	box-shadow: inset 0 0 0 2px <?php self::e($color_default_inverted); ?>;
}

input[type=checkbox].wsf-field.wsf-image + label.wsf-label,
input[type=radio].wsf-field.wsf-image + label.wsf-label {
	margin-left: 0;
	padding-left: 0;
	position: relative;
}

input[type=checkbox].wsf-field.wsf-image + label.wsf-label:before,
input[type=radio].wsf-field.wsf-image + label.wsf-label:before {
	display: none;
}

input[type=checkbox].wsf-field.wsf-image + label.wsf-label:after,
input[type=radio].wsf-field.wsf-image + label.wsf-label:after {
	display: none;
}

input[type=checkbox].wsf-field.wsf-image + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image + label.wsf-label > img {
	background-color: <?php self::e($form_background_color); ?>;
<?php if ($form_border) { ?>
	border: <?php self::e($form_border_width . $unit_of_measurement . ' ' . $form_border_style . ' ' . $form_border_color); ?>;
<?php } ?>
<?php if ($form_border_radius > 0) { ?>
	border-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
<?php } ?>
	cursor: pointer;
	display: inline-block;
	height: auto;
	max-width: 100%;
	padding: 2px;
<?php if ($form_transition) { ?>
	transition: background-color <?php self::e($form_transition_speed); ?>, border-color <?php self::e($form_transition_speed); ?>, box-shadow <?php self::e($form_transition_speed); ?>;
<?php } ?>
	vertical-align: middle;
}

input[type=checkbox].wsf-field.wsf-image.wsf-circle + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image.wsf-circle + label.wsf-label > img {
	border-radius: 50%;
}

input[type=checkbox].wsf-field.wsf-image.wsf-responsive + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image.wsf-responsive + label.wsf-label > img {
	height: auto;
	max-width: 100%;
	width: 100%; 
}

input[type=checkbox].wsf-field.wsf-image.wsf-image-full + label.wsf-label,
input[type=radio].wsf-field.wsf-image.wsf-image-full + label.wsf-label {
	width: 100%;
}

<?php if ($form_hover) { ?>
input[type=checkbox].wsf-field.wsf-image:enabled:hover + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image:enabled:hover + label.wsf-label > img {
<?php if ($form_hover_background_color != $form_background_color) { ?>
	background-color: <?php self::e($form_hover_background_color); ?>;
<?php } ?>
<?php if ($form_hover_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_hover_border_colo); ?>
<?php } ?>
}
<?php } ?>

<?php if ($form_focus) { ?>
input[type=checkbox].wsf-field.wsf-image:focus + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image:focus + label.wsf-label > img {
<?php if ($form_focus_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_focus_border_color); ?>;
<?php } ?>
<?php if ($form_box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($color_primary, 85)); ?>;
<?php } ?>
}
<?php } ?>

input[type=checkbox].wsf-field.wsf-image:disabled + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image:disabled + label.wsf-label > img {
	cursor: not-allowed;
	opacity: .5;
}

input[type=checkbox].wsf-field.wsf-image:checked + label.wsf-label > img,
input[type=radio].wsf-field.wsf-image:checked + label.wsf-label > img {
	background-color: <?php self::e($form_checked_color); ?>;
	border-color: <?php self::e($form_checked_color); ?>;
}

.wsf-image-caption {
	color: <?php self::e($form_help_color); ?>;
	display: block;
	font-size: <?php self::e($form_font_size_small . $unit_of_measurement); ?>;
	line-height: <?php self::e($line_height); ?>;
	margin-top: <?php self::e($spacing_extra_small . $unit_of_measurement); ?>;
}


/* Validation */
.wsf-validated input[type=email].wsf-field:invalid,
.wsf-validated input[type=number].wsf-field:invalid,
.wsf-validated input[type=tel].wsf-field:invalid,
.wsf-validated input[type=text].wsf-field:invalid,
.wsf-validated input[type=url].wsf-field:invalid,
.wsf-validated select.wsf-field:invalid,
.wsf-validated textarea.wsf-field:invalid {
<?php if ($form_error_background_color != $form_background_color) { ?>
	background-color: <?php self::e($form_error_background_color); ?>;
<?php } ?>
<?php if ($form_border) { ?>
<?php if ($form_error_border_color != $form_border_color) { ?>
	border-color: <?php self::e($form_error_border_color); ?>;
<?php } ?>
<?php } ?>
<?php if ($form_error_border_color != $form_color) { ?>
	color: <?php self::e($form_error_color); ?>;
<?php } ?>
}

<?php if ($form_focus) { ?>
<?php if ($form_box_shadow) { ?>
.wsf-validated input[type=email].wsf-field:invalid:focus,
.wsf-validated input[type=number].wsf-field:invalid:focus,
.wsf-validated input[type=tel].wsf-field:invalid:focus,
.wsf-validated input[type=text].wsf-field:invalid:focus,
.wsf-validated input[type=url].wsf-field:invalid:focus,
.wsf-validated select.wsf-field:invalid:focus,
.wsf-validated textarea.wsf-field:invalid:focus {
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($form_error_border_color, 85)); ?>;
}
<?php } ?>
<?php } ?>

.wsf-validated input[type=email].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=number].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=tel].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=text].wsf-field:-moz-ui-invalid,
.wsf-validated input[type=url].wsf-field:-moz-ui-invalid,
.wsf-validated select.wsf-field:-moz-ui-invalid,
.wsf-validated textarea.wsf-field:-moz-ui-invalid {
	box-shadow: none;
}

<?php if ($form_error_color != $form_color) { ?>
.wsf-validated select.wsf-field:not([multiple]):not([size]):invalid {
	background-image: url('data:image/svg+xml,<svg%20width%3D"10"%20height%3D"5"%20viewBox%3D"169%20177%2010%205"%20xmlns%3D"http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg"><path%20fill%3D"<?php echo urlencode($form_error_color); ?>"%20fill-rule%3D"evenodd"%20d%3D"M174%20182l5-5h-10"%2F><%2Fsvg>');
}
<?php } ?>


<?php if ($form_border) { ?>
<?php if ($form_error_border_color != $form_border_color) { ?>
.wsf-validated input[type=checkbox].wsf-field:invalid + label.wsf-label:before,
.wsf-validated input[type=radio].wsf-field:invalid + label.wsf-label:before {
	border-color: <?php self::e($form_error_border_color); ?>;
}
<?php } ?>
<?php } ?>

<?php if ($form_focus) { ?>
<?php if ($form_box_shadow) { ?>
.wsf-validated input[type=checkbox].wsf-field:invalid:focus + label.wsf-label:before,
.wsf-validated input[type=radio].wsf-field:invalid:focus + label.wsf-label:before {
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($form_error_border_color, 85)); ?>;
}
<?php } ?>
<?php } ?>

/* Message */
.wsf-alert {
	background-color: <?php self::e($color_default_lightest); ?>;
<?php if ($form_border) { ?>
	-webkit-border-start: 4px <?php self::e($form_border_style . ' ' . $form_border_color); ?>;
	border-inline-start: 4px <?php self::e($form_border_style . ' ' . $form_border_color); ?>;
<?php } ?>
<?php if ($form_border_radius > 0) { ?>
	border-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
<?php } ?>
	font-family: <?php self::e($font_family); ?>;
	font-size: <?php self::e($form_font_size . $unit_of_measurement); ?>;
	font-weight: <?php self::e($font_weight); ?>;
	line-height: <?php self::e($line_height); ?>;
	padding: <?php self::e($spacing_small . $unit_of_measurement); ?>;
	margin-bottom: <?php self::e($grid_gutter . $unit_of_measurement); ?>;
}

.wsf-alert a {
	text-decoration: underline;
}

.wsf-alert > :first-child {
	margin-top: 0;
}

.wsf-alert > :last-child {
	margin-bottom: 0;
}

.wsf-alert.wsf-alert-success {
	background-color: <?php self::e(WS_Form_Common::hex_lighten_percentage($color_success, 85)); ?>;
<?php if ($form_border) { ?>
	border-color: <?php self::e(WS_Form_Common::hex_lighten_percentage($color_success, 40)); ?>;
<?php } ?>
	color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_success, 40)); ?>;
}

.wsf-alert.wsf-alert-success a,
.wsf-alert.wsf-alert-success a:hover,
.wsf-alert.wsf-alert-success a:focus {
	color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_success, 60)); ?>;
}

.wsf-alert.wsf-alert-information {
	background-color: <?php self::e(WS_Form_Common::hex_lighten_percentage(esc_html($color_information), 85)); ?>;
<?php if ($form_border) { ?>
	border-color: <?php self::e(WS_Form_Common::hex_lighten_percentage(esc_html($color_information), 40)); ?>;
<?php } ?>
	color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_information, 40)); ?>;
}

.wsf-alert.wsf-alert-information a,
.wsf-alert.wsf-alert-information a:hover,
.wsf-alert.wsf-alert-information a:focus {
	color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_information, 60)); ?>;
}

.wsf-alert.wsf-alert-warning {
	background-color: <?php self::e(WS_Form_Common::hex_lighten_percentage($color_warning, 85)); ?>;
<?php if ($form_border) { ?>
	border-color: <?php self::e(WS_Form_Common::hex_lighten_percentage($color_warning, 40)); ?>;
<?php } ?>
	color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_warning, 40)); ?>;
}

.wsf-alert.wsf-alert-warning a,
.wsf-alert.wsf-alert-warning a:hover,
.wsf-alert.wsf-alert-warning a:focus {
	color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_warning, 60)); ?>;
}

.wsf-alert.wsf-alert-danger {
	background-color: <?php self::e(WS_Form_Common::hex_lighten_percentage($color_danger, 85)); ?>;
<?php if ($form_border) { ?>
	border-color: <?php self::e(WS_Form_Common::hex_lighten_percentage($color_danger, 40)); ?>;
<?php } ?>
	color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_danger, 40)); ?>;
}

.wsf-alert.wsf-alert-danger a,
.wsf-alert.wsf-alert-danger a:hover,
.wsf-alert.wsf-alert-danger a:focus {
	color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_danger, 60)); ?>;
}

/* Button */
button.wsf-button {
	-webkit-appearance: none;
	background-color: <?php self::e($color_default_lighter); ?>;
<?php if ($form_border) { ?>
	border: <?php self::e($form_border_width . $unit_of_measurement . ' ' . $form_border_style . ' ' . $form_border_color); ?>;
<?php } else { ?>
	border: none;
<?php } ?>
<?php if ($form_border_radius > 0) { ?>
	border-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
<?php } ?>
	color: <?php self::e($form_color); ?>;
	cursor: pointer;
	display: inline-block;
	font-family: <?php self::e($font_family); ?>;
	font-size: <?php self::e($form_font_size . $unit_of_measurement); ?>;
	font-weight: <?php self::e($font_weight); ?>;
	height: <?php self::e($input_height . $unit_of_measurement); ?>;
	line-height: <?php self::e($line_height); ?>;
	padding: <?php self::e($form_spacing_vertical . $unit_of_measurement . ' ' . $form_spacing_horizontal . $unit_of_measurement); ?>;
	margin: 0;
	text-align: center;
	text-decoration: none;
	touch-action: manipulation;
<?php if ($form_transition) { ?>
	transition: background-color <?php self::e($form_transition_speed); ?>, border-color <?php self::e($form_transition_speed); ?>, box-shadow <?php self::e($form_transition_speed); ?>;
<?php } ?>
	-webkit-user-select: none;
	-moz-user-select: none;
	-ms-user-select: none;
	user-select: none;
	vertical-align: middle;
}

button.wsf-button.wsf-button-full {
	width: 100%;
}

<?php if ($form_hover) { ?>
button.wsf-button:hover {
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage($form_border_color, 10)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage($form_border_color, 10)); ?>;
}
<?php } ?>

button.wsf-button:focus,
button.wsf-button:active {
<?php if ($form_focus) { ?>
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage($form_border_color, 20)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage($form_border_color, 20)); ?>;
<?php if ($form_box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($form_border_color, 85)); ?>;
<?php } ?>
<?php } ?>
	outline: 0;
}

button.wsf-button:disabled {
	background-color: <?php self::e($color_default_lighter); ?>;
	border-color: <?php self::e($form_border_color); ?>;
}

button.wsf-button.wsf-button-primary {
	background-color: <?php self::e($color_primary); ?>;
	border-color: <?php self::e($color_primary); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}

<?php if ($form_hover) { ?>
button.wsf-button.wsf-button-primary:hover {
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_primary, 10)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_primary, 10)); ?>;
}
<?php } ?>

<?php if ($form_focus) { ?>
button.wsf-button.wsf-button-primary:focus,
button.wsf-button.wsf-button-primary:active {
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_primary, 20)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_primary, 20)); ?>;
<?php if ($form_box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($color_primary, 85)); ?>;
<?php } ?>
}
<?php } ?>

button.wsf-button.wsf-button-primary:disabled {
	background-color: <?php self::e($color_primary); ?>;
	border-color: <?php self::e($color_primary); ?>;
}

button.wsf-button.wsf-button-secondary {
	background-color: <?php self::e($color_secondary); ?>;
	border-color: <?php self::e($color_secondary); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}

<?php if ($form_hover) { ?>
button.wsf-button.wsf-button-secondary:hover {
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_secondary, 10)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_secondary, 10)); ?>;
}
<?php } ?>

<?php if ($form_focus) { ?>
button.wsf-button.wsf-button-secondary:focus,
button.wsf-button.wsf-button-secondary:active {
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_secondary, 20)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_secondary, 20)); ?>;
<?php if ($form_box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($color_secondary, 85)); ?>;
<?php } ?>
}
<?php } ?>

button.wsf-button.wsf-button-secondary:disabled {
	background-color: <?php self::e($color_secondary); ?>;
	border-color: <?php self::e($color_secondary); ?>;
}

button.wsf-button.wsf-button-success {
	background-color: <?php self::e($color_success); ?>;
	border-color: <?php self::e($color_success); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}

<?php if ($form_hover) { ?>
button.wsf-button.wsf-button-success:hover {
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_success, 10)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_success, 10)); ?>;
}
<?php } ?>

<?php if ($form_focus) { ?>
button.wsf-button.wsf-button-success:focus,
button.wsf-button.wsf-button-success:active {
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_success, 20)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_success, 20)); ?>;
<?php if ($form_box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($color_success, 85)); ?>;
<?php } ?>
}
<?php } ?>

button.wsf-button.wsf-button-success:disabled {
	background-color: <?php self::e($color_success); ?>;
	border-color: <?php self::e($color_success); ?>;
}

button.wsf-button.wsf-button-information {
	background-color: <?php self::e($color_information); ?>;
	border-color: <?php self::e($color_information); ?>;
	color: <?php self::e($color_default); ?>;
}

<?php if ($form_hover) { ?>
button.wsf-button.wsf-button-information:hover {
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage(esc_html($color_information), 10)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage(esc_html($color_information), 10)); ?>;
}
<?php } ?>

<?php if ($form_focus) { ?>
button.wsf-button.wsf-button-information:focus,
button.wsf-button.wsf-button-information:active {
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage(esc_html($color_information), 20)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage(esc_html($color_information), 20)); ?>;
<?php if ($form_box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($color_information, 85)); ?>;
<?php } ?>
}
<?php } ?>

button.wsf-button.wsf-button-information:disabled {
	background-color: <?php self::e($color_information); ?>;
	border-color: <?php self::e($color_information); ?>;
}

button.wsf-button.wsf-button-warning {
	background-color: <?php self::e($color_warning); ?>;
	border-color: <?php self::e($color_warning); ?>;
	color: <?php self::e($color_default); ?>;
}

<?php if ($form_hover) { ?>
button.wsf-button.wsf-button-warning:hover {
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_warning, 10)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_warning, 10)); ?>;
}
<?php } ?>

<?php if ($form_focus) { ?>
button.wsf-button.wsf-button-warning:focus,
button.wsf-button.wsf-button-warning:active {
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_warning, 20)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_warning, 20)); ?>;
<?php if ($form_box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($color_warning, 85)); ?>;
<?php } ?>
}
<?php } ?>

button.wsf-button.wsf-button-warning:disabled {
	background-color: <?php self::e($color_warning); ?>;
	border-color: <?php self::e($color_warning); ?>;
}

button.wsf-button.wsf-button-danger {
	background-color: <?php self::e($color_danger); ?>;
	border-color: <?php self::e($color_danger); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}

<?php if ($form_hover) { ?>
button.wsf-button.wsf-button-danger:hover {
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_danger, 10)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_danger, 10)); ?>;
}
<?php } ?>

<?php if ($form_focus) { ?>
button.wsf-button.wsf-button-danger:focus,
button.wsf-button.wsf-button-danger:active {
	background-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_danger, 20)); ?>;
	border-color: <?php self::e(WS_Form_Common::hex_darken_percentage($color_danger, 20)); ?>;
<?php if ($form_box_shadow) { ?>
	box-shadow: 0 0 0 <?php self::e($form_box_shadow_width . $unit_of_measurement); ?> <?php self::e(WS_Form_Common::hex_lighten_percentage($color_danger, 85)); ?>;
<?php } ?>
}
<?php } ?>

button.wsf-button.wsf-button-danger:disabled {
	background-color: <?php self::e($color_danger); ?>;
	border-color: <?php self::e($color_danger); ?>;
}

<?php if ($form_border) { ?>
button.wsf-button.wsf-button-inverted {
	background-color: <?php self::e($form_background_color); ?>;
	border-color: <?php self::e($form_border_color); ?>;
	color: <?php self::e($form_color); ?>;
<?php if ($form_transition) { ?>
	transition: background-color <?php self::e($form_transition_speed); ?>, color <?php self::e($form_transition_speed); ?>;
<?php } ?>
}

<?php if ($form_hover) { ?>
button.wsf-button.wsf-button-inverted:hover {
	background-color: <?php self::e($color_default_lighter); ?>;
}
<?php } ?>

<?php if ($form_focus) { ?>
button.wsf-button.wsf-button-inverted:focus,
button.wsf-button.wsf-button-inverted:active {
	background-color: <?php self::e($color_default_lighter); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted:disabled {
	background-color: <?php self::e($form_background_color); ?>;
}

button.wsf-button.wsf-button-inverted.wsf-button-primary {
	border-color: <?php self::e($color_primary); ?>;
	color: <?php self::e($color_primary); ?>;
}

<?php if ($form_hover) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-primary:hover {
	background-color: <?php self::e($color_primary); ?>;
	border-color: <?php self::e($color_primary); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}
<?php } ?>

<?php if ($form_focus) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-primary:focus {
	background-color: <?php self::e($color_primary); ?>;
	border-color: <?php self::e($color_primary); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted.wsf-button-primary:disabled {
	background-color: <?php self::e($form_background_color); ?>;
	border-color: <?php self::e($color_primary); ?>;
	color: <?php self::e($color_primary); ?>;
}

button.wsf-button.wsf-button-inverted.wsf-button-secondary {
	border-color: <?php self::e($color_secondary); ?>;
	color: <?php self::e($color_secondary); ?>;
}

<?php if ($form_hover) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-secondary:hover {
	background-color: <?php self::e($color_secondary); ?>;
	border-color: <?php self::e($color_secondary); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}
<?php } ?>

<?php if ($form_focus) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-secondary:focus {
	background-color: <?php self::e($color_secondary); ?>;
	border-color: <?php self::e($color_secondary); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted.wsf-button-secondary:disabled {
	background-color: <?php self::e($form_background_color); ?>;
	border-color: <?php self::e($color_secondary); ?>;
	color: <?php self::e($color_secondary); ?>;
}

button.wsf-button.wsf-button-inverted.wsf-button-success {
	border-color: <?php self::e($color_success); ?>;
	color: <?php self::e($color_success); ?>;
}

<?php if ($form_hover) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-success:hover {
	background-color: <?php self::e($color_success); ?>;
	border-color: <?php self::e($color_success); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}
<?php } ?>

<?php if ($form_focus) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-success:focus {
	background-color: <?php self::e($color_success); ?>;
	border-color: <?php self::e($color_success); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted.wsf-button-success:disabled {
	background-color: <?php self::e($form_background_color); ?>;
	border-color: <?php self::e($color_success); ?>;
	color: <?php self::e($color_success); ?>;
}

button.wsf-button.wsf-button-inverted.wsf-button-information {
	border-color: <?php self::e($color_information); ?>;
	color: <?php self::e($color_information); ?>;
}

<?php if ($form_hover) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-information:hover {
	background-color: <?php self::e($color_information); ?>;
	border-color: <?php self::e($color_information); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}
<?php } ?>

<?php if ($form_focus) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-information:focus {
	background-color: <?php self::e($color_information); ?>;
	border-color: <?php self::e($color_information); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted.wsf-button-information:disabled {
	background-color: <?php self::e($form_background_color); ?>;
	border-color: <?php self::e($color_information); ?>;
	color: <?php self::e($color_information); ?>;
}

button.wsf-button.wsf-button-inverted.wsf-button-warning {
	border-color: <?php self::e($color_warning); ?>;
	color: <?php self::e($color_warning); ?>;
}

<?php if ($form_hover) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-warning:hover {
	background-color: <?php self::e($color_warning); ?>;
	border-color: <?php self::e($color_warning); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}
<?php } ?>

<?php if ($form_focus) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-warning:focus {
	background-color: <?php self::e($color_warning); ?>;
	border-color: <?php self::e($color_warning); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted.wsf-button-warning:disabled {
	background-color: <?php self::e($form_background_color); ?>;
	border-color: <?php self::e($color_warning); ?>;
	color: <?php self::e($color_warning); ?>;
}

button.wsf-button.wsf-button-inverted.wsf-button-danger {
	border-color: <?php self::e($color_danger); ?>;
	color: <?php self::e($color_danger); ?>;
}

<?php if ($form_hover) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-danger:hover {
	background-color: <?php self::e($color_danger); ?>;
	border-color: <?php self::e($color_danger); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}
<?php } ?>

<?php if ($form_focus) { ?>
button.wsf-button.wsf-button-inverted.wsf-button-danger:focus {
	background-color: <?php self::e($color_danger); ?>;
	border-color: <?php self::e($color_danger); ?>;
	color: <?php self::e($color_default_inverted); ?>;
}
<?php } ?>

button.wsf-button.wsf-button-inverted.wsf-button-danger:disabled {
	background-color: <?php self::e($form_background_color); ?>;
	border-color: <?php self::e($color_danger); ?>;
	color: <?php self::e($color_danger); ?>;
}
<?php } ?>

button.wsf-button::-moz-focus-inner {
	border: 0;
	margin: 0;
	padding: 0;
}

button.wsf-button:disabled {
	cursor: not-allowed;
	opacity: .5;
	transition: none;
}

.wsf-form-post-lock-progress button[type="submit"].wsf-button {
	cursor: progress;
}

/* Helpers */
.wsf-text-primary {
	color: <?php self::e($color_primary); ?>;
}

.wsf-text-secondary {
	color: <?php self::e($color_secondary); ?>;
}

.wsf-text-success {
	color: <?php self::e($color_success); ?>;
}

.wsf-text-information {
	color: <?php self::e($color_information); ?>;
}

.wsf-text-warning {
	color: <?php self::e($color_warning); ?>;
}

.wsf-text-danger {
	color: <?php self::e($color_danger); ?>;
}

.wsf-text-left {
	text-align: left;
}

.wsf-text-center {
	text-align: center;
}

.wsf-text-right {
	text-align: right;
}

.wsf-hidden {
	display: none !important;
}

.wsf-label-position-inside input.wsf-field[placeholder]::placeholder,
.wsf-label-position-inside textarea.wsf-field[placeholder]::placeholder {
	color: transparent;
}

.wsf-label-position-inside select.wsf-field + label,
.wsf-label-position-inside input.wsf-field[placeholder] + label,
.wsf-label-position-inside textarea.wsf-field[placeholder] + label,
.wsf-label-position-inside select.wsf-field + .wsf-input-group-append + label,
.wsf-label-position-inside input.wsf-field[placeholder] + .wsf-input-group-append + label,
.wsf-label-position-inside textarea.wsf-field[placeholder] + .wsf-input-group-append + label {
	left: <?php self::e((($grid_gutter / 2) + $form_spacing_horizontal + $form_border_width) . $unit_of_measurement); ?>;
	line-height: <?php self::e($line_height); ?>;
	margin-top: 0;
	position: absolute;
	top: <?php self::e($form_spacing_vertical . $unit_of_measurement); ?>;;
	transform-origin: 0 0;
<?php if ($transition) { ?>
	transition: transform <?php self::e($transition_speed); ?>ms;
<?php } ?>
	user-select: none;
}

.wsf-label-position-inside select.wsf-field + label,
.wsf-label-position-inside input.wsf-field[placeholder]:focus + label,
.wsf-label-position-inside input.wsf-field[placeholder]:not(:placeholder-shown) + label,
.wsf-label-position-inside textarea.wsf-field[placeholder]:focus + label,
.wsf-label-position-inside textarea.wsf-field[placeholder]:not(:placeholder-shown) + label,
.wsf-label-position-inside select.wsf-field + .wsf-input-group-append + label,
.wsf-label-position-inside input.wsf-field[placeholder]:focus + .wsf-input-group-append + label,
.wsf-label-position-inside input.wsf-field[placeholder]:not(:placeholder-shown) + .wsf-input-group-append + label,
.wsf-label-position-inside textarea.wsf-field[placeholder]:focus + .wsf-input-group-append + label,
.wsf-label-position-inside textarea.wsf-field[placeholder]:not(:placeholder-shown) + .wsf-input-group-append + label {
<?php

	switch($label_position_inside_mode) {

		case 'move' :
?>
	background-color: <?php self::e($form_background_color); ?>;
	-webkit-margin-start: -<?php self::e(($form_font_size / 4) . $unit_of_measurement); ?>;
	margin-inline-start: -<?php self::e(($form_font_size / 4) . $unit_of_measurement); ?>;
	padding-left: <?php self::e(($form_font_size / 4) . $unit_of_measurement); ?>;
	padding-right: <?php self::e(($form_font_size / 4) . $unit_of_measurement); ?>;
	transform: translate(0, <?php self::e($label_column_inside_offset . $unit_of_measurement); ?>) scale(<?php self::e($label_column_inside_scale); ?>);
<?php
			break;

		default :
?>
	display: none;
<?php
	}
?>
}
<?php
		}

		// Skin - RTL
		public function render_skin_rtl() {

			// Customizer
			$enable_cache = !(WS_Form_Common::get_query_var('customize_theme') !== '');

			$customize_groups = WS_Form_Config::get_customize();

			// Set skin variables
			foreach($customize_groups as $customize_group) {

				foreach($customize_group['fields'] as $meta_key => $config) {

					$variable = str_replace('skin_', '', $meta_key);

					$default = isset($config['default']) ? $config['default'] : '';

					$type = isset($config['type']) ? $config['type'] : '';

					$$variable = WS_Form_Common::option_get($meta_key, $default, false, $enable_cache, true);
				}
			}

			// Advanced
			$unit_of_measurement = 'px';

			// Spacing
			$spacing = 20;
			$spacing_extra_large = 80;
			$spacing_large = 40;
			$spacing_small = 10;
			$spacing_extra_small = 5;

			// Forms
			$form_border = $border; // true | false
			$form_border_color = $color_default_lighter;
			$form_border_style = $border_style;
			$form_border_width = $border_width;
			$form_border_radius = $border_radius;
			$form_font_size = $font_size;
			$form_spacing_horizontal = $spacing_small;
			$form_spacing_vertical = ($spacing_small * .85);
			$form_transition = $transition; // true | false
			$form_transition_timing_function = $transition_timing_function;
			$form_transition_speed = $transition_speed . 'ms ' . $form_transition_timing_function;
			$input_height = ((round($form_font_size * $line_height) + ($form_spacing_vertical * 2)) + ($form_border_width * 2));
			$checkbox_size = round($form_font_size * $line_height);
			$radio_size = round($form_font_size * $line_height);
			$color_size = $input_height;
?>
<?php if ($form_border_radius > 0) { ?>
.wsf-input-group-has-prepend > .wsf-field,
.wsf-input-group-has-prepend > select.wsf-field ~ .select2-container .select2-selection--single,
.wsf-input-group-has-prepend > select.wsf-field ~ .select2-container .select2-selection--multiple,
.wsf-input-group-has-prepend > .dropzone {
	border-top-left-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?> !important;
	border-top-right-radius: 0 !important;
	border-bottom-left-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?> !important;
	border-bottom-right-radius: 0 !important;
}

.wsf-input-group-has-append > .wsf-field,
.wsf-input-group-has-append > select.wsf-field ~ .select2-container .select2-selection--single,
.wsf-input-group-has-append > select.wsf-field ~ .select2-container .select2-selection--multiple,
.wsf-input-group-has-append > .dropzone {
	border-top-left-radius: 0 !important;
	border-top-right-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?> !important;
	border-bottom-left-radius: 0 !important;
	border-bottom-right-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?> !important;
}
<?php } ?>

.wsf-input-group-has-prepend.wsf-input-group-has-append > .wsf-field,
.wsf-input-group-has-prepend.wsf-input-group-has-append > select.wsf-field ~ .select2-container .select2-selection--single,
.wsf-input-group-has-prepend.wsf-input-group-has-append > select.wsf-field ~ .select2-container .select2-selection--multiple,
.wsf-input-group-has-prepend.wsf-input-group-has-append > .dropzone {
	border-top-left-radius: 0 !important;
	border-top-right-radius: 0 !important;
	border-bottom-left-radius: 0 !important;
	border-bottom-right-radius: 0 !important;
}

.wsf-input-group-prepend {
<?php if ($form_border_radius > 0) { ?>
	border-top-left-radius: 0;
	border-top-right-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
	border-bottom-left-radius: 0;
	border-bottom-right-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
<?php } ?>
}

.wsf-input-group-append {
<?php if ($form_border_radius > 0) { ?>
	border-top-left-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
	border-top-right-radius: 0;
	border-bottom-left-radius: <?php self::e($form_border_radius . $unit_of_measurement); ?>;
	border-bottom-right-radius: 0;
<?php } ?>
}

select.wsf-field:not([multiple]):not([size]) {
	background-position: left <?php self::e($spacing_small . $unit_of_measurement); ?> center;
}


input[type=checkbox].wsf-field + label.wsf-label:before {
	left: auto;
	right: 0;
}

input[type=checkbox].wsf-field + label.wsf-label:after {
	left: auto;
	right: 0;
}

input[type=radio].wsf-field + label.wsf-label:before {
	left: auto;
	right: 0;
}

input[type=radio].wsf-field + label.wsf-label:after {
	left: auto;
	right: 0;
}

input[type=checkbox].wsf-field.wsf-switch + label.wsf-label:after,
input[type=radio].wsf-field.wsf-switch + label.wsf-label:after {
	left: auto;
	right: <?php self::e(($form_border_width * 2). $unit_of_measurement); ?>;
<?php if ($form_transition) { ?>
	transition: background-color <?php self::e($form_transition_speed); ?>, border-color <?php self::e($form_transition_speed); ?>, right <?php self::e($form_transition_speed); ?>;
<?php } ?>
}

input[type=checkbox].wsf-field.wsf-switch:checked + label.wsf-label:after,
input[type=radio].wsf-field.wsf-switch:checked + label.wsf-label:after {
	left: auto;
	right: <?php self::e(($checkbox_size - ($form_border_width * 2)) . $unit_of_measurement); ?>
}

}

.wsf-label-position-inside select.wsf-field + label,
.wsf-label-position-inside input.wsf-field[placeholder] + label,
.wsf-label-position-inside textarea.wsf-field[placeholder] + label,
.wsf-label-position-inside select.wsf-field + .wsf-input-group-append + label,
.wsf-label-position-inside input.wsf-field[placeholder] + .wsf-input-group-append + label,
.wsf-label-position-inside textarea.wsf-field[placeholder] + .wsf-input-group-append + label {
	left: auto;
	right: <?php self::e((($grid_gutter / 2) + $form_spacing_horizontal + $form_border_width) . $unit_of_measurement); ?>;
}

<?php
		}

		public function get_skin($css_minify = null, $force_build = false) {

			// Build CSS
			$css_return = '';

			// Minify
			if(is_null($css_minify)) {

				$css_minify = !SCRIPT_DEBUG;
			}

			// Initial build of compiled files
			$css_compile = WS_Form_Common::option_get('css_compile', false);
			if($css_compile && !$force_build) {

				if($css_minify) {

					$css_return = WS_Form_Common::option_get('css_public_skin_min');

				} else {

					$css_return = WS_Form_Common::option_get('css_public_skin');
				}

			} else {

				ob_start();
				self::render_skin();
				if(is_rtl()) {

					self::render_skin_rtl();
				}
				$css_return = ob_get_contents();
				ob_end_clean();

				// Apply filters
				$css_return = apply_filters('wsf_get_skin', $css_return);

				$css_return = $css_minify ? self::minify($css_return) : $css_return;
			}

			return $css_return;
		}

		public function inline($css) {

			// Output CSS
			return sprintf('<style>%s</style>', $css);
		}

		public function minify($css) {

			// Basic minify
			$css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css);
			$css = preg_replace('/\s{2,}/', ' ', $css);
			$css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
			$css = preg_replace('/;}/', '}', $css);
			$css = str_replace(array("\r\n","\r","\n","\t",'  ','    ','    '),"",$css);

			return $css;
		}

		public function get_email() {

			$css_return = '	svg { max-width: 100%; }

	h1, h2, h3, h4 {

		font-family: sans-serif;
		font-weight: bold;
		margin: 0;
		margin-bottom: 10px;"
	}
	h1 {
		font-size: 24px !important;
	}
	h2 {
		font-size: 22px !important;
	}
	h3 {
		font-size: 20px !important;
	}
	h4 {
		font-size: 18px !important;
	}
	p,li,td,span,a {

		font-family: sans-serif;
		font-size: 14px;
		font-weight: normal;
		margin: 0;
		margin-bottom: 10px;"
 	}

	@media only screen and (max-width: 620px) {

		p,li,td,span,a {
			font-size: 16px;
	 	}
		.wrapper {
			padding: 10px !important;
		}
		.content {
			padding: 0 !important;
		}
		.container {
			padding: 0 !important;
			width: 100% !important;
		}
		.main {
			border-left-width: 0 !important;
			border-radius: 0 !important;
			border-right-width: 0 !important;
		}
	}
			';

			// Minify
			$css_minify = !SCRIPT_DEBUG;

			return $css_minify ? self::minify($css_return) : $css_return;
		}

		// Escape CSS values
		public function e($css_value) {

			$css_value = wp_strip_all_tags($css_value);
			$css_value = str_replace(';', '', $css_value);
			echo $css_value;
		}

		// Inside offset default
		public static function inside_offset_default() {

			// Customizer
			$enable_cache = !(WS_Form_Common::get_query_var('customize_theme') !== '');

			// Components
			$border_width = floatval(WS_Form_Common::option_get('skin_border_width', 1, false, $enable_cache, true));

			// Typography
			$font_size = floatval(WS_Form_Common::option_get('skin_font_size', 14, false, $enable_cache, true));

			// Line height
			$line_height = floatval(WS_Form_Common::option_get('skin_line_height', 1.4, false, $enable_cache, true));

			// Spacing
			$spacing_small = 10;

			// Forms
			$form_border_width = $border_width;
			$form_font_size = $font_size;
			$form_spacing_horizontal = $spacing_small;

			return -(round(($form_font_size * $line_height) / 2) + $form_spacing_horizontal - $form_border_width);
		}
	}
