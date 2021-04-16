/**
 * EasyPost admin jquery file.
 */
jQuery( document ).ready( function( $ ) {
	'use strict';

	var {
		ajaxurl,
		carrier_empty,
		tracking_id_empty,
		notification_error_header,
		notification_success_header,
		processing_button_text,
		wcep_ajax_nonce,
		invalid_ajax_response,
		ajax_nonce_failure,
		in_inches,
		generating_label_preview_btn_text,
		regenerate_shipping_label_btn_text,
		regenerate_return_label_btn_text,
		no_labels_available_error,
		available_labels_heading,
		email_recipients_heading,
		postage_label_title,
		return_label_title,
		add_recipient_title,
		remove_recipient_title,
		selected_labels_empty,
		email_recipients_empty,
	} = WCEP_Admin_JS_Obj;

	var link_clicked = '';

	/**
	 * Enable/disable sandbox mode.
	 */
	$( document ).on( 'click', '#wcep_enable_easypost_sandbox', function() {
		if ( $( this ).is( ':checked' ) ) {
			$( '#wcep_sandbox_api_key' ).parents( 'tr' ).show();
			$( '#wcep_production_api_key' ).parents( 'tr' ).hide();
		} else {
			$( '#wcep_sandbox_api_key' ).parents( 'tr' ).hide();
			$( '#wcep_production_api_key' ).parents( 'tr' ).show();
		}
	} );

	/**
	 * Show/hide the api keys input boxes based on sandbox modes.
	 */
	if ( $( '#wcep_sandbox_api_key' ).hasClass( 'dnone' ) ) {
		$( '#wcep_sandbox_api_key' ).parents( 'tr' ).hide();
	}

	if ( $( '#wcep_production_api_key' ).hasClass( 'dnone' ) ) {
		$( '#wcep_production_api_key' ).parents( 'tr' ).hide();
	}

	// Datepicker for shipment date.
	$( '.wcep-shipment-date' ).datepicker( {
		dateFormat: 'yy-mm-dd'
	} );

	/**
	 * Save tracking info.
	 */
	$( document ).on( 'click', 'input[name="wcep-show-tracking-info"]', function( e ) {
		e.preventDefault();

		var this_btn = $( this );
		var this_btn_text = this_btn.val();
		var error_li = '';
		var carrier = $( '#wcep-shipment-carrier' ).val();
		var tracking_id = $( '#wcep-shipment-tracking-id' ).val();

		if ( '' === carrier ) {
			error_li += '<li>' + carrier_empty + '</li>';
		}

		if ( '' === tracking_id ) {
			error_li += '<li>' + tracking_id_empty + '</li>';
		}

		if ( '' !== error_li ) {
			var error = '<ol type="1" style="margin: 0 0 0 10px;">' + error_li + '</ol>';
			wcep_show_notification( 'fa fa-warning', notification_error_header, error, 'error' );
			return false;
		}
		
		var shipment_date = $( '#wcep-shipment-date' ).val();
		var order_id = $( '#post_ID' ).val();

		this_btn.val( processing_button_text );

		block_element( $( '#wcep-track-shipment' ) );
		// Send AJAX to save tracking info.
		var data = {
			action: 'save_tracking_info',
			carrier: carrier,
			tracking_id: tracking_id,
			shipment_date: shipment_date,
			wcep_ajax_nonce: wcep_ajax_nonce,
			order_id: order_id
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				unblock_element( $( '#wcep-track-shipment' ) );
				this_btn.val( this_btn_text );

				// Check for invalid ajax response.
				if ( 0 === response ) {
					wcep_show_notification( 'fa fa-warning', notification_error_header, invalid_ajax_response, 'error' );
					return false;
				}

				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcep_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				var {code, notification_message, html} = response.data;

				if ( 'wcep-tracking-info-saved' === code ) {
					wcep_show_notification( 'fa fa-check', notification_success_header, notification_message, 'success' );
					$( '.wcep-tracking-information' ).html( html );
				} else if ( 'wcep-tracking-info-not-saved' === code ) {
					wcep_show_notification( 'fa fa-warning', notification_error_header, notification_message, 'error' );
					return false;
				}
			},
		} );

		// This is done to prevent the form submission.
		return false;
	} );

	/**
	 * Shipping/Return label.
	 */
	$( document ).on( 'click', '.wcep-print-label-actions .shipping-label', function( e ) {
		e.preventDefault();

		var this_btn = $( this );
		var this_btn_text = this_btn.html();
		var order_id = $( '#post_ID' ).val();
		var items = [];
		var is_return = 'no';

		// Loop in the items to fetch their desired information.
		$( '.wcep-line-items-tbody tr' ).each( function() {
			var this_row = $( this );

			// Get the product ID.
			var product_id = this_row.data( 'product_id' );

			// Get the item ID.
			var item_id = this_row.data( 'order_item_id' );

			// Get the item dimensions.
			var length = this_row.find( 'td.item_dimensions' ).data( 'item_length' );
			var width  = this_row.find( 'td.item_dimensions' ).data( 'item_width' );
			var height = this_row.find( 'td.item_dimensions' ).data( 'item_height' );

			// Get the item weight.
			var weight = this_row.find( 'td.item_weight' ).data( 'item_weight' );

			// Collect all data into a temp array.
			var temp_arr = {};
			temp_arr['item_id'] = item_id;
			temp_arr['product_id'] = product_id;
			temp_arr['length'] = parseFloat( length );
			temp_arr['width'] = parseFloat( width );
			temp_arr['height'] = parseFloat( height );
			temp_arr['weight'] = parseFloat( weight );

			// Push the data into the array.
			items.push( temp_arr );
		} );

		// Check to see if the request is for the return label.
		if ( this_btn.hasClass( 'is-return' ) ) {
			is_return = 'yes';
		}

		// Process ajax now.
		this_btn.text( processing_button_text );
		block_element( $( '#wcep-shipment-label-metabox' ) );

		var data = {
			action: 'generate_shipping_label',
			wcep_ajax_nonce: wcep_ajax_nonce,
			order_id: order_id,
			items: items,
			is_return: is_return
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				unblock_element( $( '#wcep-shipment-label-metabox' ) );
				this_btn.text( this_btn_text );

				// Check for invalid ajax response.
				if ( 0 === response ) {
					wcep_show_notification( 'fa fa-warning', notification_error_header, invalid_ajax_response, 'error' );
					return false;
				}

				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcep_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				var {code, notification_message, postage_labels} = response.data;

				if ( 'wcep-postage-labels-created' === code ) {
					wcep_show_notification( 'fa fa-check', notification_success_header, notification_message, 'success' );

					// If postage labels are created.
					if ( 0 < postage_labels.length ) {
						wcep_generate_postage_labels_preview( postage_labels, this_btn, is_return );
					}
				} else if ( 'wcep-postage-labels-not-created' === code ) {
					wcep_show_notification( 'fa fa-warning', notification_error_header, notification_message, 'error' );
					return false;
				}
			},
		} );
	} );

	/**
	 * Generate the postage labels preview.
	 *
	 * @param {array} postage_labels
	 * @param {object} this_btn
	 * @param {string} is_return
	 */
	function wcep_generate_postage_labels_preview( postage_labels, this_btn, is_return ) {
		this_btn.text( generating_label_preview_btn_text );
		block_element( $( '#wcep-shipment-label-metabox' ) );

		// Send AJAX to generate postage labels.
		var data = {
			action: 'generate_postage_label_preview',
			postage_labels: postage_labels,
			wcep_ajax_nonce: wcep_ajax_nonce,
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				unblock_element( $( '#wcep-shipment-label-metabox' ) );

				// Update the button text.
				if ( 'yes' === is_return ) {
					this_btn.text( regenerate_return_label_btn_text );
				} else {
					this_btn.text( regenerate_shipping_label_btn_text );
				}				

				// Check for invalid ajax response.
				if ( 0 === response ) {
					console.log( 'lkoo' );
					wcep_show_notification( 'fa fa-warning', notification_error_header, invalid_ajax_response, 'error' );
					return false;
				}

				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcep_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				var {code, notification_message, html} = response.data;

				if ( 'wcep-postage-labels-preview-created' === code ) {
					wcep_show_notification( 'fa fa-check', notification_success_header, notification_message, 'success' );

					if ( 'yes' === is_return ) {
						$( '.wcep-return-labels .labels' ).html( html );
					} else {
						$( '.wcep-postage-labels .labels' ).html( html );
					}
				}
			},
		} );
	}

	/**
	 * Open email label modal.
	 */
	$( document ).on( 'click', '.wcep-print-label-actions .email-label', function( e ) {
		e.preventDefault();

		var postage_labels_count = $( '#wcep-labels-count' ).data( 'postage-labels' );
		var return_labels_count = $( '#wcep-labels-count' ).data( 'return-labels' );

		if ( 0 === postage_labels_count && 0 === return_labels_count ) {
			wcep_show_notification( 'fa fa-warning', notification_error_header, no_labels_available_error, 'error' );
			return false;
		}

		var html = '<div class="wcep-available-labels">';
		html += '<h3>' + available_labels_heading + '</h3>';

		if ( 0 < postage_labels_count ) {
			html += '<p>';
			html += '<input type="checkbox" id="count-postage-labels" checked />';
			html += '<label for="count-postage-labels">' + postage_label_title + '</label>';
			html += '</p>';
		}

		if ( 0 < return_labels_count ) {
			html += '<p>';
			html += '<input type="checkbox" id="count-return-labels" checked />';
			html += '<label for="count-return-labels">' + return_label_title + '</label>';
			html += '</p>';
		}

		html += '</div>';
		html += '<div class="wcep-email-recipients">';
		html += '<h3>' + email_recipients_heading + '</h3>';
		html += '<div class="email-recipient">';
		html += '<input type="email" placeholder="xyz@example.com" />';
		html += '<input type="button" class="button button-secondary" id="add-recipient" value="+" title="' + add_recipient_title + '" />';
		html += '</div>';
		html += '</div>';

		$( '#wcep-email-label-modal .content' ).html( html );
		$( '#wcep-email-label-modal' ).addClass( 'open' );
	} );

	/**
	 * Add email recipient.
	 */
	$( document ).on( 'click', '#add-recipient', function( e ) {
		e.preventDefault();

		var html = '<div class="email-recipient">';
		html += '<input type="email" placeholder="xyz@example.com" />';
		html += '<input type="button" class="button button-secondary remove-recipient" value="-" title="' + remove_recipient_title + '" />';
		html += '</div>';

		$( '.wcep-email-recipients' ).append( html );
	} );

	/**
	 * Remove email recipient.
	 */
	$( document ).on( 'click', '.remove-recipient', function( e ) {
		e.preventDefault();
		$( this ).parent( '.email-recipient' ).remove();
	} );

	/**
	 * Email label.
	 */
	$( document ).on( 'click', '.wcep-email-label', function( e ) {
		e.preventDefault();

		var this_btn = $( this );
		var this_btn_text = this_btn.text();
		var order_id = $( '#post_ID' ).val();
		var email_postage_label = 'no';
		var email_return_label = 'no';
		var error_li = '';
		var email_recipients = [];

		if ( $( '#count-postage-labels' ).is( ':checked' ) ) {
			email_postage_label = 'yes';
		}

		if ( $( '#count-return-labels' ).is( ':checked' ) ) {
			email_return_label = 'yes';
		}

		if ( 'no' === email_postage_label && 'no' === email_return_label ) {
			error_li += '<li>' + selected_labels_empty + '</li>';
		}

		// Gather the email recipients.
		$( '.email-recipient input[type="email"]' ).each( function() {
			var email = $( this ).val();

			if ( '' !== email && 1 === is_valid_email( email ) ) {
				email_recipients.push( email );
			}
		} );

		// Check if there's any valid email received.
		if ( 0 === email_recipients.length ) {
			error_li += '<li>' + email_recipients_empty + '</li>';
		}

		if ( '' !== error_li ) {
			var error = '<ol type="1" style="margin: 0 0 0 10px;">' + error_li + '</ol>';
			wcep_show_notification( 'fa fa-warning', notification_error_header, error, 'error' );
			return false;
		}

		// Process ajax now.
		this_btn.text( processing_button_text );
		block_element( this_btn );

		var data = {
			action: 'email_label',
			wcep_ajax_nonce: wcep_ajax_nonce,
			order_id: order_id,
			email_recipients: email_recipients,
			email_postage_label: email_postage_label,
			email_return_label: email_return_label,
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				unblock_element( this_btn );
				this_btn.text( this_btn_text );

				// Check for invalid ajax response.
				if ( 0 === response ) {
					wcep_show_notification( 'fa fa-warning', notification_error_header, invalid_ajax_response, 'error' );
					return false;
				}

				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcep_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				var {code, notification_message} = response.data;

				if ( 'wcep-labels-emailed' === code ) {
					wcep_show_notification( 'fa fa-check', notification_success_header, notification_message, 'success' );
					$( '.wcep-modal' ).removeClass( 'open' );
				}
			},
		} );
	} );

	/**
	 * Edit line item.
	 */
	$( document ).on( 'click', '.wcep-edit-line-item', function ( e ) {
		e.preventDefault();
		var this_btn = $( this );
		link_clicked = this_btn;

		// Get the item dimensions.
		var length = this_btn.parents( 'tr' ).find( 'td.item_dimensions' ).data( 'item_length' );
		var width  = this_btn.parents( 'tr' ).find( 'td.item_dimensions' ).data( 'item_width' );
		var height = this_btn.parents( 'tr' ).find( 'td.item_dimensions' ).data( 'item_height' );

		// Get the item weight.
		var weight = this_btn.parents( 'tr' ).find( 'td.item_weight' ).data( 'item_weight' );

		// Put in the values.
		$( '#wcep-item-length' ).val( length );
		$( '#wcep-item-width' ).val( width );
		$( '#wcep-item-height' ).val( height );
		$( '#wcep-item-weight' ).val( weight );

		$( '#wcep-edit-line-item-modal' ).addClass( 'open' );
	} );

	/**
	 * Close modal.
	 */
	$( document ).on( 'click', '.close', function() {
		$( '.wcep-modal' ).removeClass( 'open' );
	} );

	/**
	 * Update the dimensions on line item.
	 */
	$( document ).on( 'click', '.wcep-update-order-line-item', function() {
		var length = parseFloat( $( '#wcep-item-length' ).val() );
		var width = parseFloat( $( '#wcep-item-width' ).val() );
		var height = parseFloat( $( '#wcep-item-height' ).val() );
		var weight = parseFloat( $( '#wcep-item-weight' ).val() );

		// Update the values.
		link_clicked.parents( 'tr' ).find( 'td.item_dimensions' ).data( 'item_length', length );
		link_clicked.parents( 'tr' ).find( 'td.item_dimensions' ).data( 'item_width', width );
		link_clicked.parents( 'tr' ).find( 'td.item_dimensions' ).data( 'item_height', height );
		link_clicked.parents( 'tr' ).find( 'td.item_weight' ).data( 'item_weight', weight );

		// Update the dimensions html.
		var dimensions_html = '<div class="view">' + length + '*' + width + '*' + height + '<br><small class="times">' + in_inches + '</small></div>';
		link_clicked.parents( 'tr' ).find( 'td.item_dimensions' ).html( dimensions_html );

		// Update the weight html.
		var weight_in_lbs = weight * 0.0625;
		weight_in_lbs = weight_in_lbs.toFixed( 1 );
		
		var weight_html = '<div class="view">' + weight + ' <small class="times">oz</small></div>';
		weight_html += '<div class="view">' + weight_in_lbs + ' <small class="times">lbs</small></div>';
		link_clicked.parents( 'tr' ).find( 'td.item_weight' ).html( weight_html );

		// Close the modal now.
		$( '.wcep-modal' ).removeClass( 'open' );
	} );

	/**
	 * Close the notification.
	 */
	$( document ).on( 'click', '.wcep_notification_close', function() {
		wcep_hide_notification();
	} );

	/**
	 * Validate email.
	 *
	 * @param {string} email 
	 */
	function is_valid_email( email ) {
		var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;

		if ( ! regex.test( email ) ) {
			return -1;
		}

		return 1;
	}

	/**
	 * Block element.
	 *
	 * @param {string} element 
	 */
	function block_element( element ) {
		element.addClass( 'non-clickable' );
	}

	/**
	 * Unblock element.
	 *
	 * @param {string} element 
	 */
	function unblock_element( element ) {
		element.removeClass( 'non-clickable' );
	}

	/**
	 * Function defined to show the notification.
	 *
	 * @param {string} icon_class
	 * @param {string} header_text
	 * @param {string} message
	 * @param {string} success_or_error
	 */
	function wcep_show_notification( icon_class, header_text, message, success_or_error ) {
		$('.wcep_notification_popup .wcep_notification_icon i').removeClass().addClass( icon_class );
		$('.wcep_notification_popup .wcep_notification_message h3').html( header_text );
		$('.wcep_notification_popup .wcep_notification_message p').html( message );
		$('.wcep_notification_popup').removeClass('is-success is-error');

		if ( 'error' === success_or_error ) {
			$( '.wcep_notification_popup' ).addClass( 'active is-error' );
		} else if ( 'success' === success_or_error ) {
			$( '.wcep_notification_popup' ).addClass( 'active is-success' );
		}

		// Dismiss the notification after 3 secs.
		setTimeout( function () {
			wcep_hide_notification();
		}, 3000 );
	}

	/**
	 * Function to hide notification
	 */
	function wcep_hide_notification() {
		$( '.wcep_notification_popup' ).removeClass( 'active' );
	}

	/**
	 * Close modal on esc key press.
	 */
	$( document ).on( 'keyup', function( e ) {
		if ( 27 === e.keyCode ) {
			$( '.wcep-modal' ).removeClass( 'open' );
		}
	} );
} );
