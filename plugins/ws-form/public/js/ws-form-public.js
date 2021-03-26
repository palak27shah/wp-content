(function($) {

	'use strict';

	// Set is_admin
	$.WS_Form.prototype.set_is_admin = function() { return false; }

	// One time init for admin page
	$.WS_Form.prototype.init = function() {

		// Build data cache
		this.data_cache_build();

		// Set global variables once for performance
		this.set_globals();
	}

	// Continue initialization after submit data retrieved
	$.WS_Form.prototype.init_after_get_submit = function(submit_retrieved) {


		// Build form
		this.form_build();
	}

	// Set global variables once for performance
	$.WS_Form.prototype.set_globals = function() {

		// Get framework ID
		this.framework_id = $.WS_Form.settings_plugin.framework;

		// Get framework settings
		this.framework = $.WS_Form.frameworks.types[this.framework_id];

		// Get current framework
		this.framework_fields = this.framework['fields']['public'];

		// Get invalid_feedback placeholder mask
		this.invalid_feedback_mask_placeholder = '';
		if(typeof($.WS_Form.meta_keys['invalid_feedback']) !== 'undefined') {

			if(typeof($.WS_Form.meta_keys['invalid_feedback']['p']) !== 'undefined') {

				this.invalid_feedback_mask_placeholder = $.WS_Form.meta_keys['invalid_feedback']['p'];
			}
		}

		// Custom action URL
		this.form_action_custom = (this.form_obj.attr('action') != (ws_form_settings.url + 'submit'));

		// Get validated class
		var class_validated_array = (typeof(this.framework.fields.public.class_form_validated) !== 'undefined') ? this.framework.fields.public.class_form_validated : [];
		this.class_validated = class_validated_array.join(' ');


		// Hash
		if(
			ws_form_settings.wsf_hash &&
			(typeof(ws_form_settings.wsf_hash) === 'object')
		) {

			// Set hash from query string
			for(var hash_index in ws_form_settings.wsf_hash) {

				if(!ws_form_settings.wsf_hash.hasOwnProperty(hash_index)) { continue; }

				var wsf_hash = ws_form_settings.wsf_hash[hash_index];

				if(
					(typeof(wsf_hash.id) !== 'undefined') &&
					(typeof(wsf_hash.hash) !== 'undefined') &&
					(typeof(wsf_hash.token) !== 'undefined') &&
					(wsf_hash.id == this.form_id)
				) {

					this.hash_set(wsf_hash.hash, wsf_hash.token, true);
				}
			}

		} else {

			// Set hash from cookie
			this.hash_set(this.cookie_get('hash', ''), false, true);
		}

		// Visual editor?
		this.visual_editor = (typeof(this.form_canvas_obj.attr('data-visual-builder')) !== 'undefined');

		// Read submission data if hash is defined
		var ws_this = this;
		if(this.hash) {

			var url = 'submit/hash/' + this.hash + '/';
			if(this.token) { url += this.token + '/'; }

			// Call AJAX request
			$.WS_Form.this.api_call(url, 'GET', false, function(response) {

				if(typeof(response.data) !== 'undefined') {

					// Save the submissions data
					ws_this.submit = response.data;
				}

				// Initialize after getting submit
				ws_this.init_after_get_submit(true);

				// Finished with submit data
				ws_this.submit = false;

			}, function(response) {

				// Read auto populate data instead
				ws_this.read_json_populate();

				// Initialize after getting submit
				ws_this.init_after_get_submit(false);
			});

		} else {

			// Read auto populate data
			this.read_json_populate();

			// Initialize after getting submit
			this.init_after_get_submit(false);
		}
	}

	// Read auto populate data
	$.WS_Form.prototype.read_json_populate = function() {

		if(typeof(wsf_form_json_populate) !== 'undefined') {

			if(typeof(wsf_form_json_populate[this.form_id]) !== 'undefined') {

				this.submit_auto_populate = wsf_form_json_populate[this.form_id];
			}
		}
	}


	// Render an error message
	$.WS_Form.prototype.error = function(language_id, variable, error_class) {

		if(typeof(variable) == 'undefined') { variable = ''; }
		if(typeof(error_class) == 'undefined') { error_class = ''; }

		// Build error message
		var error_message = this.language(language_id, variable, false).replace(/%s/g, variable);

		// Show error message
		if(!this.visual_editor && this.get_object_meta_value(this.form, 'submit_show_errors', true)) {

			this.action_message(error_message);
		}

		if (window.console && window.console.error) { console.error(error_message); }
	}

	// Render any interface elements that rely on the form object
	$.WS_Form.prototype.form_render = function() {

		// Timer
		this.form_timer();


		// Initialize framework
		this.form_framework();

		// Form preview
		this.form_preview();

		// Groups - Tabs - Initialize
		this.form_tabs();


		// Navigation
		this.form_navigation();


		// Client side form validation
		this.form_validation();

		// Select all
		this.form_select_all();

		// Select min max
		this.form_select_min_max();


		// Checkbox min max
		this.form_checkbox_min_max();

		// Text input and textarea character and word count
		this.form_character_word_count();


		// Required
		this.form_required();

		// Input masks
		this.form_inputmask();

		// Spam protection
		this.form_spam_protection();

		// Bypass
		this.form_bypass_enabled = true;
		this.form_bypass(false);
		// Form stats
		this.form_stat();

		// Form validation - Real time
		this.form_validate_real_time();


		// Trigger rendered event
		this.trigger('rendered');

	}

	$.WS_Form.prototype.form_timer = function() {

		// Timer
		this.date_start = this.cookie_get('date_start', false);
		if((this.date_start === false) || isNaN(this.date_start) || (this.date_start == '')) {

			this.date_start = new Date().getTime();
			this.cookie_set('date_start', this.date_start, false);
		}
	}

	// Trigger events
	$.WS_Form.prototype.trigger = function(slug) {

		// New method
		var action_type = 'wsf-' + slug;
		$(document).trigger(action_type, [this.form, this.form_id, this.form_instance_id, this.form_obj, this.form_canvas_obj]);

		// Legacy method - Instance
		var trigger_instance = 'wsf-' + slug + '-instance-' + this.form_instance_id;
		$(window).trigger(trigger_instance);

		// Legacy method - Form
		var trigger_form = 'wsf-' + slug + '-form-' + this.form_id;
		$(window).trigger(trigger_form);
	}

	// Initialize JS
	$.WS_Form.prototype.form_framework = function() {

		// Add framework form attributes
		if(
			(typeof(this.framework.form.public) !== 'undefined') &&
			(typeof(this.framework.form.public.attributes) === 'object')
		) {

			for(var attribute in this.framework.form.public.attributes) {

				var attribute_value = this.framework.form.public.attributes[attribute];

				this.form_obj.attr(attribute, attribute_value);
			}
		}

		// Check framework init_js
		if(typeof(this.framework.init_js) !== 'undefined') {

			// Framework init JS values
			var framework_init_js_values = {'form_canvas_selector': '#' + this.form_obj_id};
			var framework_init_js = this.mask_parse(this.framework.init_js, framework_init_js_values);

			try {

				$.globalEval('(function($) { ' + framework_init_js + ' })(jQuery);');

			} catch(e) {

				this.error('error_js', action_javascript);
			}
		}
	}

	// Form - Reset
	$.WS_Form.prototype.form_reset = function(e) {

		var ws_this = this;

		// Trigger
		this.trigger('reset-before');

		// Unmark as validated
		this.form_obj.removeClass(this.class_validated);

		// HTML form reset
		this.form_obj[0].reset();
		// Trigger
		this.trigger('reset-complete');
	}

	// Form - Clear
	$.WS_Form.prototype.form_clear = function() {

		var ws_this = this;

		// Trigger
		this.trigger('clear-before');

		// Unmark as validated
		this.form_obj.removeClass(this.class_validated);

		// Clear fields
		for(var key in this.field_data_cache) {

			if(!this.field_data_cache.hasOwnProperty(key)) { continue; }

			var field = this.field_data_cache[key];

			var field_id = field.id;
			var field_name = this.field_name_prefix + field_id;

			var field_type_config = $.WS_Form.field_type_cache[field.type];
			var trigger = (typeof(field_type_config.trigger) !== 'undefined') ? field_type_config.trigger : 'change';

			// Clear value
			switch(field.type) {

				case 'checkbox' :
				case 'price_checkbox' :
				case 'radio' :
				case 'price_radio' :

					$('[name="' + field_name + '"], [name^="' + field_name + '["]', this.form_canvas_obj).each(function() {

						if($(this).is(':checked')) {
	
							$(this).prop('checked', false).trigger(trigger);
						}
					});

					break;

				case 'select' :
				case 'price_select' :

					$('[name="' + field_name + '"], [name^="' + field_name + '["] option', this.form_canvas_obj).each(function() {

						if($(this).is(':selected')) {
	
							$(this).prop('selected', false);
							$(this).closest('select').trigger(trigger);
						}
					});

					break;

				case 'textarea' :

					$('[name="' + field_name + '"], [name^="' + field_name + '["]', this.form_canvas_obj).each(function() {

						if($(this).val() != '') {

							$(this).val('').trigger(trigger);
							ws_this.textarea_set_value($(this), '');
						}
					});

					break;

				case 'color' :

					$('[name="' + field_name + '"], [name^="' + field_name + '["]', this.form_canvas_obj).each(function() {

						if($(this).val() != '') {

							$(this).val('').trigger(trigger);

							if($(this).hasClass('minicolors-input')) {

								$(this).minicolors('value', '');
							}
						}
					});

					break;

				default:

					$('[name="' + field_name + '"], [name^="' + field_name + '["]', this.form_canvas_obj).each(function() {

						if($(this).val() != '') {

							$(this).val('').trigger(trigger);
						}
					});
			}
		}

		// Trigger
		this.trigger('clear-complete');
	}

	// Form reload
	$.WS_Form.prototype.form_reload = function() {

		// Read submission data if hash is defined
		var ws_this = this;
		if(this.hash != '') {

			// Call AJAX request
			$.WS_Form.this.api_call('submit/hash/' + this.hash, 'GET', false, function(response) {

				// Save the submissions data
				ws_this.submit = response.data;

				ws_this.form_reload_after_get_submit(true);

				// Finished with submit data
				ws_this.submit = false;

			}, function(response) {

				ws_this.form_reload_after_get_submit(false);
			});

		} else {

			// Reset submit
			this.submit = false;

			this.form_reload_after_get_submit(false);
		}
	}

	// Form reload - After get submit
	$.WS_Form.prototype.form_reload_after_get_submit = function(submit_retrieved) {

		// Clear any messages
		$('[data-wsf-message][data-wsf-instance-id="' + this.form_instance_id + '"]').remove();

		// Show the form
		this.form_canvas_obj.show();

		// Reset form tag
		this.form_canvas_obj.removeClass(this.class_validated)

		// Clear ecommerce real time validation hooks
		this.form_validation_real_time_hooks = [];

		// Empty form object
		this.form_canvas_obj.empty();

		// Build form
		this.form_build();
	}

	// Form - Hash reset
	$.WS_Form.prototype.form_hash_clear = function() {

		// Clear hash variable
		this.hash = '';

		// Clear hash cookie
		this.cookie_clear('hash')

	}


	// Form navigation
	$.WS_Form.prototype.form_navigation = function() {

		var ws_this = this;

		var group_count = $('.wsf-group-tabs', this.form_canvas_obj).children(':not([data-wsf-group-hidden])').length;

		// Buttons - Next
		$('[data-action="wsf-tab_next"]', this.form_canvas_obj).each(function() {

			// Remove existing click event
			$(this).off('click');

			// Get next group
			var group_next = $(this).closest('[data-group-index]').nextAll(':not([data-wsf-group-hidden])').first();

			// If there are no tabs, or no next tab, disable the next button
			if(
				(group_count <= 1) ||
				(!group_next.length)
			) {
				$(this).attr('disabled', '').attr('data-wsf-disabled', '');

			} else {

				if(typeof($(this).attr('data-wsf-disabled')) !== 'undefined') { $(this).removeAttr('disabled').removeAttr('data-wsf-disabled'); }
			}

			// If button is disabled, then don't initialize
			if(typeof($(this).attr('disabled')) !== 'undefined') { return; }

			// Add click event
			$(this).on('click', function() {

				ws_this.group_index_new($(this), group_next.attr('data-group-index'));
			});
		});

		// Buttons - Previous
		$('[data-action="wsf-tab_previous"]', this.form_canvas_obj).each(function() {

			// Remove existing click event
			$(this).off('click');

			// Get previous group
			var group_previous = $(this).closest('[data-group-index]').prevAll(':not([data-wsf-group-hidden])').first();

			// If there are no tabs, or no previous tab, disable the previous button
			if(
				(group_count <= 1) ||
				(!group_previous.length)
			) {
				$(this).attr('disabled', '').attr('data-wsf-disabled', '');

			} else {

				if(typeof($(this).attr('data-wsf-disabled')) !== 'undefined') { $(this).removeAttr('disabled').removeAttr('data-wsf-disabled'); }
			}

			// If button is disabled, then don't initialize
			if(typeof($(this).attr('disabled')) !== 'undefined') { return; }

			// Add click event
			$(this).on('click', function() {

				ws_this.group_index_new($(this), group_previous.attr('data-group-index'));
			});
		});

		// Buttons - Save
		this.form_canvas_obj.off('click', '[data-action="wsf-save"]').on('click', '[data-action="wsf-save"]', function() {

			// Get field ID
			var field_id = $(this).closest('[data-id]').attr('data-id');

			if(typeof(ws_this.field_data_cache[field_id]) !== 'undefined') {

				var field = ws_this.field_data_cache[field_id];

				var validate_form = ws_this.get_object_meta_value(field, 'validate_form', '');

				if(validate_form) {

					ws_this.form_post_if_validated('save');

				} else {

					ws_this.form_post('save');
				}
			}
		});

		// Buttons - Reset
		this.form_canvas_obj.off('click', '[data-action="wsf-reset"]').on('click', '[data-action="wsf-reset"]', function(e) {

			// Prevent default
			e.preventDefault();

			ws_this.form_reset();
		});

		// Buttons - Clear
		this.form_canvas_obj.off('click', '[data-action="wsf-clear"]').on('click', '[data-action="wsf-clear"]', function() {

			ws_this.form_clear();
		});
	}

	// Tab - Activate by offset amount
	$.WS_Form.prototype.group_index_new = function(obj, group_index_new) {

		// Activate tab
		this.group_index_set(group_index_new);

		// Get field ID
		var field_id = obj.closest('[data-id]').attr('data-id');
		var field = this.field_data_cache[field_id];
		var scroll_to_top = this.get_object_meta_value(field, 'scroll_to_top', '');
		var scroll_to_top_offset = this.get_object_meta_value(field, 'scroll_to_top_offset', '0');
		scroll_to_top_offset = (scroll_to_top_offset == '') ? 0 : parseInt(scroll_to_top_offset, 10);
		var scroll_position = this.form_canvas_obj.offset().top - scroll_to_top_offset;

		switch(scroll_to_top) {

			// Instant
			case 'instant' :

				$('html,body').scrollTop(scroll_position);

				break;

			// Smooth
			case 'smooth' :

				var scroll_to_top_duration = this.get_object_meta_value(field, 'scroll_to_top_duration', '0');
				scroll_to_top_duration = (scroll_to_top_duration == '') ? 0 : parseInt(scroll_to_top_duration, 10);

				$('html,body').animate({

					scrollTop: scroll_position

				}, scroll_to_top_duration);

				break;
		}
	}

	// Tab - Set
	$.WS_Form.prototype.group_index_set = function(group_index) {

		if(this.form.groups.length <= 1) { return false; }

		var framework_tabs = this.framework['tabs']['public'];

		if(typeof(framework_tabs.activate_js) !== 'undefined') {

			var activate_js = framework_tabs.activate_js;	

			if(activate_js != '') {

				// Parse activate_js
				var mask_values = {'form': '#' + this.form_obj_id, 'index': group_index};
				var activate_js_parsed = this.mask_parse(activate_js, mask_values);

				// Execute activate tab javascript
				$.globalEval('(function($) { $(function() {' + activate_js_parsed + '}); })(jQuery);');

				// Set cookie
				this.cookie_set('tab_index', group_index);
			}
		}

	}

	// Get tab index object resides in
	$.WS_Form.prototype.get_group_index = function(obj) {

		var group_count = $('.wsf-tabs', this.form_canvas_obj).children(':visible').length;
		if(group_count <= 1) { return false; }

		// Get group
		var group_single = obj.closest('[data-group-index]');
		if(group_single.length == 0) { return false; }

		// Get group index
		var group_index = group_single.first().attr('data-group-index');
		if(group_index == undefined) { return false; }

		return parseInt(group_index, 10);
	}

	// Get section id object resides in
	$.WS_Form.prototype.get_section_id = function(obj) {

		var section_id = obj.closest('[id^="' + this.form_id_prefix + 'section-"]').attr('data-id');
		if(!section_id) { return false; }
		return parseInt(section_id, 10);
	}

	// Get field id object resides in
	$.WS_Form.prototype.get_field_id_from_obj = function(obj) {

		var field_id = obj.closest('[data-type]').attr('data-id');
		if(!field_id) { return false; }
		return parseInt(field_id, 10);
	}


	// Form preview
	$.WS_Form.prototype.form_preview = function() {

		if(this.form_canvas_obj[0].hasAttribute('data-preview')) {

			this.form_add_hidden_input('wsf_preview', 'true');
		}
	}

	// Form spam protection
	$.WS_Form.prototype.form_spam_protection = function() {

		// Honeypot
		var honeypot = this.get_object_meta_value(this.form, 'honeypot', false);

		if(honeypot) {

			// Add honeypot field
			var honeypot_hash = (this.form.published_checksum != '') ? this.form.published_checksum : ('honeypot_unpublished_' + this.form_id);

			// Build honeypot input
			var framework_type = $.WS_Form.settings_plugin.framework;
			var framework = $.WS_Form.frameworks.types[framework_type];
			var fields = this.framework['fields']['public'];
			var honeypot_attributes = (typeof(fields.honeypot_attributes) !== 'undefined') ? ' ' + fields.honeypot_attributes.join(' ') : '';

			// Add to form
			this.form_add_hidden_input('field_' + honeypot_hash, '', false, 'autocomplete="off"' + honeypot_attributes);

			// Hide it
			var honeypot_obj = $('[name="field_' + honeypot_hash + '"]', this.form_canvas_obj);
			honeypot_obj.css({'position': 'absolute', 'left': '-9999em'});

		}
	}


	// Adds required string (if found in framework config) to all labels
	$.WS_Form.prototype.form_required = function() {

		var ws_this = this;

		// Get required label HTML
		var label_required = this.get_object_meta_value(this.form, 'label_required', false);
		if(!label_required) { return false; }

		var label_mask_required = this.get_object_meta_value(this.form, 'label_mask_required', '', true, true);
		if(label_mask_required == '') {

			// Use framework mask_required_label
			var framework_type = $.WS_Form.settings_plugin.framework;
			var framework = $.WS_Form.frameworks.types[framework_type];
			var fields = this.framework['fields']['public'];

			if(typeof(fields.mask_required_label) === 'undefined') { return false; }
			var label_mask_required = fields.mask_required_label;
			if(label_mask_required == '') { return false; }
		}

		// Get all labels in this form
		$('label', this.form_canvas_obj).each(function() {

			// Get 'for' attribute of label
			var label_for = $(this).attr('for');
			if(label_for === undefined) { return; }

			// Get field related to 'for'
			var field_obj = $('[id="' + label_for + '"]', ws_this.form_canvas_obj);
			if(!field_obj.length) { return; }

			// Check if field should be processed
			if(typeof(field_obj.attr('data-init-required')) !== 'undefined') { return; }

			// Check if field is required
			var field_required = (typeof(field_obj.attr('data-required')) !== 'undefined');

			// Check if the require string should be added to the parent label (e.g. for radios)
			var label_required_id = $(this).attr('data-label-required-id');
			if((typeof(label_required_id) !== 'undefined') && (label_required_id !== false)) {

				var label_obj = $('#' + label_required_id, ws_this.form_canvas_obj);

			} else {

				var label_obj = $(this);
			}

			// Check if wsf-required-wrapper span exists, if not, create it (You can manually insert it in config using #required)
			var required_wrapper = $('.wsf-required-wrapper', label_obj);
			if(!required_wrapper.length && field_required) {

				var required_wrapper_html = '<span class="wsf-required-wrapper"></span>';

				// If field is wrapped in label, find the first the first element to inject the required wrapper before
				var first_child = label_obj.children('div,[name]').first();

				// Add at appropriate place
				if(first_child.length) {

					first_child.before(required_wrapper_html);

				} else {

					label_obj.append(required_wrapper_html);
				}

				required_wrapper = $('.wsf-required-wrapper', label_obj);
			}

			if(field_required) {

				// Add it
				required_wrapper.html(label_mask_required);
				field_obj.attr('data-init-required', '');

			} else {

				// Remove it
				required_wrapper.html('');
				field_obj.removeAttr('data-init-required');
			}
		});
	}

	// Field required bypass
	$.WS_Form.prototype.form_bypass = function(conditional_initiated) {

		if(!this.form_bypass_enabled) { return; }

		var ws_this = this;

		var fields_set_custom_validity_blank = $([]);

		// Process attributes that should be bypassed if a field is hidden
		var attributes = {

			'required':						{'bypass': 'data-required-bypass', 'not': '[type="hidden"]'},
			'aria-required':				{'bypass': 'data-aria-required-bypass', 'not': '[type="hidden"]'},
			'min':							{'bypass': 'data-min-bypass', 'not': '[type="hidden"],[type="range"]'},
			'max':							{'bypass': 'data-max-bypass', 'not': '[type="hidden"],[type="range"]'},
			'minlength':					{'bypass': 'data-minlength-bypass', 'not': '[type="hidden"]'},
			'maxlength':					{'bypass': 'data-maxlength-bypass', 'not': '[type="hidden"]'},
			'pattern':						{'bypass': 'data-pattern-bypass', 'not': '[type="hidden"]'},
			'step':							{'bypass': 'data-step-bypass', 'not': '[type="hidden"],[type="range"]', 'replace': 'any'},
		};

		for(var attribute_source in attributes) {

			if(!attributes.hasOwnProperty(attribute_source)) { continue; }

			var attribute_config = attributes[attribute_source];

			var attribute_bypass = attribute_config.bypass;
			var attribute_not = attribute_config.not;
			var attribute_replace = (typeof(attribute_config.replace) !== 'undefined') ? attribute_config.replace : false;

			// If a group is visible, and contains fields that have a data bypass attribute, reset that attribute
			if($('[' + attribute_bypass + '-group]', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'group-"]:not([data-wsf-group-hidden]) [' + attribute_bypass + '-group]:not(' + attribute_not + ')', this.form_canvas_obj).attr(attribute_source, function() { return $(this).attr(attribute_bypass + '-group'); }).removeAttr(attribute_bypass + '-group');
			}

			// If a group is not visible, and contains validation attributes, add bypass attributes
			if($('[' + attribute_source + ']', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'group-"][data-wsf-group-hidden] [' + attribute_source + ']:not(' + attribute_not + '), [id^="' + this.form_id_prefix + 'group-"][data-wsf-group-hidden] [' + attribute_source + ']:not(' + attribute_not + ')').attr(attribute_bypass + '-group', function() { var attribute_source_value = $(this).attr(attribute_source); if(attribute_replace) { $(this).attr(attribute_source, attribute_replace); } else { $(this).removeAttr(attribute_source); } return attribute_source_value; });
			}

			// If a hidden field is in a hidden group, convert bypass address to group level
			if($('[' + attribute_bypass + ']', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'group-"][data-wsf-group-hidden] [' + attribute_bypass + ']:not(' + attribute_not + '), [id^="' + this.form_id_prefix + 'group-"][data-wsf-group-hidden] [' + attribute_bypass + ']:not(' + attribute_not + ')').attr(attribute_bypass + '-group', function() { return $(this).attr(attribute_bypass); }).removeAttr(attribute_bypass);
			}


			// If a section is visible, and contains fields that have a data bypass attribute, reset that attribute
			if($('[' + attribute_bypass + '-section]', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'section-"][style!="display:none;"][style!="display: none;"] [' + attribute_bypass + '-section]:not(' + attribute_not + ')', this.form_canvas_obj).attr(attribute_source, function() { return $(this).attr(attribute_bypass + '-section'); }).removeAttr(attribute_bypass + '-section');
			}

			// If a section is not visible, and contains validation attributes, add bypass attributes
			if($('[' + attribute_source + ']', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'section-"][style="display:none;"] [' + attribute_source + ']:not(' + attribute_not + '), [id^="' + this.form_id_prefix + 'section-"][style="display: none;"] [' + attribute_source + ']:not(' + attribute_not + ')').attr(attribute_bypass + '-section', function() { var attribute_source_value = $(this).attr(attribute_source); if(attribute_replace) { $(this).attr(attribute_source, attribute_replace); } else { $(this).removeAttr(attribute_source); } return attribute_source_value; });
			}

			// If a hidden field is in a hidden section, convert bypass address to section level
			if($('[' + attribute_bypass + ']', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'section-"][style="display:none;"] [' + attribute_bypass + ']:not(' + attribute_not + '), [id^="' + this.form_id_prefix + 'section-"][style="display: none;"] [' + attribute_bypass + ']:not(' + attribute_not + ')').attr(attribute_bypass + '-section', function() { return $(this).attr(attribute_bypass); }).removeAttr(attribute_bypass);
			}


			// If field is visible, add validation attributes back that have a bypass data tag
			if($('[' + attribute_bypass + ']', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'field-wrapper-"][style!="display:none;"][style!="display: none;"] [' + attribute_bypass + ']:not(' + attribute_not + ')', this.form_canvas_obj).attr(attribute_source, function() { return $(this).attr(attribute_bypass); }).removeAttr(attribute_bypass);
			}

			// If field is not visible, add contain validation attributes, add bypass attributes
			if($('[' + attribute_source + ']', this.form_canvas_obj).length) {

				$('[id^="' + this.form_id_prefix + 'field-wrapper-"][style="display:none;"] [' + attribute_source + ']:not(' + attribute_not + '), [id^="' + this.form_id_prefix + 'field-wrapper-"][style="display: none;"] [' + attribute_source + ']:not(' + attribute_not + ')', this.form_canvas_obj).attr(attribute_bypass, function() { var attribute_source_value = $(this).attr(attribute_source); if(attribute_replace) { $(this).attr(attribute_source, attribute_replace); } else { $(this).removeAttr(attribute_source) } return attribute_source_value; });
			}
		}

		// Process custom validity messages
		$('[id^="' + this.form_id_prefix + 'group-"]:not([data-wsf-group-hidden])', this.form_canvas_obj).find('[name]:not([type="hidden"]),[data-static]').each(function() {

			// Recall cached validation message
			var field_id = $(this).closest('[data-id]').attr('data-id');
			if(typeof(ws_this.validation_message_cache[field_id]) !== 'undefined') {

				if($(this)[0].willValidate) {

					$(this)[0].setCustomValidity(ws_this.validation_message_cache[field_id]);
				}
			}

			// Remove data-hidden attribute
			if(typeof($(this).attr('data-hidden-bypass')) === 'undefined') {

				$(this).removeAttr('data-hidden-group');
			}
		});

		$('[id^="' + this.form_id_prefix + 'group-"][data-wsf-group-hidden]').find('[name]:not([type="hidden"]),[data-static]').each(function() {

			// Store current validation message
			var field_id = $(this).closest('[data-id]').attr('data-id');
			var validation_message = $(this)[0].validationMessage;

			if(validation_message !== '') {

				ws_this.validation_message_cache[field_id] = validation_message;

				// Set custom validation message to blank
				fields_set_custom_validity_blank.push($(this));
			}

			// Add data-hidden attribute
			if(typeof($(this).attr('data-hidden-bypass')) === 'undefined') {

				$(this).attr('data-hidden-group', '');
			}
		});

		$('[id^="' + this.form_id_prefix + 'section-"][style!="display:none;"][style!="display: none;"]', this.form_canvas_obj).find('[name]:not([type="hidden"],[data-hidden-group]),[data-static]').each(function() {

			// Recall cached validation message
			var field_id = $(this).closest('[data-id]').attr('data-id');
			if(typeof(ws_this.validation_message_cache[field_id]) !== 'undefined') {

				if($(this)[0].willValidate) {

					$(this)[0].setCustomValidity(ws_this.validation_message_cache[field_id]);
				}
			}

			// Remove data-hidden attribute
			if(typeof($(this).attr('data-hidden-bypass')) === 'undefined') {

				$(this).removeAttr('data-hidden-section');
			}
		});

		$('[id^="' + this.form_id_prefix + 'section-"][style="display:none;"], [id^="' + this.form_id_prefix + 'section-"][style="display: none;"]').find('[name]:not([type="hidden"]),[data-static]').each(function() {

			// Store current validation message
			var field_id = $(this).closest('[data-id]').attr('data-id');
			var validation_message = $(this)[0].validationMessage;

			if(validation_message !== '') {

				ws_this.validation_message_cache[field_id] = validation_message;

				// Set custom validation message to blank
				fields_set_custom_validity_blank.push($(this));
			}

			// Add data-hidden attribute
			if(typeof($(this).attr('data-hidden-bypass')) === 'undefined') {

				$(this).attr('data-hidden-section', '');
			}
		});

		$('[id^="' + this.form_id_prefix + 'field-wrapper-"][style!="display:none;"][style!="display: none;"]', this.form_canvas_obj).find('[name]:not([type="hidden"],[data-hidden-section],[data-hidden-group]),[data-static]').each(function() {

			// Recall cached validation message
			var field_id = $(this).closest('[data-id]').attr('data-id');
			if(typeof(ws_this.validation_message_cache[field_id]) !== 'undefined') {

				if($(this)[0].willValidate) {

					$(this)[0].setCustomValidity(ws_this.validation_message_cache[field_id]);
				}
			}

			// Remove data-hidden attribute
			if(typeof($(this).attr('data-hidden-bypass')) === 'undefined') {

				$(this).removeAttr('data-hidden');
			}
		});

		$('[id^="' + this.form_id_prefix + 'field-wrapper-"][style="display:none;"], [id^="' + this.form_id_prefix + 'field-wrapper-"][style="display: none;"]', this.form_canvas_obj).find('[name]:not([type="hidden"]),[data-static]').each(function() {

			// Store current validation message
			var field_id = $(this).closest('[data-id]').attr('data-id');
			var validation_message = $(this)[0].validationMessage;

			if(validation_message !== '') {

				ws_this.validation_message_cache[field_id] = validation_message;

				// Set custom validation message to blank
				fields_set_custom_validity_blank.push($(this));
			}

			// Add data-hidden attribute
			if(typeof($(this).attr('data-hidden-bypass')) === 'undefined') {

				$(this).attr('data-hidden', '');
			}
		});

		// Clear validity of fields that were hidden
		fields_set_custom_validity_blank.each(function() {

			if($(this)[0].willValidate) {

				$(this)[0].setCustomValidity('');
			}
		});

	}

	// Select all
	$.WS_Form.prototype.form_select_all = function() {

		var ws_this = this;

		$('[data-select-all]:not([data-init-select-all])', this.form_canvas_obj).each(function() {

			// Flag so it only initializes once
			$(this).attr('data-init-select-all', '');

			// Get select all name
			var select_all_name = $(this).attr('name');
			$(this).removeAttr('name').removeAttr('value').attr('data-select-all', select_all_name);

			// Click event
			$(this).on('click', function() {

				var select_all = $(this).is(':checked');
				var select_all_name = $(this).attr('data-select-all');

				// We use 'each' here to ensure they are checked in ascending order
				$('[name="' + select_all_name + '"]:enabled', ws_this.form_canvas_obj).each(function() {

					$(this).prop('checked', select_all).trigger('change');
				});
			})
		});
	}

	// Form - Input Mask
	$.WS_Form.prototype.form_inputmask = function() {

		$('[data-inputmask]', this.form_canvas_obj).each(function () {

			if(typeof($(this).inputmask) !== 'undefined') {

				$(this).inputmask().off('invalid');
			}
		});
	}

	// Form - Checkbox Min / Max
	$.WS_Form.prototype.form_checkbox_min_max = function() {

		var ws_this = this;

		$('[data-checkbox-min]:not([data-checkbox-min-max-init]),[data-checkbox-max]:not([data-checkbox-min-max-init])', this.form_canvas_obj).each(function () {

			var checkbox_min = $(this).attr('data-checkbox-min');
			var checkbox_max = $(this).attr('data-checkbox-max');

			// If neither attribute present, disregard this feature
			if(
				(typeof(checkbox_min) === 'undefined') &&
				(typeof(checkbox_max) === 'undefined')
			) {

				return;
			}

			// Get field ID
			var field_id = $(this).attr('data-id');

			// Get repeatable suffix
			var field_repeatable_index = $(this).attr('data-repeatable-index');
			var repeatable_suffix = (typeof(field_repeatable_index) !== 'undefined') ? '-repeat-' + field_repeatable_index : '';

			// Custom invalid feedback text
			var invalid_feedback_obj = $('#' + ws_this.form_id_prefix + 'invalid-feedback-' + field_id + repeatable_suffix, ws_this.form_canvas_obj);

			// Build number input
			var checkbox_min_max = $('<input type="number" id="' + ws_this.form_id_prefix + 'checkbox-min-max-' + field_id + repeatable_suffix + '" data-checkbox-min-max data-progress-include="change" style="display:none !important;" />', ws_this.form_canvas_obj);

			// Add min attribute
			if(typeof(checkbox_min) !== 'undefined') { checkbox_min_max.attr('min', checkbox_min); }

			// Add max attribute
			if(typeof(checkbox_max) !== 'undefined') { checkbox_min_max.attr('max', checkbox_max); }
			checkbox_max = parseInt(checkbox_max, 10);

			// Add value attribute
			var checked_count = $('input[type="checkbox"]:not([data-select-all]):checked', $(this)).length;
			checkbox_min_max.attr('value', checked_count);

			// Add before invalid feedback
			invalid_feedback_obj.before(checkbox_min_max);

			// Add event on all checkboxes
			$('input[type="checkbox"]:not([data-select-all])', $(this)).on('change', function(e) {

				var field_wrapper = $(this).closest('[data-type]');

				// Get field ID
				var field_id = field_wrapper.attr('data-id');

				// Get repeatable suffix
				var field_repeatable_index = field_wrapper.attr('data-repeatable-index');
				var repeatable_suffix = (typeof(field_repeatable_index) !== 'undefined') ? '-repeat-' + field_repeatable_index : '';

				// Custom invalid feedback text
				var checkbox_min_max_obj = $('#' + ws_this.form_id_prefix + 'checkbox-min-max-' + field_id + repeatable_suffix, ws_this.form_canvas_obj);

				// Set value
				var checked_count = $('input[type="checkbox"]:not([data-select-all]):checked', field_wrapper).length;

				// Max check
				var obj_wrapper = $(this).closest('[data-type]');
				var input_number = $('input[type="number"]', obj_wrapper);
				var checkbox_max = ws_this.get_number(input_number.attr('max'), 0, false);
				if(
					(checkbox_max > 0) &&
					(checked_count > checkbox_max)
				) {

					$(this).prop('checked', false);
					checked_count--;
				}

				checkbox_min_max_obj.val(checked_count).trigger('change');
			});

			// Flag so it only initializes once
			$(this).attr('data-checkbox-min-max-init', '');
		});
	}

	// Form - Select Min / Max
	$.WS_Form.prototype.form_select_min_max = function() {

		var ws_this = this;

		$('[data-select-min]:not([data-select-min-max-init]),[data-select-max]:not([data-select-min-max-init])', this.form_canvas_obj).each(function () {

			var select_min = $(this).attr('data-select-min');
			var select_max = $(this).attr('data-select-max');

			// If neither attribute present, disregard this feature
			if(
				(typeof(select_min) === 'undefined') &&
				(typeof(select_max) === 'undefined')
			) {

				return;
			}

			// Get field ID
			var field_id = $(this).attr('data-id');

			// Get repeatable suffix
			var field_repeatable_index = $(this).attr('data-repeatable-index');
			var repeatable_suffix = (typeof(field_repeatable_index) !== 'undefined') ? '-repeat-' + field_repeatable_index : '';

			// Custom invalid feedback text
			var invalid_feedback_obj = $('#' + ws_this.form_id_prefix + 'invalid-feedback-' + field_id + repeatable_suffix, ws_this.form_canvas_obj);

			// Build number input
			var select_min_max = $('<input type="number" id="' + ws_this.form_id_prefix + 'select-min-max-' + field_id + repeatable_suffix + '" data-select-min-max data-progress-include="change" style="display:none !important;" />', ws_this.form_canvas_obj);

			// Add min attribute
			if(typeof(select_min) !== 'undefined') { select_min_max.attr('min', select_min); }

			// Add max attribute
			if(typeof(select_max) !== 'undefined') { select_min_max.attr('max', select_max); }
			select_max = parseInt(select_max, 10);

			// Add value attribute
			var selected_count = $('select option:selected', $(this)).length;
			select_min_max.attr('value', selected_count);

			// Add before invalid feedback
			invalid_feedback_obj.before(select_min_max);

			// Add event on all selects
			$('select', $(this)).on('change', function() {

				var field_wrapper = $(this).closest('[data-type]');

				// Get field ID
				var field_id = field_wrapper.attr('data-id');

				// Get repeatable suffix
				var field_repeatable_index = field_wrapper.attr('data-repeatable-index');
				var repeatable_suffix = (typeof(field_repeatable_index) !== 'undefined') ? '-repeat-' + field_repeatable_index : '';

				// Custom invalid feedback text
				var select_min_max_obj = $('#' + ws_this.form_id_prefix + 'select-min-max-' + field_id + repeatable_suffix, ws_this.form_canvas_obj);

				// Get count
				var selected_count = $('select option:selected', field_wrapper).length;

				// Max check
				if(
					(select_max > 0) &&
					(selected_count > select_max)
				) {

					$(this).prop('selected', false);
					selected_count--;
				}

				// Set value
				select_min_max_obj.val(selected_count).trigger('change');
			});

			// Flag so it only initializes once
			$(this).attr('data-select-min-max-init', '');
		});
	}

	// Form - Client side validation
	$.WS_Form.prototype.form_validation = function() {

		// WS Form forms are set with novalidate attribute so we can manage that ourselves
		var ws_this = this;

		// Remove any existing on events
		if(this.form_post_run === true) { this.form_obj.off(); }

		// Disable submit on enter
		if(!this.get_object_meta_value(this.form, 'submit_on_enter', false)) {

			this.form_obj.on('keydown', ':input:not(textarea)', function(e) {

				if(e.keyCode == 13) {

					e.preventDefault();
					return false;
				}
			});
		}

		// On submit
		this.form_obj.on('submit', function(e) {

			e.preventDefault();
			e.stopPropagation();

			// Post if form validates
			ws_this.form_post_if_validated('submit');
		});
	}

	// Form - Post if validated
	$.WS_Form.prototype.form_post_if_validated = function(post_mode) {

		// Trigger
		this.trigger(post_mode + '-before');

		// If form post is locked, return
		if(this.form_post_locked) { return; }

		// Recalculate e-commerce
		if(this.has_ecommerce) { this.form_ecommerce_calculate(); }

		// Mark as validated
		this.form_obj.addClass(this.class_validated);

		// Check validity of form
		if(this.form_validate(this.form_obj)) {

			// Trigger
			this.trigger(post_mode + '-validate-success');

				// Submit form
				this.form_post(post_mode);
		} else {

			// Trigger
			this.trigger(post_mode + '-validate-fail');
		}
	}

	// Form - Validate (WS Form validation functions)
	$.WS_Form.prototype.form_validate = function(form) {

		if(typeof(form) === 'undefined') { form = this.form_obj; }

		// Trigger rendered event
		this.trigger('validate-before');

		// Tab focussing
		var group_index_focus_enabled = (this.form.groups.length > 0);
		var group_index_focus = false;
		var object_focus = false;

		// Get form as element
		var form_el = form[0];

		// Execute browser validation
		var form_validated = form_el.checkValidity();

		if(!form_validated) {

			// Get all invalid fields
			var fields_invalid = $(':invalid', form).not('fieldset');

			if(fields_invalid) {

				// Get first invalid field
				object_focus = fields_invalid.first();

				// Get group index
				group_index_focus = this.get_group_index(object_focus);
			}
		}

		// Focus
		if(!form_validated) {

			if(object_focus !== false) {

				// Focus object
				if(this.get_object_meta_value(this.form, 'invalid_field_focus', true)) {

					if(group_index_focus !== false) { 

						this.object_focus = object_focus;

					} else {

						object_focus.trigger('focus');
					}
				}
			}

			// Focus tab
			if(group_index_focus !== false) { this.group_index_set(group_index_focus); }
		}

		// Trigger rendered event
		this.trigger('validate-after');

		return form_validated;
	}

	// Form - Validate - Real time
	$.WS_Form.prototype.form_validate_real_time = function(form) {

		var ws_this = this;

		// Set up form validation events
		for(var field_index in this.field_data_cache) {

			if(!this.field_data_cache.hasOwnProperty(field_index)) { continue; }

			var field_type = this.field_data_cache[field_index].type;
			var field_type_config = $.WS_Form.field_type_cache[field_type];

			// Get events
			if(typeof(field_type_config.events) === 'undefined') { continue; }
			var form_validate_event = field_type_config.events.event;

			// Get field ID
			var field_id = this.field_data_cache[field_index].id;

			// Check to see if this field is submitted as an array
			var submit_array = (typeof(field_type_config.submit_array) !== 'undefined') ? field_type_config.submit_array : false;

			// Check to see if field is in a repeatable section
			var field_wrapper = $('[data-type][data-id="' + field_id + '"],input[type="hidden"][data-id-hidden="' + field_id + '"]', this.form_canvas_obj);

			// Run through each wrapper found (there might be repeatables)
			field_wrapper.each(function() {

				var field_repeatable_index = $(this).attr('data-repeatable-index');
				var repeatable_suffix = (typeof(field_repeatable_index) !== 'undefined') ? '[' + field_repeatable_index + ']' : '';

				if(submit_array) {

					var field_obj = $('[name="' + ws_form_settings.field_prefix + field_id + repeatable_suffix + '[]"]:not([data-init-validate-real-time]), [name="' + ws_form_settings.field_prefix + field_id + repeatable_suffix + '[]"]:not([data-init-validate-real-time])', ws_this.form_canvas_obj);

				} else {

					var field_obj = $('[name="' + ws_form_settings.field_prefix + field_id + repeatable_suffix + '"]:not([data-init-validate-real-time]), [name="' + ws_form_settings.field_prefix + field_id + repeatable_suffix + '"]:not([data-init-validate-real-time])', ws_this.form_canvas_obj);
				}

				if(field_obj.length) {

					// Flag so it only initializes once
					field_obj.attr('data-init-validate-real-time', '');

					// Check if field should be bypassed
					var event_validate_bypass = (typeof(field_type_config.event_validate_bypass) !== 'undefined') ? field_type_config.event_validate_bypass : false;

					// Create event (Also run on blur, this prevents the mask component from causing false validation results)
					field_obj.on(form_validate_event + ' blur', function(e) {

						// Form validation
						if(!event_validate_bypass) {

							// Run validate real time processing
							ws_this.form_validate_real_time_process(false);
						}

					});
				}
			});
		}

		// Initial validation fire
		this.form_validate_real_time_process(false);
	}

	$.WS_Form.prototype.form_validate_real_time_process = function(conditional_initiated) {

		// Validate
		this.form_valid = this.form_validate_silent(this.form_obj);

		// Run conditional logic
		if(!conditional_initiated) { this.form_canvas_obj.trigger('wsf-validate-silent'); }

		// Check for form validation changes
		if(
			(this.form_valid_old === null) ||
			(this.form_valid_old != this.form_valid)
		) {

			// Run conditional logic
			if(!conditional_initiated) { this.form_canvas_obj.trigger('wsf-validate'); }
		}

		this.form_valid_old = this.form_valid;

		// Execute hooks and pass form_valid to them
		for(var hook_index in this.form_validation_real_time_hooks) {

			if(!this.form_validation_real_time_hooks.hasOwnProperty(hook_index)) { continue; }

			var hook = this.form_validation_real_time_hooks[hook_index];

			if(typeof(hook) === 'undefined') {

				delete(this.form_validation_real_time_hooks[hook_index]);

			} else {

				hook(this.form_valid, this.form, this.form_id, this.form_instance_id, this.form_obj, this.form_canvas_obj);
			}
		}

		return this.form_valid;
	}

	$.WS_Form.prototype.form_validate_real_time_register_hook = function(hook) {

		this.form_validation_real_time_hooks.push(hook);
	}

	// Form - Validate - Silent
	$.WS_Form.prototype.form_validate_silent = function(form) {

		// Get form as element
		var form_el = form[0];

		// aria-invalid="true"
		$(':valid[aria-invalid="true"]:not(fieldset)', form).removeAttr('aria-invalid');
		$(':invalid:not([aria-invalid="true"]):not(fieldset)', form).attr('aria-invalid', 'true');

		// Execute browser validation
		var form_validated = form_el.checkValidity();
		if(!form_validated) { return false; }


		return true;
	}

	// Validate any form object
	$.WS_Form.prototype.object_validate = function(obj) {

		var radio_field_processed = [];		// This ensures correct progress numbers of radios

		if(typeof(obj) === 'undefined') { return false; }

		var ws_this = this;

		var valid = true;

		// Get fields
		$('input,select,textarea', obj).filter(':not([data-hidden],[data-hidden-section],[data-hidden-group],[disabled],[type="hidden"])').each(function() {

			// Get data ID
			var field_id = $(this).closest('[data-id]').attr('data-id');

			// Get progress event
			var field = ws_this.field_data_cache[field_id];
			var field_type = field.type;

			// Get repeatable suffix
			var field_wrapper = $(this).closest('[data-type]');
			var field_repeatable_index = field_wrapper.attr('data-repeatable-index');
			var repeatable_suffix = (typeof(field_repeatable_index) !== 'undefined') ? '[' + field_repeatable_index + ']' : '';

			// Build field name
			var field_name = ws_form_settings.field_prefix + field_id + repeatable_suffix;

			// Determine field validity based on field type
			var validity = false;
			switch(field_type) {

				case 'radio' :
				case 'price_radio' :

					if(typeof(radio_field_processed[field_name]) === 'undefined') { 

						validity = $(this)[0].checkValidity();

					} else {

						return;
					}
					break;

				case 'email' :

					var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
					validity = re.test($(this).val());
					break;

				default :

					validity = $(this)[0].checkValidity();
			}

			radio_field_processed[field_name] = true;

			if(!validity) { valid = false; return false; }
		});

		return valid;
	}

	// Convert hex color to RGB values
	$.WS_Form.prototype.hex_to_hsl = function(color) {

		// Get RGB of hex color
		var rgb = this.hex_to_rgb(color);
		if(rgb === false) { return false; }

		// Get HSL of RGB
		var hsl = this.rgb_to_hsl(rgb);

		return hsl;
	}

	// Convert hex color to RGB values
	$.WS_Form.prototype.hex_to_rgb = function(color) {

		// If empty, return false
		if(color == '') { return false; }

		// Does color have a hash?
		var color_has_hash = (color[0] == '#');

		// Check
		if(color_has_hash && (color.length != 7)) { return false; }
		if(!color_has_hash && (color.length != 6)) { return false; }

		// Strip hash
		var color = color_has_hash ? color.substr(1) : color;

		// Get RGB values
		var r = parseInt(color.substr(0,2), 16);
		var g = parseInt(color.substr(2,2), 16);
		var b = parseInt(color.substr(4,2), 16);

		return {'r': r, 'g': g, 'b': b};
	}

	// Convert RGB to HSL
	$.WS_Form.prototype.rgb_to_hsl = function(rgb) {

		if(typeof(rgb.r) === 'undefined') { return false; }
		if(typeof(rgb.g) === 'undefined') { return false; }
		if(typeof(rgb.b) === 'undefined') { return false; }

		var r = rgb.r;
		var g = rgb.g;
		var b = rgb.b;

		r /= 255, g /= 255, b /= 255;

		var max = Math.max(r, g, b), min = Math.min(r, g, b);
		var h, s, l = (max + min) / 2;

		if(max == min){
	
			h = s = 0;
	
		} else {
	
			var d = max - min;
			s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

			switch(max){
				case r: h = (g - b) / d + (g < b ? 6 : 0); break;
				case g: h = (b - r) / d + 2; break;
				case b: h = (r - g) / d + 4; break;
			}

			h /= 6;
		}

		return {'h': h, 's': s, 'l': l};
	}

	$.WS_Form.prototype.conditional_process_actions = function(actions, action_then_else, source_obj, source_repeatable_index) {

		var actions_processed = 0;
		var process_required = false;
		var process_bypass = false;
		var process_navigation = false;
		var ws_this = this;

		for(var action_index in actions) {

			if(!actions.hasOwnProperty(action_index)) { continue; }

			var action_single = actions[action_index];

			// Check integrity of action
			if(!this.conditional_action_check(action_single)) { continue; }

			// Read action data
			var destination_object = action_single['object'];
			var destination_object_id = action_single['object_id'];
			var destination_object_row_id = (typeof(action_single['object_row_id']) === 'undefined') ? false : action_single['object_row_id'];
			var destination_action = action_single['action'];

			// Process by object type
			switch(destination_object) {

				case 'form' :

					// Get object wrapper
					var destination_obj_wrapper = ws_this.form_obj;

					// Get object
					var destination_obj = ws_this.form_obj;

					// Get value parsed
					var destination_value = (typeof(action_single['value']) === 'undefined') ? false : this.parse_variables_process(action_single['value'], false, false, false, false, false).output;

					// Process action
					var conditional_process_action_return = this.conditional_process_action(action_then_else, destination_action, destination_obj_wrapper, destination_obj, destination_object, destination_object_id, destination_object_row_id, destination_value, false);
					process_required = process_required || conditional_process_action_return.process_required;
					process_bypass = process_bypass || conditional_process_action_return.process_bypass;
					process_navigation = process_navigation || conditional_process_action_return.process_navigation;

					break;

				case 'group' :

					// Build group selector
					var destination_selector = '#' + this.form_id_prefix + 'group-' + destination_object_id;

					// Get object wrapper and object
					var destination_obj_wrapper = destination_obj = $(destination_selector, this.form_canvas_obj);

					// Get value parsed
					var destination_value = (typeof(action_single['value']) === 'undefined') ? false : this.parse_variables_process(action_single['value'], false, false, false, false, false).output;

					// Process action
					var conditional_process_action_return =  this.conditional_process_action(action_then_else, destination_action, destination_obj_wrapper, destination_obj, destination_object, destination_object_id, destination_object_row_id, destination_value, false);
					process_required = process_required || conditional_process_action_return.process_required;
					process_bypass = process_bypass || conditional_process_action_return.process_bypass;
					process_navigation = process_navigation || conditional_process_action_return.process_navigation;

					break;

				case 'section' :

					// Get source section ID
					var source_section_id = this.get_section_id(source_obj);

					// Get all instances of the destination section
					var destination_wrappers = $('[id^="' + this.form_id_prefix + 'section-"][data-id="' + destination_object_id + '"]', this.form_canvas_obj);
					if(!destination_wrappers.length) { break; }
					var destination_wrapper_first = destination_wrappers.first();

					// Same section?
					if(source_section_id === parseInt(destination_object_id, 10)) {

						// Section is repeatable?
						if(destination_wrapper_first.attr('data-repeatable-index')) {

							// Filter by repeatable index
							destination_wrappers = destination_wrappers.filter('[data-repeatable-index="' + source_repeatable_index + '"]');
						}
					}					

					destination_wrappers.each(function() {

						// Get destination repeatable index (This is used to localize the conditional_process_action)
						var destination_repeatable_index = ((typeof($(this).attr('data-repeatable-index')) !== 'undefined') ? $(this).attr('data-repeatable-index') : false);

						// Get value parsed
						var destination_value = (typeof(action_single['value']) === 'undefined') ? false : ws_this.parse_variables_process(action_single['value'], destination_repeatable_index, false, false, false, false, false).output;

						// Process action
						var conditional_process_action_return = ws_this.conditional_process_action(action_then_else, destination_action, $(this), $(this), destination_object, destination_object_id, destination_object_row_id, destination_value, destination_repeatable_index);
						process_required = process_required || conditional_process_action_return.process_required;
						process_bypass = process_bypass || conditional_process_action_return.process_bypass;
						process_navigation = process_navigation || conditional_process_action_return.process_navigation;
					});

					break;

				case 'field' :

					// Get source section ID
					var source_section_id = this.get_section_id(source_obj);

					// Get all instances of the destination field
					var destination_wrappers = $('[id^="' + this.form_id_prefix + 'field-wrapper-"][data-id="' + destination_object_id + '"],input[type="hidden"][data-id-hidden="' + destination_object_id + '"]', this.form_canvas_obj);
					if(!destination_wrappers.length) { break; }
					var destination_wrapper_first = destination_wrappers.first();

					// Get destination section ID
					var destination_section_id = this.get_section_id(destination_wrapper_first.first());

					// Same section?
					if(source_section_id === destination_section_id) {

						// Section is repeatable?
						if(destination_wrapper_first.attr('data-repeatable-index')) {

							// Filter by repeatable index
							destination_wrappers = destination_wrappers.filter('[data-repeatable-index="' + source_repeatable_index + '"]');
						}
					}					

					destination_wrappers.each(function() {

						// Get destination repeatable index (This is used to localize the conditional_process_action)
						var destination_repeatable_index = ((typeof($(this).attr('data-repeatable-index')) !== 'undefined') ? $(this).attr('data-repeatable-index') : false);

						// Get destination repeatable suffix
						var destination_repeatable_suffix = (destination_repeatable_index !== false) ? '-repeat-' + destination_repeatable_index : '';

						// Get destination obj
						var destination_selector = '#' + ws_this.form_id_prefix + 'field-' + destination_object_id + (destination_object_row_id ? '-row-' + destination_object_row_id : '') + destination_repeatable_suffix;
						var destination_obj = $(destination_selector, ws_this.form_canvas_obj);

						// Get field_to
						var field_to = (typeof(ws_this.field_data_cache[destination_object_id]) !== 'undefined') ? ws_this.field_data_cache[destination_object_id] : false;

						// Get value parsed
						var destination_value = (typeof(action_single['value']) === 'undefined') ? false : ws_this.parse_variables_process(action_single['value'], destination_repeatable_index, false, false, field_to, false, false).output;

						// Process action
						var conditional_process_action_return = ws_this.conditional_process_action(action_then_else, destination_action, $(this), destination_obj, destination_object, destination_object_id, destination_object_row_id, destination_value, destination_repeatable_index);
						process_required = process_required || conditional_process_action_return.process_required;
						process_bypass = process_bypass || conditional_process_action_return.process_bypass;
						process_navigation = process_navigation || conditional_process_action_return.process_navigation;
					});

					break;

				case 'action' :

					// Get value parsed
					var destination_value = (typeof(action_single['value']) === 'undefined') ? false : ws_this.parse_variables_process(action_single['value'], false, false, false, false, false).output;

					// Process action
					var conditional_process_action_return = ws_this.conditional_process_action(action_then_else, destination_action, $(this), false, destination_object, destination_object_id, destination_object_row_id, destination_value, false);
					process_required = process_required || conditional_process_action_return.process_required;
					process_bypass = process_bypass || conditional_process_action_return.process_bypass;
					process_navigation = process_navigation || conditional_process_action_return.process_navigation;

					break;
			}

			// Increment number of actions processed
			actions_processed++;
		}

		// Process required?
		if(process_required) {

			this.form_progress();
			this.form_required();
		}

		// Process bypass?
		if(process_bypass) {

			this.form_bypass(true);
			this.form_tab_validation_process();
		}

		// Process navigation?
		if(process_navigation) {

			this.form_navigation();
		}

		return actions_processed;
	}

	$.WS_Form.prototype.conditional_process_action = function(action_then_else, action, obj_wrapper, obj, object, object_id, object_row_id, value, section_repeatable_index) {

		if(typeof(value) === 'undefined') { value = ''; }

		// Build field name
		var field_name = ws_form_settings.field_prefix + object_id + (section_repeatable_index ? '[' + section_repeatable_index + ']' : '');

		// Set debug action value
		var debug_action_value = value;
		var debug_action_language_id = false;

		// Process required?
		var process_required = false;

		// Process bypass?
		var process_bypass = false;

		// Process navigation?
		var process_navigation = false;

		switch(action) {

			// Set value
			case 'value' :
			case 'value_number' :
			case 'value_datetime' :
			case 'value_tel' :
			case 'value_email' :
			case 'value_textarea' :

				// Price formatting (Ensure correctly foratted price is injected into price fields for the currency input mask)
				switch(obj_wrapper.attr('data-type')) {

					case 'price' :
					case 'cart_price' :

						// Check for blank values
						if(value === '') { value = '0'; }

						// Check if value is a number, if not, try to convert it using current website currency format
						if(isNaN(value)) { 

							var value = this.get_number(value);
						}

						// Convert to price in website currency format (Expects value to be in regular 12.345 format)
						value = this.get_price(value);
						break;
				}

				// Set value
				obj.attr('data-value-old', function() { return $(this).val(); }).val(value).filter(function() { return $(this).val() !== $(this).attr('data-value-old') }).trigger('change').removeAttr('data-value-old');

				// Text area
				if(action === 'value_textarea') {

					this.textarea_set_value(obj, value);
				}

				break;

			case 'value_range' : 
			case 'value_rating' :

				obj.attr('data-value-old', function() { return $(this).val(); }).val(value).filter(function() { return $(this).val() !== $(this).attr('data-value-old') }).trigger('change').removeAttr('data-value-old');
				break;

			case 'value_color' :

				if(obj.hasClass('minicolors-input')) {

					obj.attr('data-value-old', function() { return $(this).val(); }).minicolors('value', {color: value}).filter(function() { return $(this).val() !== $(this).attr('data-value-old') }).trigger('change').removeAttr('data-value-old');

				} else {

					obj.attr('data-value-old', function() { return $(this).val(); }).val(value).filter(function() { return $(this).val() !== $(this).attr('data-value-old') }).trigger('change').removeAttr('data-value-old');
				}
				break;

			// Set HTML
			case 'html' :

				$('[data-html],[data-text-editor]', obj_wrapper).html(value);
				break;

			// Set text editor
			case 'text_editor' :

				// wpautop
				if(typeof(this.field_data_cache[object_id]) !== 'undefined') {

					var field = this.field_data_cache[object_id];

					if(typeof($.WS_Form.field_type_cache[field.type]) !== 'undefined') {

						var field_type_config = $.WS_Form.field_type_cache[field.type];

						var meta_wpautop = (typeof(field_type_config.meta_wpautop) !== 'undefined') ? field_type_config.meta_wpautop : false;					

						if(meta_wpautop === 'text_editor') {

							value = this.wpautop(value);
						}
					}
				}

				$('[data-text-editor]', obj_wrapper).html(value);

				break;

			// Set button label
			case 'button_html' :

				obj.html(value);
				break;

			// Add class (Wrapper)
			case 'class_add_wrapper' :

				obj_wrapper.addClass(value);
				debug_action_language_id = 'debug_action_added';
				break;

			// Remove class
			case 'class_remove_wrapper' :

				obj_wrapper.removeClass(value);
				debug_action_language_id = 'debug_action_removed';
				break;

			// Add class (Wrapper)
			case 'class_add_field' :

				obj.addClass(value);
				debug_action_language_id = 'debug_action_added';
				break;

			// Remove class
			case 'class_remove_field' :

				obj.removeClass(value);
				debug_action_language_id = 'debug_action_removed';
				break;
			// Select an option
			case 'value_row_select' :
			case 'value_row_deselect' :

				if(!obj.is(':enabled')) { break; }
				var trigger = (obj.prop('selected') !== (action == 'value_row_select'));
				obj.prop('selected', false).prop('selected', (action == 'value_row_select'));
				if(trigger) { obj.closest('select').trigger('change'); }
				debug_action_language_id = 'debug_action_' + ((action == 'value_row_select') ? 'selected' : 'deselected');
				break;

			// Select an option by value
			case 'value_row_select_value' :
			case 'value_row_deselect_value' :

				var trigger = ($('option[value="' + this.html_encode(value) + '"]', obj).prop('selected') !== (action == 'value_row_select_value'));
				$('option[value="' + this.html_encode(value) + '"]', obj).prop('selected', (action == 'value_row_select_value'));
				if(trigger) { obj.trigger('change'); }
				debug_action_language_id = 'debug_action_' + ((action == 'value_row_select') ? 'selected_value' : 'deselected_value');
				break;

			// Unselect all options (Clear)
			case 'value_row_reset' :

				obj_wrapper.find('option:enabled').prop('selected', true).prop('selected', false).trigger('change');
				debug_action_language_id = 'debug_action_reset';
				break;

			// Check/uncheck a checkbox or radio
			case 'value_row_check' :
			case 'value_row_uncheck' :

				if(!obj.is(':enabled')) { break; }
				var trigger = (obj.prop('checked') !== (action == 'value_row_check'));
				obj.prop('checked', (action == 'value_row_check'));
				if(trigger) { obj.trigger('change'); }
				debug_action_language_id = 'debug_action_' + ((action == 'value_row_check') ? 'checked' : 'unchecked');
				break;

			// Check/uncheck a checkbox or radio. by value
			case 'value_row_check_value' :
			case 'value_row_uncheck_value' :

				var trigger = ($('input[value="' + this.html_encode(value) + '"]', obj_wrapper).prop('checked') !== (action == 'value_row_check_value'));
				$('input[value="' + this.html_encode(value) + '"]', obj_wrapper).prop('checked', (action == 'value_row_check_value'));
				if(trigger) { $('input[value="' + this.html_encode(value) + '"]', obj_wrapper).trigger('change'); }
				debug_action_language_id = 'debug_action_' + ((action == 'value_row_check_value') ? 'checked' : 'unchecked');
				break;

			// Set required on a checkbox or radio
			case 'value_row_required' :
			case 'value_row_not_required' :

				// Set required attribute
				obj.prop('required', (action == 'value_row_required')).removeAttr('data-init-required');

				if(action == 'value_row_required') {

					// Set ARIA required
					obj.attr('data-required', '').attr('aria-required', 'true').removeAttr('data-conditional-logic-bypass');

				} else {

					// Set ARIA not required
					obj.removeAttr('data-required').removeAttr('aria-required').attr('data-conditional-logic-bypass', '');
				}

				debug_action_language_id = 'debug_action_' + ((action == 'value_row_required') ? 'required' : 'not_required');

				process_required = true;
				process_bypass = true;

				break;

			// Set disabled on a checkbox or radio
			case 'value_row_disabled' :
			case 'value_row_not_disabled' :

				obj.attr('disabled', (action == 'value_row_disabled'));

				// Re-render select2 (Fixes select2 bug where disable attribute is not updated)
				if(typeof(obj.parent().attr('data-wsf-select2')) !== 'undefined') {

					this.form_select_ajax(obj.parent());
				}

				debug_action_language_id = 'debug_action_' + ((action == 'value_row_disabled') ? 'disabled' : 'enabled');
				break;

			// Set visible on a checkbox or radio
			case 'value_row_visible' :
			case 'value_row_not_visible' :

				if(action === 'value_row_not_visible') { obj.parent().hide(); } else { obj.parent().show(); }
				debug_action_language_id = 'debug_action_' + ((action == 'value_row_not_visible') ? 'hide' : 'show');
				break;

			// Focus checkbox or radio
			case 'value_row_focus' :

				obj.trigger('focus');
				debug_action_language_id = 'debug_action_focussed';
				break;

			// Add class
			case 'value_row_class_add' :

				obj.addClass(value);
				debug_action_language_id = 'debug_action_added';
				break;

			// Remove class
			case 'value_row_class_remove' :

				obj.removeClass(value);
				debug_action_language_id = 'debug_action_removed';
				break;

			// Set custom validity
			case 'value_row_set_custom_validity' :

				// Custom invalid feedback text
				var invalid_feedback_obj = $('[id^="' + this.form_id_prefix + 'invalid-feedback-' + object_id + '-row-' + object_row_id + '"]', obj_wrapper);

				// Set invalid feedback
				this.set_invalid_feedback(obj, invalid_feedback_obj, value, object_id, object_row_id);

				// Process bypass
				process_bypass = true;

				break;

			// Set min / max / step (Floating point)
			case 'min' :
			case 'max' :
			case 'step' :

				value = (value != '') ? this.get_float(value, 0) : false;

				this.obj_set_attribute(obj, action, value);

				break;

			// Set min / max / step (Integer)
			case 'min_int' :
			case 'max_int' :
			case 'step_int' :

				var action_int = action.replace('_int', '');

				value = (value != '') ? this.get_number(value, 0, false) : false;

				this.obj_set_attribute(obj, action_int, value);

				break;

			// Set select min / max
			case 'select_min' :
			case 'select_max' :

				var min_max = (action === 'select_min') ? 'min' : 'max';

				value = (value != '') ? this.get_number(value, 0, false) : false;

				var form_select_min_max_process = (typeof(obj_wrapper.attr('data-select-min-max-init')) === 'undefined');

				if(value !== false) {

					obj_wrapper.attr('data-select-' + min_max, value);

				} else {

					obj_wrapper.removeAttr('data-select-' + min_max);
				}

				if(form_select_min_max_process) {

					this.form_select_min_max();

				} else {

					var input_number = $('input[type="number"]', obj_wrapper); 

					this.obj_set_attribute(input_number, min_max, value);
				}

				break;

			// Set checkbox min / max
			case 'checkbox_min' :
			case 'checkbox_max' :

				var min_max = (action === 'checkbox_min') ? 'min' : 'max';

				value = (value != '') ? this.get_number(value, 0, false) : false;

				var form_checkbox_min_max_process = (typeof(obj_wrapper.attr('data-checkbox-min-max-init')) === 'undefined');

				if(value !== false) {

					obj_wrapper.attr('data-checkbox-' + min_max, value);

				} else {

					obj_wrapper.removeAttr('data-checkbox-' + min_max);
				}

				if(form_checkbox_min_max_process) {

					this.form_checkbox_min_max();

				} else {

					var input_number = $('input[type="number"]', obj_wrapper); 

					this.obj_set_attribute(input_number, min_max, value);
				}

				break;

			// Set visibility
			case 'visibility' :

				switch(object) {

					// Tab
					case 'group' :

						var group_tab_obj = $('[href="#' + this.form_id_prefix + 'group-' + object_id + '"]', this.form_canvas_obj).parent();

						if(value === 'off') {

							// Is tab being hidden currently visible?
							var obj_visible = obj_wrapper.is(':visible');

							if(obj_visible) {

								// Attempt to find first hidden tab and show it
								var groups_visible = $('[id^="' + this.form_id_prefix + 'group-"]:not([data-wsf-group-hidden])');

								if(groups_visible.length) {

									var group_id = groups_visible.first().attr('id');

									$('[href="#' + group_id + '"]').trigger('click');
								}
							}

							// Hide tab
							group_tab_obj.attr('data-wsf-group-hidden', '').hide().attr('aria-live', 'polite').attr('aria-hidden', 'true');

							// Hide tab content
							obj_wrapper.attr('data-wsf-group-hidden', '').attr('aria-live', 'polite').attr('aria-hidden', 'true');

							debug_action_language_id = 'debug_action_hide';

						} else {

							// Show tab
							group_tab_obj.removeAttr('data-wsf-group-hidden').show().removeAttr('aria-hidden');

							// Show tab content
							obj_wrapper.removeAttr('data-wsf-group-hidden').removeAttr('aria-hidden');

							debug_action_language_id = 'debug_action_show';
						}

						// Process bypass
						process_bypass = true;

						// Process navigation
						process_navigation = true;

						break;

					// Field / section visibility
					case 'section' :
					case 'field' :

						if(value === 'off') {

							// Hide object
							obj_wrapper.hide().attr('aria-live', 'polite').attr('aria-hidden', 'true');

							// Process bypass
							process_bypass = true;

							debug_action_language_id = 'debug_action_hide';

						} else {

							// Show object
							obj_wrapper.show().removeAttr('aria-hidden');

							// Process bypass
							process_bypass = true;

							// Redraw signatures
							if(object == 'section') { this.signatures_redraw(false, object_id); }
							if(object == 'field') { this.signatures_redraw(false, false, object_id); }

							debug_action_language_id = 'debug_action_show';
						}

						break;
				}

				break;

			// Set row count
			case 'set_row_count' :

				// Get sections
				var sections = $('[data-repeatable][data-id="' + object_id + '"]', this.form_canvas_obj);
				if(!sections.length) { break; }
				var section_count = sections.length;
				if(isNaN(value)) { break; }
				var section_count_required = parseInt(value, 10);

				// Get section data
				var section = this.section_data_cache[object_id];

				// Section repeat - Min
				var section_repeat_min = this.get_object_meta_value(section, 'section_repeat_min', 1);
				if(
					(section_repeat_min == '') ||
					isNaN(section_repeat_min)

				) { section_repeat_min = 1; } else { section_repeat_min = parseInt(section_repeat_min, 10); }
				if(section_repeat_min < 1) { section_repeat_min = 1; }

				// Section repeat - Max
				var section_repeat_max = this.get_object_meta_value(section, 'section_repeat_max', false);
				if(
					(section_repeat_max == '') ||
					isNaN(section_repeat_min)

				) { section_repeat_max = false; } else { section_repeat_max = parseInt(section_repeat_max, 10); }

				// Checks
				if(section_count_required < section_repeat_min) { section_count_required = section_repeat_min; }
				if((section_repeat_max !== false) && (section_count_required > section_repeat_max)) {

					section_count_required = section_repeat_max;
				}

				// Add rows
				if(section_count < section_count_required) {

					// Get section obj to clone
					var section_clone_this = sections.last();

					// Calculate number of rows to add
					var rows_to_add = (section_count_required - section_count);
					for(var add_count = 0; add_count < rows_to_add; add_count++) {

						// Clone
						this.section_clone(section_clone_this);
					}

					// Initialize added section
					this.section_add_init(object_id);

					// Trigger event
					this.form_canvas_obj.trigger('wsf-section-repeatable').trigger('wsf-section-repeatable-' + object_id);
				}

				// Delete rows
				if(section_count > section_count_required) {

					// Calculate number of rows to delete
					var rows_to_delete = (section_count - section_count_required);
					for(var delete_count = 0; delete_count < rows_to_delete; delete_count++) {

						var sections = $('[data-repeatable][data-id="' + object_id + '"]', this.form_canvas_obj);
						sections.last().remove();
					}

					// Initialize removed section
					this.section_remove_init(object_id);

					// Trigger event
					this.form_canvas_obj.trigger('wsf-section-repeatable').trigger('wsf-section-repeatable-' + object_id);
				}

				break;

			// Disable
			case 'disabled' :

				switch(object) {

					case 'section' :

						// For sections, we need to look for a fieldset
						obj_wrapper.prop('disabled', (value == 'on'));

						if(value == 'on') {

							obj_wrapper.attr('aria-disabled', 'true');

						} else {

							obj_wrapper.removeAttr('aria-disabled');
						}

						var obj_array = $('[name]', obj_wrapper);

						break;

					case 'field' :

						var obj_array = obj;

						break;
				}

				var ws_this = this;

				$(obj_array).each(function() {

					$(this).prop('disabled', (value == 'on'));

					if(value == 'on') {

						$(this).attr('aria-disabled', 'true');

					} else {

						$(this).removeAttr('aria-disabled');
					}

					var obj_wrapper = $(this).closest('[data-type]');

					var class_disabled_array = ws_this.get_field_value_fallback(obj_wrapper.attr('data-type'), false, 'class_disabled', false);

					if(value == 'on') {

						if(class_disabled_array !== false) { $(this).addClass(class_disabled_array.join(' ')); }
						$(this).css({'pointer-events': 'none'}).attr('data-conditional-logic-bypass', '');

					} else {

						if(class_disabled_array !== false) { $(this).removeClass(class_disabled_array.join(' ')); }
						$(this).css({'pointer-events': 'auto'}).removeAttr('data-conditional-logic-bypass');
					}

					switch(obj_wrapper.attr('data-type')) {

						case 'file' :

							switch($(this).attr('data-file-type')) {

								case 'dropzonejs' :

									if(value == 'on') {

										$('.dropzone', obj_wrapper)[0].dropzone.disable();

									} else {

										$('.dropzone', obj_wrapper)[0].dropzone.enable();
									}

									break;
							}

							break;
					}
				})

				// Process navigation
				process_navigation = true;

				debug_action_language_id = 'debug_action_' + ((value == 'on') ? 'disabled' : 'enabled');
				break;

			// Required
			case 'required' :

				// Get field data
				var field = this.field_data_cache[object_id];
				switch(field.type) {

					case 'radio' :
					case 'price_radio' :

						obj = $('[name="' + field_name + '[]"]', obj_wrapper);
						break;
				}

				// Set required attribute
				obj.prop('required', (value == 'on')).removeAttr('data-init-required');

				if(value == 'on') {

					// Set ARIA required
					obj.attr('data-required', '').attr('aria-required', 'true').removeAttr('data-conditional-logic-bypass');

				} else {

					// Set ARIA not required
					obj.removeAttr('data-required').removeAttr('aria-required').attr('data-conditional-logic-bypass', '');
				}

				debug_action_language_id = 'debug_action_' + ((value == 'on') ? 'required' : 'not_required');

				process_required = true;
				process_bypass = true;

				break;

			// Required - Signature
			case 'required_signature' :

				// Set required attribute
				obj.prop('required', (value == 'on')).removeAttr('data-init-required');

				if(value == 'on') {

					// Set ARIA required
					obj.attr('data-required', '').attr('aria-required', 'true').removeAttr('data-conditional-logic-bypass');

				} else {

					// Set ARIA not required
					obj.removeAttr('data-required').removeAttr('aria-required').attr('data-conditional-logic-bypass', '');
				}

				debug_action_language_id = 'debug_action_' + ((value == 'on') ? 'required' : 'not_required');

				process_required = true;
				process_bypass = true;

				break;

			// Read only
			case 'readonly' :

				obj.prop('readonly', (value == 'on'));

				if(value == 'on') {

					obj.attr('aria-readonly', 'true');

				} else {

					obj.removeAttr('aria-readonly');
				}

				debug_action_language_id = 'debug_action_' + ((value == 'on') ? 'read_only' : 'not_read_only');

				this.form_date();	// Destroy jQuery component if readonly

				break;

			// Set custom validity
			case 'set_custom_validity' :

				// Check for invalid_feedback_last_row
				var invalid_feedback_last_row = false;
				if(typeof(this.field_data_cache[object_id]) !== 'undefined') {

					var field = this.field_data_cache[object_id];

					if(typeof($.WS_Form.field_type_cache[field.type]) !== 'undefined') {

						var field_type_config = $.WS_Form.field_type_cache[field.type];

						invalid_feedback_last_row = (typeof(field_type_config.invalid_feedback_last_row) !== 'undefined') ? field_type_config.invalid_feedback_last_row : false
					}
				}

				// Get the invalid feedback object
				var invalid_feedback_obj = $('[id^="' + this.form_id_prefix + 'invalid-feedback-' + object_id + '"]', obj_wrapper);

				// If invalid feedback is only available on the last row, then set obj to sibling input
				if(invalid_feedback_last_row) {

					var obj = invalid_feedback_obj.siblings('[id^="' + this.form_id_prefix + 'field-' + object_id + '"]');
				}

				// Set invalid feedback
				this.set_invalid_feedback(obj, invalid_feedback_obj, value, object_id);

				// Process bypass
				process_bypass = true;

				break;

			// Click
			case 'click' :

				switch(object) {

					// Tab click
					case 'group' :

						$('[href="#' + this.form_id_prefix + 'group-' + object_id + '"]').trigger('click');
						break;

					// Field click
					case 'field' :

						obj.trigger('click');
						break;
				}

				debug_action_language_id = 'debug_action_clicked';
				break;

			// Focus
			case 'focus' :

				obj.trigger('focus');
				debug_action_language_id = 'debug_action_focussed';

				break;

			// Action - Run
			case 'action_run' :

				if(this.conditional_actions_run_action.indexOf(object_id) !== -1) {

					this.form_post('action', object_id);
				}
				break;

			// Action - Enable on save
			case 'action_run_on_save' :

				if(this.conditional_actions_run_save.indexOf(object_id) === -1) {
					this.conditional_actions_run_save.push(object_id);
					this.conditional_actions_changed = true;
				}
				break;

			// Action - Enable on submit
			case 'action_run_on_submit' :

				if(this.conditional_actions_run_submit.indexOf(object_id) === -1) {
					this.conditional_actions_run_submit.push(object_id);
					this.conditional_actions_changed = true;
				}
				break;

			// Action - Disable on save
			case 'action_do_not_run_on_save' :

				var object_id_index = this.conditional_actions_run_save.indexOf(object_id);
				if (object_id_index !== -1) {
					this.conditional_actions_run_save.splice(object_id_index, 1);
					this.conditional_actions_changed = true;
				}
				break;

			// Action - Disable on submit
			case 'action_do_not_run_on_submit' :

				var object_id_index = this.conditional_actions_run_submit.indexOf(object_id);
				if (object_id_index !== -1) {
					this.conditional_actions_run_submit.splice(object_id_index, 1);
					this.conditional_actions_changed = true;
				}
				break;

			// Run JavaScript
			case 'javascript' :

				try {

					$.globalEval('(function($) {' + value + '})(jQuery);');

				} catch(e) {

					this.error('error_js', value);
				}
				break;

			// Form - Show validation
			case 'validate_show' :

				this.form_obj.addClass(this.class_validated);
				break;

			// Form - Hide validation
			case 'validate_hide' :

				this.form_obj.removeClass(this.class_validated);
				break;

			// Form - Save
			case 'form_save' :

				this.form_post('save');
				break;

			// Form - Save if validated
			case 'form_save_validate' :

				this.form_post_if_validated('save');
				break;

			// Form - Submit
			case 'form_submit' :

				this.form_obj.submit();
				break;

			// Form - Clear
			case 'form_clear' :

				this.form_clear();
				break;

			// Form - Reset
			case 'form_reset' :

				this.form_reset();
				break;

			// Reset
			// Clear
			case 'reset' :
			case 'clear' :

				var field_clear = (action === 'clear');

				switch(object) {

					case 'group' :

						this.group_fields_reset(object_id, field_clear);

						break;

					case 'section' :

						this.section_fields_reset(object_id, field_clear, section_repeatable_index);

						break;

					case 'field' :

						this.field_reset(object_id, field_clear, obj_wrapper);

						break;
				}

				break;
		}

		if($.WS_Form.debug_rendered) {

			var object_single_type = false;

			// Build action description for debug
			switch(object) {

				case 'form' :

					var object_single_type = this.language('debug_action_form');
					var object_single_label = this.language('debug_action_form');
					break;

				case 'group' :

					if(typeof(this.group_data_cache[object_id]) !== 'undefined') {

						var object_single = this.group_data_cache[object_id];
						var object_single_type = this.language('debug_action_group');
						var object_single_label = object_single.label;
					}
					break;

				case 'section' :

					if(typeof(this.section_data_cache[object_id]) !== 'undefined') {

						var object_single = this.section_data_cache[object_id];
						var object_single_type = this.language('debug_action_section');
						var object_single_label = object_single.label;
					}
					break;

				case 'field' :

					if(typeof(this.field_data_cache[object_id]) !== 'undefined') {

						var object_single = this.field_data_cache[object_id];						
						var object_single_type = object_single.type;
						var object_single_label = object_single.label;
					}
					break;

				case 'action' :

					if(typeof(this.action_data_cache[object_id]) !== 'undefined') {

						var object_single = this.action_data_cache[object_id];
						var object_single_type = this.language('debug_action_action');
						var object_single_label = object_single.label;
					}
					break;
			}

			if(object_single_type !== false) {

				if(debug_action_language_id !== false) { debug_action_value = this.language(debug_action_language_id); }

				var conditional_settings = $.WS_Form.settings_form.conditional;
				var conditional_settings_objects = conditional_settings.objects;
				var conditional_settings_actions = conditional_settings_objects[object]['action'];
				var conditional_settings_action = conditional_settings_actions[action];

				var action_description = conditional_settings_action.text.toUpperCase();
				if(typeof(conditional_settings_action.values) !== 'undefined') {

					if(typeof(conditional_settings_action.values) === 'object') {

						if(typeof(conditional_settings_action.values[value]) !== 'undefined') {

							debug_action_value = conditional_settings_action.values[value].text;
						}
					}
				}

				var log_description = '<strong>[' + this.html_encode(object_single_label) + '] ' + action_description + (((debug_action_value !== false) && (debug_action_value != '')) ? " '" + this.html_encode(debug_action_value) + "'" : '') + '</strong> (' + this.language('debug_action_type') + ': ' + object_single_type + ' | ID: ' + object_id + (object_row_id ? ' | ' + this.language('debug_action_row') + ' ID: ' + object_row_id : '') + ')';

				this.log('log_conditional_action_' + action_then_else, log_description, 'conditional');
			}
		}

		return { process_required: process_required, process_bypass: process_bypass, process_navigation: process_navigation };
	}

	// Set object attribute (if false, remove the attribute)
	$.WS_Form.prototype.obj_set_attribute = function(obj, attribute, value) {

		if(typeof(obj.attr('data-' + attribute + '-bypass')) !== 'undefined') {

			if(value !== false) {

				obj.attr('data-' + attribute + '-bypass', value).trigger('change');

			} else {

				obj.removeAttr('data-' + attribute + '-bypass').trigger('change');
			}

		} else {

			if(value !== false) {

				obj.attr(attribute, value).trigger('change');

			} else {

				obj.removeAttr(attribute).trigger('change');
			}
		}
	}

	$.WS_Form.prototype.group_fields_reset = function(group_id, field_clear) {

		if(typeof(this.group_data_cache[group_id]) === 'undefined') { return false; }

		// Get group
		var group = this.group_data_cache[group_id];
		if(typeof(group.sections) === 'undefined') { return false; }

		// Get all fields in group
		var sections = group.sections;

		for(var section_index in sections) {

			if(!sections.hasOwnProperty(section_index)) { continue; }

			var section = sections[section_index];

			this.section_fields_reset(section.id, field_clear, false);
		}
	}

	$.WS_Form.prototype.section_fields_reset = function(section_id, field_clear, section_repeatable_index) {

		if(typeof(this.section_data_cache[section_id]) === 'undefined') { return false; }

		// Get section
		var section = this.section_data_cache[section_id];
		if(typeof(section.fields) === 'undefined') { return false; }

		// Get all fields in section
		var fields = section.fields;

		for(var field_index in fields) {

			if(!fields.hasOwnProperty(field_index)) { continue; }

			var field = fields[field_index];
			var field_id = field.id;

			if(section_repeatable_index === false) {

				var object_selector_wrapper = '[id^="' + this.form_id_prefix + 'field-wrapper-' + field_id + '"][data-id="' + field.id + '"]';

			} else {

				var object_selector_wrapper = '#' + this.form_id_prefix + 'field-wrapper-' + field_id + '-repeat-' + section_repeatable_index;
			}

			var obj_wrapper = $(object_selector_wrapper, this.form_canvas_obj);

			this.field_reset(field_id, field_clear, obj_wrapper);
		}
	}

	$.WS_Form.prototype.field_reset = function(field_id, field_clear, obj_wrapper) {

		var ws_this = this;

		if(typeof(obj_wrapper) === 'undefined') { obj_wrapper = false; }

		if(typeof(this.field_data_cache[field_id]) === 'undefined') { return; }

		var field = this.field_data_cache[field_id];

		var field_type_config = $.WS_Form.field_type_cache[field.type];
		var trigger_action = (typeof(field_type_config.trigger) !== 'undefined') ? field_type_config.trigger : 'change';

		switch(field.type) {

			case 'select' :

				$('option', obj_wrapper).each(function() {

					var selected_new = field_clear ? false : $(this).prop('defaultSelected');
					var trigger = $(this).prop('selected') !== selected_new;
					$(this).prop('selected', selected_new);
					if(trigger) { $(this).trigger(trigger_action); }
				});
				break;

			case 'checkbox' :

				$('input[type="checkbox"]', obj_wrapper).each(function() {

					var checked_new = field_clear ? false : $(this).prop('defaultChecked');
					var trigger = $(this).prop('checked') !== checked_new;
					$(this).prop('checked', checked_new);
					if(trigger) { $(this).trigger(trigger_action); }
				});
				break;

			case 'radio' :

				$('input[type="radio"]', obj_wrapper).each(function() {

					var checked_new = field_clear ? false : $(this).prop('defaultChecked');
					var trigger = $(this).prop('checked') !== checked_new;
					$(this).prop('checked', checked_new);
					if(trigger) { $(this).trigger(trigger_action); }
				});
				break;

			case 'textarea' :

				$('textarea', obj_wrapper).each(function() {

					var val_new = field_clear ? '' : $(this).prop('defaultValue');
					var trigger = $(this).val() !== val_new;
					$(this).val(val_new);
					ws_this.textarea_set_value($(this), val_new);
					if(trigger) { $(this).trigger('change'); }
				});
				break;

			case 'color' :

				$('input', obj_wrapper).each(function() {

					var val_new = field_clear ? '' : $(this).prop('defaultValue');
					var trigger = $(this).val() !== val_new;
					$(this).val(val_new);
					if($(this).hasClass('minicolors-input')) {
						$(this).minicolors('value', {color: val_new});
					}
					if(trigger) { $(this).trigger('change'); }
				});
				break;

			case 'hidden' :

				// Hidden fields don't have a wrapper so the obj_wrapper is the field. You cannot use the defaultValue property on hidden fields as it gets update when val() is used, so we use data-default-value attribute instead.
				var val_new = field_clear ? '' : obj_wrapper.attr('data-default-value');
				var trigger = obj_wrapper.val() !== val_new;
				obj_wrapper.val(val_new);
				if(trigger) { obj_wrapper.trigger(trigger_action); }
				break;

			case 'googlemap' :

				$('input', obj_wrapper).each(function() {

					var val_new = field_clear ? '' : $(this).attr('data-default-value');
					var trigger = $(this).val() !== val_new;
					$(this).val(val_new);
					if(trigger) { $(this).trigger(trigger_action); }
				});
				break;

			default :

				$('input', obj_wrapper).each(function() {

					var val_new = field_clear ? '' : $(this).prop('defaultValue');
					var trigger = $(this).val() !== val_new;
					$(this).val(val_new);
					if(trigger) { $(this).trigger(trigger_action); }
				});
		}
	}

	$.WS_Form.prototype.conditional_logic_previous = function(accumulator, value, logic_previous) {

		switch(logic_previous) {

			// OR
			case '||' :

				accumulator |= value;
				break;

			// AND
			case '&&' :

				accumulator &= value;
				break;
		}

		return accumulator;
	}

	// Check integrity of a condition
	$.WS_Form.prototype.conditional_condition_check = function(condition) {

		return !(

			(condition === null) ||
			(typeof(condition) !== 'object') ||
			(typeof(condition.id) === 'undefined') ||
			(typeof(condition.object) === 'undefined') ||
			(typeof(condition.object_id) === 'undefined') ||
			(typeof(condition.object_row_id) === 'undefined') ||
			(typeof(condition.logic) === 'undefined') ||
			(typeof(condition.value) === 'undefined') ||
			(typeof(condition.case_sensitive) === 'undefined') ||
			(typeof(condition.logic_previous) === 'undefined') ||
			(condition.id == '') ||
			(condition.id == 0) ||
			(condition.object == '') ||
			(condition.object_id == '') ||
			(condition.logic == '')
		);
	}

	// Check integrity of an action
	$.WS_Form.prototype.conditional_action_check = function(action) {

		return !(

			(action === null) ||
			(typeof(action) !== 'object') ||
			(typeof(action.object) === 'undefined') ||
			(typeof(action.object_id) === 'undefined') ||
			(typeof(action.action) === 'undefined') ||
			(action.object == '') ||
			(action.object_id == '') ||
			(action.action == '')
		);
	}

	// Group - Tabs - Init
	$.WS_Form.prototype.form_tabs = function() {

		if(this.form.groups.length <= 1) { return false; }

		var ws_this = this;

		// Get tab index cookie if settings require it
		var index = (this.get_object_meta_value(this.form, 'cookie_tab_index')) ? this.cookie_get('tab_index', 0) : 0;

		// Check index is valid
		var tabs_obj = $('.wsf-group-tabs', this.form_canvas_obj);
		var li_obj = tabs_obj.children();
		if(
			(typeof(li_obj[index]) === 'undefined') ||
			(typeof($(li_obj[index]).attr('data-wsf-group-hidden')) !== 'undefined')
		) {

			index = 0;

			var li_obj_visible = $(':not([data-wsf-group-hidden])', li_obj);

			if(li_obj_visible.length) {

				index = li_obj_visible.first().index();
			}

			// Save current tab index to cookie
			if(ws_this.get_object_meta_value(ws_this.form, 'cookie_tab_index')) {

				ws_this.cookie_set('tab_index', index);
			}
		}

		// If we are using the WS Form framework, then we need to run our own tabs script
		if($.WS_Form.settings_plugin.framework == ws_form_settings.framework_admin) {

			// Destroy tabs (Ensures subsequent calls work)
			if(tabs_obj.hasClass('wsf-tabs')) { this.tabs_destroy(); }

			// Init tabs
			this.tabs(tabs_obj, { active: index });

		} else {

			// Set active tab
			this.group_index_set(index);
		}

		var framework_tabs = this.framework['tabs']['public'];

		if(typeof(framework_tabs.event_js) !== 'undefined') {

			var event_js = framework_tabs.event_js;
			var event_type_js = (typeof(framework_tabs.event_type_js) !== 'undefined') ? framework_tabs.event_type_js : false;
			var event_selector_wrapper_js = (typeof(framework_tabs.event_selector_wrapper_js) !== 'undefined') ? framework_tabs.event_selector_wrapper_js : false;
			var event_selector_active_js = (typeof(framework_tabs.event_selector_active_js) !== 'undefined') ? framework_tabs.event_selector_active_js : false;

			switch(event_type_js) {

				case 'wrapper' :

					var event_selector = $(event_selector_wrapper_js, this.form_canvas_obj);
					break;

				default :

					var event_selector = $('[href^="#' + this.form_id_prefix + 'group-"]', this.form_canvas_obj);
			}

			// Set up on click event for each tab
			event_selector.on(event_js, function (event, ui) {

				switch(event_type_js) {

					case 'wrapper' :

						var event_active_selector = $(event_selector_active_js, event_selector);
						var tab_index = event_active_selector.index();
						break;

					default :

						var tab_index = $(this).parent().index();
				}

				// Save current tab index to cookie
				if(ws_this.get_object_meta_value(ws_this.form, 'cookie_tab_index')) {

					ws_this.cookie_set('tab_index', tab_index);
				}

				// Object focus
				if(ws_this.object_focus !== false) {

					ws_this.object_focus.trigger('focus');
					ws_this.object_focus = false;
				}
			});
		}
	}

	// Tab validation
	$.WS_Form.prototype.form_tab_validation = function() {

		var ws_this = this;

		var tab_validation = this.get_object_meta_value(this.form, 'tab_validation');
		if(tab_validation) {

			this.form_canvas_obj.on('wsf-validate-silent', function() {

				ws_this.form_tab_validation_process();
			});

			this.form_tab_validation_process();
		}
	}

	// Tab validation
	$.WS_Form.prototype.form_tab_validation_process = function() {

		var tab_validation = this.get_object_meta_value(this.form, 'tab_validation');
		if(!tab_validation) { return; }

		var ws_this = this;

		var tab_validated_previous = true;

		// Get tabs
		var tabs = $('.wsf-group-tabs > :not([data-wsf-group-hidden]) > a', this.form_canvas_obj);

		// Get tab count
		var tab_count = tabs.length;

		// Get tab_index_current
		var tab_index_current = 0;
		tabs.each(function(tab_index) {

			var tab_visible = $($(this).attr('href')).is(':visible');
			if(tab_visible) {

				tab_index_current = tab_index;
				return false;
			}
		});

		tabs.each(function(tab_index) {

			// Render validation for previous tab
			ws_this.tab_validation_previous($(this), tab_validated_previous);

			// Validate tab
			if(tab_index < (tab_count - 1)) {

				if(tab_validated_previous === true) {

					var tab_validated_current = ws_this.object_validate($($(this).attr('href')));

				} else {

					var tab_validated_current = false;
				}

				// Render validation for current tab
				ws_this.tab_validation_current($(this), tab_validated_current);

				tab_validated_previous = tab_validated_current;
			}

			// If we are on a tab that is beyond the current invalidated tab, change tab to first invalidated tab
			if( !tab_validated_current &&
				(tab_index_current > tab_index)
			) {

				// Activate tab
				ws_this.group_index_set(tab_index);
			}
		});

		// Form navigation
		this.form_navigation();
	}

	// Tab validation - Current
	$.WS_Form.prototype.tab_validation_current = function(obj, tab_validated) {

		var tab_id = obj.attr('href');
		var tab_content_obj = $(tab_id, this.form_canvas_obj);
		var button_next_obj = $('button[data-action="wsf-tab_next"]', tab_content_obj);

		if(tab_validated) {

			button_next_obj.removeAttr('disabled');

		} else {

			button_next_obj.attr('disabled', '');
		}
	}

	// Tab validation - Previous
	$.WS_Form.prototype.tab_validation_previous = function(obj, tab_validated) {

		var framework_tabs = this.framework['tabs']['public'];

		if(typeof(framework_tabs.class_disabled) !== 'undefined') {

			if(tab_validated) {

				obj.removeClass(framework_tabs.class_disabled);

			} else {

				obj.addClass(framework_tabs.class_disabled);
			}
		}

		if(typeof(framework_tabs.class_parent_disabled) !== 'undefined') {

			if(tab_validated) {

				obj.parent().removeClass(framework_tabs.class_parent_disabled);

			} else {

				obj.parent().addClass(framework_tabs.class_parent_disabled);
			}
		}
	}

	// Form - Post
	$.WS_Form.prototype.form_post = function(post_mode, action_id) {

		if(typeof(post_mode) == 'undefined') { post_mode = 'save'; }
		if(typeof(action_id) == 'undefined') { action_id = 0; }

		// Determine if this is a submit
		var submit = (post_mode == 'submit');

		// Trigger post mode event
		this.trigger(post_mode);

		var ws_this = this;

		// Lock form
		this.form_post_lock();

		// Build form data
		this.form_add_hidden_input('wsf_form_id', this.form_id);
		this.form_add_hidden_input('wsf_hash', this.hash);
		this.form_add_hidden_input(ws_form_settings.wsf_nonce_field_name, ws_form_settings.wsf_nonce);

		// Tracking
		if(this.get_object_meta_value(this.form, 'tracking_duration', 'on') == 'on') {

			this.form_add_hidden_input('wsf_duration', Math.round((new Date().getTime() - this.date_start) / 1000));
		}


		// Reset date start
		if(post_mode == 'submit') {

			this.date_start = false;
			this.cookie_set('date_start', false, false);
			this.form_timer();
		}

		if((typeof(ws_form_settings.post_id) !== 'undefined') && (ws_form_settings.post_id > 0)) {

			this.form_add_hidden_input('wsf_post_id', ws_form_settings.post_id);
		}

		// Post mode
		this.form_add_hidden_input('wsf_post_mode', post_mode);

		// Work out which fields are hidden
		var hidden_array = $('[data-hidden],[data-hidden-section],[data-hidden-group]', ws_this.form_canvas_obj).map(function() {

			// Get name
			var name = $(this).attr('name');
			if(typeof(name) === 'undefined') {

				var name = $(this).attr('data-name');
				if(typeof(name) === 'undefined') {

					return '';
				}
			}

			// Strip brackets (For select, radio and checkboxes)
			name = name.replace('[]', '');

			return name;

		}).get();
		hidden_array = hidden_array.filter(function(value, index, self) { 

			return self.indexOf(value) === index;
		});
		var hidden = hidden_array.join();
		this.form_add_hidden_input('wsf_hidden', hidden);

		// Work out which required fields to bypass (because they are hidden) or no longer required because of conditional logic
		var bypass_required_array = $('[data-required-bypass],[data-required-bypass-section],[data-required-bypass-group],[data-conditional-logic-bypass]', this.form_canvas_obj).map(function() {

			// Get name
			var name = $(this).attr('name');

			// Strip brackets (For select, radio and checkboxes)
			name = name.replace('[]', '');

			return name;

		}).get();
		bypass_required_array = bypass_required_array.filter(function(value, index, self) { 

			return self.indexOf(value) === index;
		});
		var bypass_required = bypass_required_array.join();
		this.form_add_hidden_input('wsf_bypass_required', bypass_required);


		// Do not run AJAX
		if(
			(action_id == 0) &&
			(this.form_ajax === false)
		) {

			// We're done!
			ws_this.trigger(post_mode + '-complete');
			ws_this.trigger('complete');
			return;
		}

		// Trigger
		ws_this.trigger(post_mode + '-before-ajax');

		// Build form data
		var form_data = new FormData(this.form_obj[0]);

		// Action ID (Inject into form_data so that it doesn't stay on the form)
		if(action_id > 0) {

			form_data.append('wsf_action_id', action_id);
		}

		// Call API
		this.api_call('submit', 'POST', form_data, function(response) {

			// Success

			// Check for validation errors
			var error_validation = (typeof(response.error_validation) !== 'undefined') && response.error_validation;

			// Check for errors
			var errors = (

				(typeof(response.data) !== 'undefined') &&
				(typeof(response.data.errors) !== 'undefined') &&
				response.data.errors.length
			);

			// If response is invalid or form is being saved, force unlock it
			var form_post_unlock_force = (

				(typeof(response.data) === 'undefined') ||
				(post_mode == 'save') ||
				error_validation ||
				errors
			);

			// Unlock form
			ws_this.form_post_unlock('progress', !form_post_unlock_force, form_post_unlock_force);

			// Check for form reload on submit
			if(
				(submit && !error_validation && !errors)
			) {

				// Clear hash
				ws_this.form_hash_clear();

				if(ws_this.get_object_meta_value(ws_this.form, 'submit_reload', true)) {

					// Reload
					ws_this.form_reload();
				}
			}

			// Trigger error event
			if(errors || error_validation) {

				// Trigger post most complete event
				ws_this.trigger(post_mode + '-error');
				ws_this.trigger('error');
			}

			// Show error messages
			if(errors && ws_this.get_object_meta_value(ws_this.form, 'submit_show_errors', true)) {

				for(var error_index in response.data.errors) {

					if(!response.data.errors.hasOwnProperty(error_index)) { continue; }

					var error_message = response.data.errors[error_index];
					ws_this.action_message(error_message);
				}
			}

			ws_this.trigger(post_mode + '-complete');
			ws_this.trigger('complete');

			return !errors;

		}, function(response) {

			// Error
			ws_this.form_post_unlock('progress', true, true);


			// Show error message
			if(typeof(response.error_message) !== 'undefined') {

				ws_this.action_message(response.error_message);
			}

			// Trigger post most complete event
			ws_this.trigger(post_mode + '-error');
			ws_this.trigger('error');

		}, (action_id > 0));
	}

	// Form lock
	$.WS_Form.prototype.form_post_lock = function(cursor, force, ecommerce_calculate_disable) {

		if(typeof(cursor) === 'undefined') { cursor = 'progress'; }
		if(typeof(force) === 'undefined') { force = false; }
		if(typeof(ecommerce_calculate_disable) === 'undefined') { ecommerce_calculate_disable = false; }

		if(this.form_obj.hasClass('wsf-form-post-lock')) { return; }

		if(force || this.get_object_meta_value(this.form, 'submit_lock', false)) {

			// Stop further calculations
			if(ecommerce_calculate_disable) {

				this.form_ecommerce_calculate_enabled = false;
			}

			// Add locked class to form
			this.form_obj.addClass('wsf-form-post-lock' + (cursor ? ' wsf-form-post-lock-' + cursor : ''));

			// Disable submit buttons
			$('button[type="submit"].wsf-button, input[type="submit"].wsf-button, button[data-action="wsf-save"].wsf-button, button[data-ecommerce-payment].wsf-button, [data-post-lock]', this.form_canvas_obj).attr('disabled', '');
			this.form_post_locked = true;

			// Trigger lock event
			this.trigger('lock');

		}
	}

	// Form unlock
	$.WS_Form.prototype.form_post_unlock = function(cursor, timeout, force) {

		if(typeof(cursor) === 'undefined') { cursor = 'progress'; }
		if(typeof(timeout) === 'undefined') { timeout = true; }
		if(typeof(force) === 'undefined') { force = false; }

		if(!this.form_obj.hasClass('wsf-form-post-lock')) { return; }

		var ws_this = this;

		var unlock_fn = function() {

			// Re-enable cart calculations
			ws_this.form_ecommerce_calculate_enabled = true;

			// Remove locked class from form
			ws_this.form_obj.removeClass('wsf-form-post-lock' + (cursor ? ' wsf-form-post-lock-' + cursor : ''));

			// Enable submit buttons
			$('button[type="submit"].wsf-button, input[type="submit"].wsf-button, button[data-action="wsf-save"].wsf-button, button[data-ecommerce-payment].wsf-button, [data-post-lock]', ws_this.form_canvas_obj).removeAttr('disabled');
			ws_this.form_post_locked = false;

			// Reset post upload progress indicators
			ws_this.api_call_progress_reset();

			// Trigger unlock event
			ws_this.trigger('unlock');

		}

		if(force || this.get_object_meta_value(this.form, 'submit_unlock', false)) {

			// Enable post buttons
			timeout ? setTimeout(function() { unlock_fn(); }, 1000) : unlock_fn();
		}
	}

	// API Call
	$.WS_Form.prototype.api_call = function(ajax_path, method, params, success_callback, error_callback, force_ajax_path) {

		// Defaults
		if(typeof(method) === 'undefined') { method = 'POST'; }
		if(!params) { params = new FormData(); }
		if(typeof(force_ajax_path) === 'undefined') { force_ajax_path = false; }

		var ws_this = this;


		// Make AJAX request
		var url = force_ajax_path ? (ws_form_settings.url + ajax_path) : ((ajax_path == 'submit') ? this.form_obj.attr('action') : (ws_form_settings.url + ajax_path));

		// Check for custom action URL
		if(
			!force_ajax_path &&
			this.form_action_custom &&
			((ajax_path == 'submit') || (ajax_path == 'save'))
		) {

			// Custom action submit
			this.form_obj.off('submit');
			this.form_obj.submit();
			return true;
		}

		// NONCE
		if(
			(typeof(params.get) === 'undefined') || // Do it anyway for IE 11
			(params.get(ws_form_settings.wsf_nonce_field_name) === null)
		) {

			params.append(ws_form_settings.wsf_nonce_field_name, ws_form_settings.wsf_nonce);
		}

		// Convert FormData to object if making GET request (IE11 friendly code so not that elegant)
		if(method === 'GET') {

			var params_object = {};

			var form_data_entries = params.entries();
			var form_data_entry = form_data_entries.next();

			while (!form_data_entry.done) {

				var pair = form_data_entry.value;
				params_object[pair[0]] = pair[1];
				form_data_entry = form_data_entries.next();
			}

			params = params_object;
		}

		// Call AJAX
		var ajax_request = {

			method: method,
			url: url,
			beforeSend: function(xhr) {

				// Nonce (X-WP-Nonce)
				xhr.setRequestHeader('X-WP-Nonce', ws_form_settings.x_wp_nonce);
			},
			contentType: false,
			processData: (method === 'GET'),
 			statusCode: {

				// Success
				200: function(response) {

					// Handle hash response
					var hash_ok = ws_this.api_call_hash(response);

					// Call success function
					var success_callback_result = (typeof(success_callback) === 'function') ? success_callback(response) : true;

					// Check for data to process
					if(
						(typeof(response.data) !== 'undefined') &&
						success_callback_result
					) {

						// Check for nonce
						if(typeof(response.data.x_wp_nonce) !== 'undefined') { ws_form_settings.x_wp_nonce = response.data.x_wp_nonce; }

						// Check for action_js (These are returned from the action system to tell the browser to do something)
						if(typeof(response.data.js) === 'object') { ws_this.action_js_init(response.data.js); }
					}
				},

				// Bad request
				400: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 400, url, error_callback);
				},

				// Unauthorized
				401: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 401, url, error_callback);
				},

				// Forbidden
				403: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 403, url, error_callback);
				},

				// Not found
				404: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 404, url, error_callback);
				},

				// Server error
				500: function(response) {

					// Process error
					ws_this.api_call_error_handler(response, 500, url, error_callback);
				}
			},

			complete: function() {

				this.api_call_handle = false;
			}
		};

		// Data
		if(params !== false) { ajax_request.data = params; }

		// Progress
		var progress_objs = $('[data-source="post_progress"]', this.form_canvas_obj);
		if(progress_objs.length) {

			ajax_request.xhr = function() {

				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(e) { ws_this.api_call_progress(progress_objs, e); }, false);
				xhr.addEventListener("progress", function(e) { ws_this.api_call_progress(progress_objs, e); }, false);
				return xhr;
			};
		}

		return $.ajax(ajax_request);
	};

	// API call - Process error
	$.WS_Form.prototype.api_call_error_handler = function(response, status, url, error_callback) {

		// Get response data
		var data = (typeof(response.responseJSON) !== 'undefined') ? response.responseJSON : false;

		// Process WS Form API error message
		if(data && data.error) {

			if(data.error_message) {

				this.error('error_api_call_' + status, data.error_message);

			} else {

				this.error('error_api_call_' + status, url);
			}

		} else {

			// Fallback
			this.error('error_api_call_' + status, url);
		}

		// Call error call back
		if(typeof(error_callback) === 'function') {

			// Run error callback
			error_callback(data);

		}
	}

	// API Call - Progress
	$.WS_Form.prototype.api_call_progress = function(progress_objs, e) {

		if(!e.lengthComputable) { return; }

		var ws_this = this;

		progress_objs.each(function() {

			// Get progress value
			var progress_percentage = (e.loaded / e.total) * 100;

			// Set progress fields
			ws_this.form_progress_set_value($(this), Math.round(progress_percentage));
		});
	}

	// API Call - Progress
	$.WS_Form.prototype.api_call_progress_reset = function() {

		var ws_this = this;

		var progress_obj = $('[data-progress-bar][data-source="post_progress"]', this.form_canvas_obj);
		progress_obj.each(function() {

			ws_this.form_progress_set_value($(this), 0);
		});
	}

	// JS Actions - Init
	$.WS_Form.prototype.action_js_init = function(action_js) {

		// Trigger actions start event
		this.trigger('actions-start');

		this.action_js = action_js;

		this.action_js_process_next();
	};

	$.WS_Form.prototype.action_js_process_next = function() {

		if(this.action_js.length == 0) {

			// Trigger actions finish event
			this.trigger('actions-finish');

			return false;
		}

		var js_action = this.action_js.shift();

		var action = this.js_action_get_parameter(js_action, 'action');

		switch(action) {

			// Redirect
			case 'redirect' :

				var url = this.js_action_get_parameter(js_action, 'url');
				if(url !== false) { location.href = js_action['url']; }

				// Actions end at this point because of the redirect

				break;

			// Message
			case 'message' :

				var message = this.js_action_get_parameter(js_action, 'message');
				var type = this.js_action_get_parameter(js_action, 'type');
				var method = this.js_action_get_parameter(js_action, 'method');
				var duration = this.js_action_get_parameter(js_action, 'duration');
				var form_hide = this.js_action_get_parameter(js_action, 'form_hide');
				var clear = this.js_action_get_parameter(js_action, 'clear');
				var scroll_top = this.js_action_get_parameter(js_action, 'scroll_top');
				var scroll_top_offset = this.js_action_get_parameter(js_action, 'scroll_top_offset');
				var scroll_top_duration = this.js_action_get_parameter(js_action, 'scroll_top_duration');
				var form_show = this.js_action_get_parameter(js_action, 'form_show');
				var message_hide = this.js_action_get_parameter(js_action, 'message_hide');

				this.action_message(message, type, method, duration, form_hide, clear, scroll_top, scroll_top_offset, scroll_top_duration, form_show, message_hide);

				break;
			// Field invalid feedback
			case 'field_invalid_feedback' :

				var field_id = this.js_action_get_parameter(js_action, 'field_id');
				var invalid_feedback = this.js_action_get_parameter(js_action, 'invalid_feedback');

				// Field object
				var field_obj = $('#' + this.form_id_prefix + 'field-' + field_id, this.form_canvas_obj);

				// Custom invalid feedback text
				var invalid_feedback_obj = $('#' + this.form_id_prefix + 'invalid-feedback-' + field_id, this.form_canvas_obj);

				// Set invalid feedback
				this.set_invalid_feedback(field_obj, invalid_feedback_obj, invalid_feedback, field_id);

				// Process bypass
				process_bypass = true;

				var ws_this = this;

				// Reset if field modified
				field_obj.one('change input keyup', function() {

					var field_id = $(this).closest('[data-id]').attr('data-id');

					// Custom invalid feedback text
					var invalid_feedback_obj = $('#' + ws_this.form_id_prefix + 'invalid-feedback-' + field_id, this.form_canvas_obj);

					// Reset invalid feedback
					ws_this.set_invalid_feedback($(this), invalid_feedback_obj, '', field_id);
				})

				break;

			case 'trigger' :

				var event = this.js_action_get_parameter(js_action, 'event');
				var params = this.js_action_get_parameter(js_action, 'params');

				$(document).trigger(event, params);

				break;

			// Unknown
			default :

				this.action_js_process_next();
		}
	}

	// JS Actions - Get js_action config parameter from AJAX return
	$.WS_Form.prototype.js_action_get_parameter = function(js_action_parameters, meta_key) {

		return typeof(js_action_parameters[meta_key]) !== 'undefined' ? js_action_parameters[meta_key] : false;
	}

	// JS Actions - Get framework config value
	$.WS_Form.prototype.get_framework_config_value = function(object, meta_key) {

		if(typeof(this.framework[object]) === 'undefined') {
			return false;
		}
		if(typeof(this.framework[object]['public']) === 'undefined') {
			return false;
		}
		if(typeof(this.framework[object]['public'][meta_key]) === 'undefined') { return false; }

		return this.framework[object]['public'][meta_key];
	}

	// JS Action - Message
	$.WS_Form.prototype.action_message = function(message, type, method, duration, form_hide, clear, scroll_top, scroll_top_offset, scroll_top_duration, form_show, message_hide) {

		// Error message setting defaults
		if(typeof(type) === 'undefined') { type = this.get_object_meta_value(this.form, 'error_type', 'danger'); }
		if(typeof(method) === 'undefined') { method = this.get_object_meta_value(this.form, 'error_method', 'after'); }
		if(typeof(duration) === 'undefined') { duration = parseInt(this.get_object_meta_value(this.form, 'error_duration', '4000')); }
		if(typeof(form_hide) === 'undefined') { form_hide = (this.get_object_meta_value(this.form, 'error_form_hide', '') == 'on'); }
		if(typeof(clear) === 'undefined') { clear = (this.get_object_meta_value(this.form, 'error_clear', '') == 'on'); }
		if(typeof(scroll_top) === 'undefined') { scroll_top = (this.get_object_meta_value(this.form, 'error_scroll_top', '') == 'on'); }
		if(typeof(scroll_top_offset) === 'undefined') { scroll_top_offset = parseInt(this.get_object_meta_value(this.form, 'error_scroll_top_offset', '0')); }
		scroll_top_offset = (scroll_top_offset == '') ? 0 : parseInt(scroll_top_offset, 10);
		if(typeof(scroll_top_duration) === 'undefined') { scroll_top_duration = parseInt(this.get_object_meta_value(this.form, 'error_scroll_top_duration', '400')); }
		if(typeof(form_show) === 'undefined') { form_show = (this.get_object_meta_value(this.form, 'error_form_show', '') == 'on'); }
		if(typeof(message_hide) === 'undefined') { message_hide = (this.get_object_meta_value(this.form, 'error_message_hide', 'on') == 'on'); }

		var scroll_position = this.form_canvas_obj.offset().top - scroll_top_offset;

		// Parse duration
		duration = parseInt(duration, 10);
		if(duration < 0) { duration = 0; }

		// Get config
		var mask_wrapper = this.get_framework_config_value('message', 'mask_wrapper');
		var types = this.get_framework_config_value('message', 'types');

		var type = (typeof(types[type]) !== 'undefined') ? types[type] : false;
		var mask_wrapper_class = (typeof(type['mask_wrapper_class']) !== 'undefined') ? type['mask_wrapper_class'] : '';

		// Clear other messages
		if(clear) {

			$('[data-wsf-message][data-wsf-instance-id="' + this.form_instance_id + '"]').remove();
		}

		// Scroll top
		switch(scroll_top) {

			case 'instant' :
			case 'on' :			// Legacy

				$('html,body').scrollTop(scroll_position);

				break;

			// Smooth
			case 'smooth' :

				scroll_top_duration = (scroll_top_duration == '') ? 0 : parseInt(scroll_top_duration, 10);

				$('html,body').animate({

					scrollTop: scroll_position

				}, scroll_top_duration);

				break;
		}

		var mask_wrapper_values = {

			'message':				message,
			'mask_wrapper_class':	mask_wrapper_class 
		};

		var message_div = $('<div/>', { html: this.mask_parse(mask_wrapper, mask_wrapper_values) });
		message_div.attr('role', 'alert');
		message_div.attr('data-wsf-message', '');
		message_div.attr('data-wsf-instance-id', this.form_instance_id);

		// Hide form?
		if(form_hide) { this.form_obj.hide(); }

		// Render message
		switch(method) {

			// Before
			case 'before' :

				message_div.insertBefore(this.form_obj);
				break;

			// After
			case 'after' :

				message_div.insertAfter(this.form_obj);
				break;
		}

		// Process next action
		var ws_this = this;

		duration = parseInt(duration, 10);

		if(duration > 0) {

			setTimeout(function() {

				// Should this message be removed?
				if(message_hide) { message_div.remove(); }

				// Should the form be shown?
				if(form_show) { ws_this.form_canvas_obj.show(); }

				// Process next js_action
				ws_this.action_js_process_next();

			}, duration);

		} else {

			// Process next js_action
			ws_this.action_js_process_next();
		}
	}
	// Text input and textarea character and word count
	$.WS_Form.prototype.form_character_word_count = function(obj) {

		var ws_this = this;
		if(typeof(obj) === 'undefined') { obj = this.form_canvas_obj; }

		// Run through each input that accepts text
		for(var field_id in this.field_data_cache) {

			if(!this.field_data_cache.hasOwnProperty(field_id)) { continue; }

			var field = this.field_data_cache[field_id];

			// Process help?
			var help = this.get_object_meta_value(field, 'help', '', false, true);
			var process_help = (

				(help.indexOf('#character_') !== -1) ||
				(help.indexOf('#word_') !== -1)
			);

			// Process min or max?
			var process_min_max = (

				this.has_object_meta_key(field, 'min_length') ||
				this.has_object_meta_key(field, 'max_length') ||
				this.has_object_meta_key(field, 'min_length_words') ||
				this.has_object_meta_key(field, 'max_length_words')
			);

			if(process_min_max || process_help) {

				// Process count functionality on field
				var field_obj = $('#' + this.form_id_prefix + 'field-' + field_id, obj);
				if(!field_obj.length) { field_obj = $('[id^="' + this.form_id_prefix + 'field-' + field_id + '-"]:not([data-init-char-word-count]):not(iframe)', obj); }

				field_obj.each(function() {

					// Flag so it only initializes once
					$(this).attr('data-init-char-word-count', '');

					if(ws_this.form_character_word_count_process($(this))) {

						$(this).on('keyup change focus blur paste', function() { ws_this.form_character_word_count_process($(this)); });
					}
				});
			}
		}
	}

	// Text input and textarea character and word count - Process
	$.WS_Form.prototype.form_character_word_count_process = function(obj) {

		// Get help text
		var field_wrapper = obj.closest('[data-type]');
		var field_id = field_wrapper.attr('data-id');
		var field_repeatable_index = field_wrapper.attr('data-repeatable-index');
		var repeatable_suffix = (typeof(field_repeatable_index) !== 'undefined') ? '-repeat-' + field_repeatable_index : '';
		var field = this.field_data_cache[field_id];

		// Process invalid feedback

		// Get minimum and maximum character count
		var min_length = this.get_object_meta_value(field, 'min_length', '');
		min_length = (parseInt(min_length, 10) > 0) ? parseInt(min_length, 10) : false;

		var max_length = this.get_object_meta_value(field, 'max_length', '');
		max_length = (parseInt(max_length, 10) > 0) ? parseInt(max_length, 10) : false;

		// Get minimum and maximum word length
		var min_length_words = this.get_object_meta_value(field, 'min_length_words', '');
		min_length_words = (parseInt(min_length_words, 10) > 0) ? parseInt(min_length_words, 10) : false;

		var max_length_words = this.get_object_meta_value(field, 'max_length_words', '');
		max_length_words = (parseInt(max_length_words, 10) > 0) ? parseInt(max_length_words, 10) : false;

		// Calculate sizes
		var val = obj.val();

		var character_count = val.length;
		var character_remaining = (max_length !== false) ? max_length - character_count : false;
		if(character_remaining < 0) { character_remaining = 0; }

		var word_count = this.get_word_count(val);
		var word_remaining = (max_length_words !== false) ? max_length_words - word_count : false;
		if(word_remaining < 0) { word_remaining = 0; }

		// Check minimum and maximums counts
		var count_invalid = false;
		var count_invalid_message_array = [];

		if((min_length !== false) && (character_count < min_length)) {

			count_invalid_message_array.push(this.language('error_min_length', min_length));
			count_invalid = true;
		}
		if((max_length !== false) && (character_count > max_length)) {

			count_invalid_message_array.push(this.language('error_max_length', max_length));
			count_invalid = true;
		}
		if((min_length_words !== false) && (word_count < min_length_words)) {

			count_invalid_message_array.push(this.language('error_min_length_words', min_length_words));
			count_invalid = true;
		}
		if((max_length_words !== false) && (word_count > max_length_words)) {

			count_invalid_message_array.push(this.language('error_max_length_words', max_length_words));
			count_invalid = true;
		}

		// Get repeatable suffix
		var field_repeatable_index = field_wrapper.attr('data-repeatable-index');
		var repeatable_suffix = (typeof(field_repeatable_index) !== 'undefined') ? '-repeat-' + field_repeatable_index : '';

		// Custom invalid feedback text
		var invalid_feedback_obj = $('#' + this.form_id_prefix + 'invalid-feedback-' + field_id + repeatable_suffix, this.form_canvas_obj);

		// Check if required
		if(
			(typeof(obj.attr('required')) !== 'undefined') ||
			(val.length > 0)
		) {

			// Check if count_invalid
			if(count_invalid) {

				// Set invalid feedback
				this.set_invalid_feedback(obj, invalid_feedback_obj, count_invalid_message_array.join(' / '), field_id);

			} else {

				// Reset invalid feedback
				this.set_invalid_feedback(obj, invalid_feedback_obj, '', field_id);
			}

		} else {

			// Reset invalid feedback
			this.set_invalid_feedback(obj, invalid_feedback_obj, '', field_id);
		}

		// Process help
		var help = this.get_object_meta_value(field, 'help', '', false, true);

		// If #character_ and #word_ not present, don't bother processing
		if(
			(help.indexOf('#character_') === -1) &&
			(help.indexOf('#word_') === -1)
		) {
			return true;
		}

		// Get language
		var character_singular = this.language('character_singular');
		var character_plural = this.language('character_plural');
		var word_singular = this.language('word_singular');
		var word_plural = this.language('word_plural');

		// Set mask values
		var mask_values_help = {

			// Characters
			'character_count':				character_count,
			'character_count_label':		(character_count == 1 ? character_singular : character_plural),
			'character_remaining':			(character_remaining !== false) ? character_remaining : '',
			'character_remaining_label':	(character_remaining == 1 ? character_singular : character_plural),
			'character_min':				(min_length !== false) ? min_length : '',
			'character_min_label':			(min_length !== false) ? (min_length == 1 ? character_singular : character_plural) : '',
			'character_max':				(max_length !== false) ? max_length : '',
			'character_max_label':			(max_length !== false) ? (max_length == 1 ? character_singular : character_plural) : '',

			// Words
			'word_count':			word_count,
			'word_count_label':		(word_count == 1 ? word_singular : word_plural),
			'word_remaining':		(word_remaining !== false) ? word_remaining : '',
			'word_remaining_label': (word_remaining == 1 ? word_singular : word_plural),
			'word_min':				(min_length_words !== false) ? min_length_words : '',
			'word_min_label':		(min_length_words !== false) ? (min_length_words == 1 ? word_singular : word_plural) : '',
			'word_max':				(max_length_words !== false) ? max_length_words : '',
			'word_max_label':		(max_length_words !== false) ? (max_length_words == 1 ? word_singular : word_plural) : ''
		};

		// Parse help mask
		var help_parsed = this.mask_parse(help, mask_values_help);

		// Update help HTML
		var help_id = this.form_id_prefix + 'help-' + field_id + repeatable_suffix;
		$('#' + help_id, this.form_canvas_obj).html(help_parsed);

		return true;
	}

	// Get word count of a string
	$.WS_Form.prototype.get_word_count = function(input_string) {

		// Trim input string
		input_string = input_string.trim();

		// If string is empty, return 0
		if(input_string.length == 0) { return 0; }

		// Return word count
		return input_string.trim().replace(/\s+/gi, ' ').split(' ').length;
	}

	// API Call
	$.WS_Form.prototype.api_call_hash = function(response) {

		var hash_ok = true;
		if(typeof(response.hash) === 'undefined') { hash_ok = false; }
		if(hash_ok && (response.hash.length != 32)) { hash_ok = false; }
		if(hash_ok) {

			// Set hash
			this.hash_set(response.hash)
		}

		return hash_ok;
	}

	// Hash - Set
	$.WS_Form.prototype.hash_set = function(hash, token, cookie_set) {

		if(typeof(token) === 'undefined') { token = false; }
		if(typeof(cookie_set) === 'undefined') { cookie_set = false; }

		if(hash != this.hash) {

			// Set hash
			this.hash = hash;

			// Set hash cookie
			cookie_set = true;

		}

		if(token) {

			// Set token
			this.token = token;

		}

		if(cookie_set) {

			var cookie_hash = this.get_object_value($.WS_Form.settings_plugin, 'cookie_hash');

			if(cookie_hash) {

				this.cookie_set('hash', this.hash);
			}
		}
	}

	// Generate password
	$.WS_Form.prototype.generate_password = function(length) {

		var password = '';
		var characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~`|}{[]\\:;?><,./-=';
		
		for(var i = 0; i < length; ++i) { password += characters.charAt(Math.floor(Math.random() * characters.length)); }

		return password;
	}

	// Form - Statistics
	$.WS_Form.prototype.form_stat = function() {

		// Add view
		if(ws_form_settings.stat) { this.form_stat_add_view(); }
	}

	// Add view statistic
	$.WS_Form.prototype.form_stat_add_view = function() {

		// Call AJAX
		$.ajax({ method: 'POST', url: ws_form_settings.add_view_url, data: { wsffid: this.form_id } });
	}

	// Initialize forms function
	window.wsf_form_instances = [];

	window.wsf_form_init = function(force_reload) {

		if(typeof(force_reload) === 'undefined') { force_reload = false; }

		if(!$('.wsf-form').length) { return; }

		// Get highest instance ID
		var set_instance_id = 0;
		var instance_id_array = [];
		$('.wsf-form').each(function() {

			if(typeof($(this).attr('data-instance-id')) === 'undefined') { return; }

			// Get instance ID
			var instance_id_single = parseInt($(this).attr('data-instance-id'));

			// Check for duplicate instance ID
			if(instance_id_array.indexOf(instance_id_single) !== -1) {

				// If duplicate, remove the data-instance-id so it is reset
				$(this).removeAttr('data-instance-id');

			} else {

				// Check if this is the highest instance ID
				if(instance_id_single > set_instance_id) { set_instance_id = instance_id_single; }
			}

			instance_id_array.push(instance_id_single);
		});

		// Increment to next instance ID
		set_instance_id++;

		// Render each form
		$('.wsf-form').each(function() {

			// Skip forms already initialized
			if(!force_reload && $(this).html() !== '') { return; }

			// Set instance ID
			if(typeof($(this).attr('data-instance-id')) === 'undefined') {

				// Set ID (Only if custom ID not set)
				if(typeof($(this).attr('data-wsf-custom-id')) === 'undefined') {

					$(this).attr('id', 'ws-form-' + set_instance_id);
				}

				// Set instance ID
				$(this).attr('data-instance-id', set_instance_id);

				set_instance_id++;
			}

			// Get attributes
			var id = $(this).attr('id');
			var form_id = $(this).attr('data-id');
			var instance_id = $(this).attr('data-instance-id');

			if(id && form_id && instance_id) {

				// Initiate new WS Form object
				var ws_form = new $.WS_Form();

				// Save to wsf_form_instances array
				window.wsf_form_instances[instance_id] = ws_form;

				// Render
				ws_form.render({

					'obj' :			'#' + id,
					'form_id':		form_id
				});
			}
		});
	}

	// On load
	$(function() { wsf_form_init(); });

})(jQuery);


