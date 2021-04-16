/**
 * EasyPost public jquery file.
 */
jQuery( document ).ready( function( $ ) {
	'use strict';

	var {
		ajaxurl,
		wcep_ajax_nonce,
		invalid_ajax_response,
		ajax_nonce_failure,
		is_cart,
		is_checkout,
		notification_success_header,
		notification_error_header,
		loader_image,
		waiting_message,
		pickup_items_not_selected,
		pickup_reference_missing,
		pickup_date_missing,
		pickup_customer_first_name_missing,
		pickup_customer_last_name_missing,
		pickup_customer_company_missing,
		pickup_customer_address_1_missing,
		pickup_customer_address_2_missing,
		pickup_customer_city_missing,
		pickup_customer_postcode_missing,
		pickup_customer_country_state_missing,
		pickup_customer_phone_missing,
	} = WCEP_Public_JS_Obj;

	/**
	 * Add insurance cost to the cart totals.
	 */
	$( document ).on( 'click', '#wcep-insure-shipment', function( e ) {
		var this_checkbox = $( this );
		var is_checked = this_checkbox.is( ':checked' );
		var insure_shipment = '';

		if ( true === is_checked ) {
			insure_shipment = 'yes';
		} else {
			insure_shipment = 'no';
		}

		var insurance_cost = this_checkbox.val();

		// Get the shipment ID.
		var shipment_id = '';

		// Loop in through the shipping methods to check the selected shipping method.
		$( '.shipping_method' ).each( function() {
			var this_radio = $( this );
			if ( this_radio.is( ':checked' ) ) {
				shipment_id = this_radio.val();
			}
		} );

		block_element( $( '.shop_table' ) );
		// Send AJAX to insure the shipment.
		var data = {
			action: 'insure_shipment',
			wcep_ajax_nonce: wcep_ajax_nonce,
			insure_shipment: insure_shipment,
			insurance_cost: insurance_cost,
			shipment_id: shipment_id
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				unblock_element( $( '.shop_table' ) );
				// Check for invalid ajax response.
				if ( 0 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, invalid_ajax_response, 'error' );
					return false;
				}

				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				var {code, notification_message} = response.data;

				if ( 'wcep-shipment-insurance-saved' === code ) {
					wcep_show_notification( 'fa fa-check', notification_success_header, notification_message, 'success' );

					// Update the cart page, if the user is on the cart page.
					if ( 'yes' === is_cart ) {
						var wc_cart_update_btn = $( 'table.woocommerce-cart-form__contents td.actions button[name="update_cart"]' );

						if ( wc_cart_update_btn.is( '[disabled]' ) || wc_cart_update_btn.is( '[disabled=disabled]' ) ) {
							wc_cart_update_btn.prop( 'disabled', false );
							wc_cart_update_btn.click();
						}
					} else if ( 'yes' === is_checkout ) {
						$( document.body ).trigger( 'update_checkout' );
					}
				}
			},
		} );
	} );

	/**
	 * Open the modal for generating order pickup.
	 */
	$( document ).on( 'click', '.wcep-return-order-and-generate-pickup a', function( e ) {
		e.preventDefault();

		var this_btn = $( this );
		var order_id = this_btn.data( 'order-id' );

		var html = '<div class="wcep-loader">';
		html += '<img src="' + loader_image + '" alt="loader" />';
		html += '<p>' + waiting_message + '</p>';
		html += '</div>';

		$( '#wcep-pickup-modal .content' ).html( html ).css( { 'width': 'unset' } );
		$( '#wcep-pickup-modal' ).addClass( 'open' );

		// Send the ajax to fetch the pickup form fields.
		var data = {
			action: 'pickup_form_fields',
			wcep_ajax_nonce: wcep_ajax_nonce,
			order_id: order_id
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				// Check for invalid ajax response.
				if ( 0 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, invalid_ajax_response, 'error' );
					return false;
				}

				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				var {code, html} = response.data;

				if ( 'wcep-pickup-form-fields-fetched' === code ) {
					$( '#wcep-pickup-modal .content' ).html( html ).css( { 'width': '100%' } );

					$( '#customer-country-state' ).select2();

					// Pickup datepicker.
					var date = new Date();
					var weekday = date.getDay();
					var daysTillWeekOver = 6 - weekday;
					var dateMax = `+${daysTillWeekOver}d`;
					$( '#pickup-date' ).datepicker( {
						maxDate: dateMax,
						minDate: 1,
						dateFormat: 'yy-mm-dd',
					} );
				}
			},
		} );
	} );

	/**
	 * Generate pickup.
	 */
	$( document ).on( 'click', '.wcep-generate-pickup', function( e ) {
		e.preventDefault();

		var error_li = '';

		var item_ids = [];
		// Loop in through the items get item ids for pickup.
		$( '.wcep-order-details tbody tr' ).each( function() {
			var this_tr = $( this );
			var this_checkbox = this_tr.find( 'td.wcep-pickup-item input[type="checkbox"]' );

			if ( this_checkbox.is( ':checked' ) ) {
				var item_id = this_tr.data( 'item-id' );

				// Check if the item ID is valid.
				if ( 1 === is_valid( item_id ) ) {
					item_ids.push( item_id );
				}
			}
		} );

		// Add to error if no item is selected.
		if ( 0 === item_ids.length ) {
			error_li += '<li>' + pickup_items_not_selected + '</li>';
		}

		// Reference info.
		var reference = $( '#pickup-reference' ).val();
		if ( '' === reference ) {
			error_li += '<li>' + pickup_reference_missing + '</li>';
		}

		// Pickup date.
		var pickup_date = $( '#pickup-date' ).val();
		if ( '' === pickup_date ) {
			error_li += '<li>' + pickup_date_missing + '</li>';
		}

		// Special message.
		var instructions = $( '#pickup-instructions' ).val();

		// Is account address.
		var is_account_address = ( $( '#is-account-address' ).is( ':checked' ) ) ? 'yes' : 'no';

		// Order ID.
		var order_id = $( '#wcep-order-id' ).val();

		// Customer first name.
		var customer_first_name = $( '#customer-first-name' ).val();
		if ( '' === customer_first_name ) {
			error_li += '<li>' + pickup_customer_first_name_missing + '</li>';
		}

		// Customer last name.
		var customer_last_name = $( '#customer-last-name' ).val();
		if ( '' === customer_last_name ) {
			error_li += '<li>' + pickup_customer_last_name_missing + '</li>';
		}

		// Customer company.
		var customer_company = $( '#customer-company' ).val();
		if ( '' === customer_company ) {
			error_li += '<li>' + pickup_customer_company_missing + '</li>';
		}

		// Customer address.
		var customer_address = $( '#customer-address' ).val();
		if ( '' === customer_address ) {
			error_li += '<li>' + pickup_customer_address_1_missing + '</li>';
		}

		// Customer address 2.
		var customer_address_2 = $( '#customer-address-2' ).val();
		if ( '' === customer_address_2 ) {
			error_li += '<li>' + pickup_customer_address_2_missing + '</li>';
		}

		// Customer city.
		var customer_city = $( '#customer-city' ).val();
		if ( '' === customer_city ) {
			error_li += '<li>' + pickup_customer_city_missing + '</li>';
		}

		// Customer postcode.
		var customer_postcode = $( '#customer-postcode' ).val();
		if ( '' === customer_postcode ) {
			error_li += '<li>' + pickup_customer_postcode_missing + '</li>';
		}

		// Customer country & state.
		var customer_country_state = $( '#customer-country-state' ).val();
		if ( '' === customer_country_state ) {
			error_li += '<li>' + pickup_customer_country_state_missing + '</li>';
		}

		// Customer country & state.
		var customer_country_state = $( '#customer-country-state' ).val();
		if ( '' === customer_country_state ) {
			error_li += '<li>' + pickup_customer_country_state_missing + '</li>';
		}

		// Customer phone.
		var customer_phone = $( '#customer-phone' ).val();
		if ( '' === customer_phone ) {
			error_li += '<li>' + pickup_customer_phone_missing + '</li>';
		}

		if ( '' !== error_li ) {
			var error = '<ol type="1" style="margin: 0 0 0 10px;">' + error_li + '</ol>';
			wcep_show_notification( 'fa fa-warning', notification_error_header, error, 'error' );
			return false;
		}

		block_element( $( '#wcep-pickup-modal .content-wrapper .content' ) );
		// Send AJAX to create pickup.
		var data = {
			action: 'create_pickup',
			wcep_ajax_nonce: wcep_ajax_nonce,
			item_ids: item_ids,
			reference: reference,
			pickup_date: pickup_date,
			instructions: instructions,
			is_account_address: is_account_address,
			order_id: order_id,
			customer_first_name: customer_first_name,
			customer_last_name: customer_last_name,
			customer_company: customer_company,
			customer_address: customer_address,
			customer_address_2: customer_address_2,
			customer_city: customer_city,
			customer_postcode: customer_postcode,
			customer_country_state: customer_country_state,
			customer_phone: customer_phone,
		};
		$.ajax( {
			dataType: 'JSON',
			url: ajaxurl,
			type: 'POST',
			data: data,
			success: function ( response ) {
				unblock_element( $( '#wcep-pickup-modal .content-wrapper .content' ) );
				// Check for invalid ajax response.
				if ( 0 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, invalid_ajax_response, 'error' );
					return false;
				}

				// Check for the nonce check failure.
				if ( -1 === response ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, ajax_nonce_failure, 'error' );
					return false;
				}

				var {code, notification_message} = response.data;

				if ( 'wcep-pickup-created' === code ) {
					wcep_show_notification( 'fa fa-check', notification_success_header, notification_message, 'success' );
					$( '.wcep-modal' ).removeClass( 'open' );
					window.location.href = window.location.href;
				} else if ( 'wcep-pickup-not-created' === code ) {
					wcpe_show_notification( 'fa fa-warning', notification_error_header, notification_message, 'error' );
					return false;
				}
			},
		} );
	} );

	/**
	 * Close modal.
	 */
	$( document ).on( 'click', '.close', function() {
		$( '.wcep-modal' ).removeClass( 'open' );
	} );

	/**
	 * Close modal on esc key press.
	 */
	$( document ).on( 'keyup', function( e ) {
		if ( 27 === e.keyCode ) {
			$( '.wcep-modal' ).removeClass( 'open' );
		}
	} );

	/**
	 * Close the notification.
	 */
	$( document ).on( 'click', '.wcep_notification_close', function() {
		wcep_hide_notification();
	} );

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
	 * @param {string} notification_type
	 */
	function wcep_show_notification( icon_class, header_text, message, notification_type ) {
		$('.wcep_notification_popup .wcep_notification_icon i').removeClass().addClass( icon_class );
		$('.wcep_notification_popup .wcep_notification_message h3').html( header_text );
		$('.wcep_notification_popup .wcep_notification_message p').html( message );
		$('.wcep_notification_popup').removeClass('is-success is-error');

		if ( 'error' === notification_type ) {
			$( '.wcep_notification_popup' ).addClass( 'active is-error' );
		} else if ( 'success' === notification_type ) {
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
	 * Check if a number is valid.
	 * 
	 * @param {number} data 
	 */
	function is_valid( data ) {
		if ( '' === data || undefined === data || isNaN( data ) || 0 === data ) {
			return -1;
		} else {
			return 1;
		}
	}
} );
