<?php
/**
 * This file is used for writing all the re-usable custom functions.
 *
 * @since 1.0.0
 * @package Easypost_For_Wc
 * @subpackage Easypost_For_Wc/includes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Check if easypost for woocommerce is enabled.
 *
 * @return boolean
 */
function is_wcep_enabled() {
	$wcep_enabled = get_option( 'wcep_enable_easypost' );

	return ( ! empty( $wcep_enabled ) && 'yes' === $wcep_enabled ) ? true : false;
}

/**
 * Check if sandbox mode is enabled.
 *
 * @return boolean
 */
function wcep_is_sandbox_mode_on() {
	$sandbox_enabled = get_option( 'wcep_enable_easypost_sandbox' );

	return ( ! empty( $sandbox_enabled ) && 'yes' === $sandbox_enabled ) ? true : false;
}

/**
 * Check if address verification is enabled.
 *
 * @return boolean
 */
function wcep_is_address_verification_enabled() {
	$verify_address = get_option( 'wcep_verify_address_at_checkout' );

	return ( ! empty( $verify_address ) && 'yes' === $verify_address ) ? true : false;
}

/**
 * Return the EasyPost API key.
 *
 * @return string
 */
function wcep_get_api_key() {

	if ( wcep_is_sandbox_mode_on() ) {
		return get_option( 'wcep_sandbox_api_key' );
	} else {
		return get_option( 'wcep_production_api_key' );
	}
}

/**
 * Get the shipment insurance cost.
 *
 * @return boolean|string
 */
function wcep_get_shipment_insurance_cost() {
	return get_option( 'wcep_shipment_insurance_cost' );
}

/**
 * Get the number of days for showing pickup facility.
 *
 * @return boolean|int
 */
function wcep_get_pickup_avaiability_after_order_completion() {
	$pickup_facility = get_option( 'wcep_pickup_avaiability_after_order_completion' );

	if ( empty( $pickup_facility ) || is_bool( $pickup_facility ) ) {
		return false;
	}

	return (int) $pickup_facility;
}

/**
 * Function to decide, which of the product IDs to be considered.
 *
 * @param int $product_id Holds the product ID.
 * @param int $variation_id Holds the variation ID.
 * @return int
 */
function wcep_product_id( $product_id, $variation_id ) {

	return ( 0 !== $variation_id ) ? $variation_id : $product_id;
}

/**
 * Return the services and subservices offered by EasyPost.
 *
 * @return array
 */
function wcep_get_easypost_services_and_subservices() {
	$services = array(
		'USPS'  => array(
			'subservices' => array(
				'First'                                 => 'First-Class Mail',
				'Priority'                              => 'Priority Mail&#0174;',
				'Express'                               => 'Priority Mail Express&#8482;',
				'ParcelSelect'                          => 'USPS Parcel Select',
				'LibraryMail'                           => 'Library Mail Parcel',
				'MediaMail'                             => 'Media Mail Parcel',
				'CriticalMail'                          => 'USPS Critical Mail',
				'FirstClassMailInternational'           => 'First Class Mail International',
				'FirstClassPackageInternationalService' => 'First Class Package Service&#8482; International',
				'PriorityMailInternational'             => 'Priority Mail International&#0174;',
				'ExpressMailInternational'              => 'Express Mail International',
			),
		),
		'FedEx' => array(
			'subservices' => array(
				'FIRST_OVERNIGHT'        => 'First Overnight',
				'PRIORITY_OVERNIGHT'     => 'Priority Overnight',
				'STANDARD_OVERNIGHT'     => 'Standard Overnight',
				'FEDEX_2_DAY_AM'         => 'FedEx 2 Day AM',
				'FEDEX_2_DAY'            => 'FedEx 2 Day',
				'FEDEX_EXPRESS_SAVER'    => 'FedEx Express Saver',
				'GROUND_HOME_DELIVERY'   => 'FedEx Ground Home Delivery',
				'FEDEX_GROUND'           => 'FedEx Ground',
				'INTERNATIONAL_PRIORITY' => 'FedEx International Priority',
				'INTERNATIONAL_ECONOMY'  => 'FedEx International Economy',
				'INTERNATIONAL_FIRST'    => 'FedEx International First',
			),
		),
		'UPS'   => array(
			'subservices' => array(
				'Ground'            => 'Ground (UPS)',
				'3DaySelect'        => '3 Day Select (UPS)',
				'2ndDayAirAM'       => '2nd Day Air AM (UPS)',
				'2ndDayAir'         => '2nd Day Air (UPS)',
				'NextDayAirSaver'   => 'Next Day Air Saver (UPS)',
				'NextDayAirEarlyAM' => 'Next Day Air Early AM (UPS)',
				'NextDayAir'        => 'Next Day Air (UPS)',
				'Express'           => 'Express (UPS)',
				'Expedited'         => 'Expedited (UPS)',
				'ExpressPlus'       => 'Express Plus (UPS)',
				'UPSSaver'          => 'UPS Saver (UPS)',
				'UPSStandard'       => 'UPS Standard (UPS)',
			),
		),
	);

	/**
	 * Easypost services filter.
	 *
	 * This filter allows us to customize the list of services & subservices provided by EasyPost.
	 *
	 * @param array $services Holds the services and subservices array.
	 * @return array
	 */
	return apply_filters( 'wcep_easypost_services_subservices', $services );
}

/**
 * Return the pre-defined package offered by EasyPost.
 *
 * @return array
 */
function wcep_get_easypost_predefined_packages() {
	$packages = array(
		'USPS'  => array(
			'Card',
			'Letter',
			'Flat',
			'FlatRateEnvelope',
			'FlatRateLegalEnvelope',
			'FlatRatePaddedEnvelope',
			'Parcel',
			'IrregularParcel',
			'SoftPack',
			'SmallFlatRateBox',
			'MediumFlatRateBox',
			'LargeFlatRateBox',
			'LargeFlatRateBoxAPOFPO',
			'RegionalRateBoxA',
			'RegionalRateBoxB',
		),
		'FedEx' => array(
			'FedExEnvelope',
			'FedExBox',
			'FedExPak',
			'FedExTube',
			'FedEx10kgBox',
			'FedEx25kgBox',
			'FedExSmallBox',
			'FedExMediumBox',
			'FedExLargeBox',
			'FedExExtraLargeBox',
		),
		'UPS'   => array(
			'UPSLetter',
			'UPSExpressBox',
			'UPS25kgBox',
			'UPS10kgBox',
			'Tube',
			'Pak',
			'SmallExpressBox',
			'MediumExpressBox',
			'LargeExpressBox',
		),
	);

	/**
	 * Predefined packages filter.
	 *
	 * This filter allows us to customize the list of pre-defined packages provided by different carriers.
	 *
	 * @param array $packages Holds the pre-defined packages array.
	 * @return array
	 */
	return apply_filters( 'wcep_easypost_predefined_packages', $packages );
}

/**
 * Get available carriers name.
 *
 * @return array
 */
function wcep_get_carriers() {
	$services = wcep_get_easypost_services_and_subservices();

	if ( empty( $services ) || ! is_array( $services ) ) {
		return array();
	}

	$carriers = array();
	foreach ( $services as $service_name => $subservices ) {
		$carriers[ $service_name ] = $service_name;
	}

	return $carriers;
}

/**
 * Create the tracking info html.
 *
 * @param array $tracking_info Holds the tracking info array.
 * @return string
 */
function wcep_create_tracking_info_html( $tracking_info ) {
	$carrier          = ( ! empty( $tracking_info['carrier'] ) ) ? $tracking_info['carrier'] : '';
	$tracking_id      = ( ! empty( $tracking_info['tracking_id'] ) ) ? $tracking_info['tracking_id'] : '';
	$shipment_date    = ( ! empty( $tracking_info['shipment_date'] ) ) ? $tracking_info['shipment_date'] : '';
	$current_date     = ( ! empty( $tracking_info['current_date'] ) ) ? $tracking_info['current_date'] : '';
	$ep_tracking_id   = ( ! empty( $tracking_info['easypost_tracking_id'] ) ) ? $tracking_info['easypost_tracking_id'] : '';
	$tracking_message = ( ! empty( $tracking_info['easypost_tracking_message'] ) ) ? $tracking_info['easypost_tracking_message'] : '';
	$message          = '';

	// Format the shipment date.
	$formatted_shipment_date = ( ! empty( $shipment_date ) ) ? gmdate( 'F jS, Y', strtotime( $shipment_date ) ) : '';

	if ( ! empty( $shipment_date ) ) {
		// Check if the shipment date falls before or after the current date.
		if ( strtotime( $shipment_date ) < strtotime( $current_date ) ) {
			/* translators: 1: %s: shipment date, 2: %s: carrier name */
			$message .= sprintf( __( 'Your order was delivered on %1$s via %2$s.', 'wc-easypost' ), $formatted_shipment_date, $carrier );
		} elseif ( strtotime( $shipment_date ) === strtotime( $current_date ) ) {
			/* translators: 1: %s: shipment date, 2: %s: carrier name */
			$message .= sprintf( __( 'Your order is to be delivered on today, %1$s via %2$s.', 'wc-easypost' ), $formatted_shipment_date, $carrier );
		} else {
			/* translators: 1: %s: shipment date, 2: %s: carrier name */
			$message .= sprintf( __( 'Your order is to be delivered on %1$s via %2$s.', 'wc-easypost' ), $formatted_shipment_date, $carrier );
		}
	} else {
		/* translators: 1: %s: carrier name */
		$message .= sprintf( __( 'Your order was delivered via %1$s.', 'wc-easypost' ), $carrier );
	}

	// HTML for tracking IDs.
	$tracking_id_link = '';

	if ( ! empty( $tracking_id ) ) {
		if ( 'USPS' === $carrier ) {
			$tracking_link = "https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1={$tracking_id}";
		} elseif ( 'UPS' === $carrier ) {
			$tracking_link = "https://www.ups.com/track?loc=en_US&tracknum={$tracking_id}&requester=WT/";
		} elseif ( 'FedEx' === $carrier ) {
			$tracking_link = "https://www.fedex.com/apps/fedextrack/?action=track&action=track&tracknumbers={$tracking_id}";
		}
		$tracking_id_link = '<a href="' . $tracking_link . '">' . $tracking_id . '</a>';
	}

	// Add the message for tracking ID link.
	/* translators: 1: %s: tracking link */
	$message .= sprintf( __( ' To track shipment, please follow the shipment ID %1$s.', 'wc-easypost' ), $tracking_id_link );

	// Add the message from easypost.
	if ( ! empty( $ep_tracking_id ) && ! empty( $tracking_message ) ) {
		/* translators: 1: %s: easypost tracking id, 2: %s: tracking message, 3: %s: opening strong tag, 4: %s: closing strong tag, 5: %s: br tag */
		$message .= sprintf( __( '%5$s%5$sTracking ID from EasyPost is %3$s%1$s%4$s and message is: %3$s%2$s%4$s.', 'wc-easypost' ), $ep_tracking_id, $tracking_message, '<strong>', '</strong>', '<br />' );
	}

	ob_start();
	?>
	<li class="note system-note wcep-tracking-data-info">
		<div class="note_content">
			<p><?php echo wp_kses_post( $message ); ?></p>
		</div>
		<p class="meta">
			<abbr class="exact-date" title="<?php echo esc_attr( $current_date ); ?>">
				<?php echo esc_html( gmdate( 'F j, Y, g:i:s A', strtotime( $current_date ) ) ); ?>
			</abbr>
		</p>
	</li>
	<?php

	$html = ob_get_clean();

	/**
	 * Tracking info html filter.
	 *
	 * This filter helps modify the html markup of the tracking info.
	 *
	 * @param string $html Holds the tracking info HTML.
	 * @param array  $tracking_info Holds all the tracking information data.
	 * @return string
	 */
	return apply_filters( 'wcep_tracking_information_html', $html, $tracking_info );
}

/**
 * Return the shipment insurance checkbox on publis end.
 *
 * @param float $shipment_insurance Holds the shipment insurance amount.
 * @return string
 */
function wcep_shipment_insurance_checkbox( $shipment_insurance ) {
	// Check if the insurance amount is provided.
	if ( empty( $shipment_insurance ) ) {
		return;
	}

	$shipment_insurance_formatted = wc_price( $shipment_insurance );
	$insurance_cost               = WC()->session->get( 'wcep_shipment_insurance_cost' );

	ob_start();
	?>
	<div class="wcep-shipment-insuramce-block">
		<input type="checkbox" id="wcep-insure-shipment" value="<?php echo esc_html( $shipment_insurance ); ?>" <?php echo ( 0 < $insurance_cost ) ? 'checked' : ''; ?> />
		<label for="wcep-insure-shipment">
			<?php
			echo wp_kses_post(
				apply_filters(
					'wcep_shipment_insurance_message',
					/* translators: 1: %s formatted insurance fee. */
					sprintf( __( 'Check this checkbox to insure your shipment. Extra charges of %1$s applicable.', 'wc-easypost' ), $shipment_insurance_formatted ),
					$shipment_insurance
				)
			);
			?>
		</label>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Returns the image src by attachment ID.
 *
 * @param int $image_id Holds the attachment ID.
 * @return string
 */
function wcep_get_image_source_by_id( $image_id ) {

	if ( empty( $image_id ) ) {
		return wc_placeholder_img_src();
	}

	return wp_get_attachment_url( $image_id );

}

/**
 * Return the store name.
 *
 * @return string
 */
function wcep_get_business_name() {
	$store_name = get_option( 'wcep_woocommerce_store_name' );

	return ( ! empty( $store_name ) && ! is_bool( $store_name ) ) ? $store_name : '';
}

/**
 * Return the store address line 1.
 *
 * @return string
 */
function wcep_get_business_address_1() {
	$store_address_1 = get_option( 'wcep_woocommerce_store_address' );

	return ( ! empty( $store_address_1 ) && ! is_bool( $store_address_1 ) ) ? $store_address_1 : '';
}

/**
 * Return the store address line 2.
 *
 * @return string
 */
function wcep_get_business_address_2() {
	$store_address_2 = get_option( 'wcep_woocommerce_store_address_2' );

	return ( ! empty( $store_address_2 ) && ! is_bool( $store_address_2 ) ) ? $store_address_2 : '';
}

/**
 * Return the store city.
 *
 * @return string
 */
function wcep_get_business_city() {
	$store_city = get_option( 'wcep_woocommerce_store_city' );

	return ( ! empty( $store_city ) && ! is_bool( $store_city ) ) ? $store_city : '';
}

/**
 * Return the store postcode.
 *
 * @return string
 */
function wcep_get_business_postcode() {
	$store_postcode = get_option( 'wcep_woocommerce_store_postcode' );

	return ( ! empty( $store_postcode ) && ! is_bool( $store_postcode ) ) ? $store_postcode : '';
}

/**
 * Return the store phone number.
 *
 * @return string
 */
function wcep_get_business_phone_number() {
	$store_phone_number = get_option( 'wcep_woocommerce_store_phone_number' );

	return ( ! empty( $store_phone_number ) && ! is_bool( $store_phone_number ) ) ? $store_phone_number : '';
}

/**
 * Return the store country and state.
 *
 * @return string
 */
function wcep_get_business_default_country() {
	$store_default_country = get_option( 'wcep_woocommerce_default_country' );

	if ( empty( $store_default_country ) || is_bool( $store_default_country ) ) {
		return array();
	}

	$store_default_country = explode( ':', $store_default_country );
	$store_default_country = array(
		'country' => ( ! empty( $store_default_country[0] ) ) ? $store_default_country[0] : '',
		'state'   => ( ! empty( $store_default_country[1] ) ) ? $store_default_country[1] : '',
	);

	return $store_default_country;
}

/**
 * Get business location.
 *
 * @return array
 */
function wcep_get_business_location() {
	$default_country = wcep_get_business_default_country();

	$business_address_fields = array(
		'company'   => wcep_get_business_name(),
		'address_1' => wcep_get_business_address_1(),
		'address_2' => wcep_get_business_address_2(),
		'city'      => wcep_get_business_city(),
		'country'   => ( ! empty( $default_country['country'] ) ) ? $default_country['country'] : '',
		'state'     => ( ! empty( $default_country['state'] ) ) ? $default_country['state'] : '',
		'postcode'  => wcep_get_business_postcode(),
		'phone'     => wcep_get_business_phone_number(),
	);

	/**
	 * Business address fields filter.
	 *
	 * This filter is added to modify the address fields for the business.
	 *
	 * @param array $business_address_fields Holds the business address fields.
	 * @return array
	 */
	return apply_filters( 'wcep_business_address_fields', $business_address_fields );
}

/**
 * Generate the label preview imahe html.
 *
 * @param string $label_image Holds the label image URL.
 * @return string
 */
function wcep_get_label_preview_image_html( $label_image ) {
	ob_start();
	?>
	<a href="javascript:void(0);">
		<img src="<?php echo esc_url( $label_image ); ?>" class="wcep-label" />
	</a>
	<?php
	return ob_get_clean();
}

/**
 * The list of countries and states.
 *
 * @return array
 */
function wcep_get_country_states_list() {
	$countries_obj = new WC_Countries();
	$countries     = $countries_obj->get_countries();
	$response      = array();

	if ( empty( $countries ) || ! is_array( $countries ) ) {
		return $response;
	}

	// Loop in through the countries to fetch states.
	foreach ( $countries as $country_code => $country_name ) {
		$states                            = $countries_obj->get_states( $country_code );
		$response[ $country_code ]['name'] = $country_name;

		if ( empty( $states ) ) {
			continue;
		}

		// Add states to the array.
		$response[ $country_code ]['states'] = $states;
	}

	return $response;
}

/**
 * Get the timestamp when the order is marked as complete.
 *
 * @param int $order_id Holds the order ID.
 * @return string
 */
function wcep_order_completed_date( $order_id ) {
	if ( empty( $order_id ) ) {
		return false;
	}

	if ( false === wc_get_order( $order_id ) ) {
		return false;
	}

	return get_post_meta( $order_id, 'wcep_order_completed', true );
}
