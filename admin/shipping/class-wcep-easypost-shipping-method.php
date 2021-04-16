<?php
/**
 * EasyPost Shipping Method.
 *
 * @version 1.0.0
 * @package Easypost_For_Wc
 * @subpackage Easypost_For_Wc/admin/shipping
 */

// Require the easypost autoload file.
require WCEP_PLUGIN_PATH . 'vendor/autoload.php';

defined( 'ABSPATH' ) || exit;

/**
 * EasyPost Shipping Method class.
 */
class WCEP_EasyPost_Shipping_Method extends WC_Shipping_Method {

	/**
	 * Variable that defines US as domestic location.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $domestic Defines US as domestic location.
	 */
	private $domestic = 'US';

	/**
	 * Variable that holds all the found rates.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    array $found_rates EasyPost found rates.
	 */
	private $found_rates;

	/**
	 * Variable that holds all the carriers list.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    array $carrier_list Array of available carriers.
	 */
	private $carrier_list;

	/**
	 * Variable that holds the easypost api key.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $apikey EasyPost API key.
	 */
	private $apikey;

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Holds the instance ID.
	 */
	public function __construct( $instance_id = 0 ) {

		parent::__construct();
		$this->id           = 'wcep-easypost-shipping-method';
		$this->instance_id  = absint( $instance_id );
		$this->method_title = __( 'EasyPost', 'wc-easypost' );

		/* translators: 1: %s: opening strong tag, 2: %s: closing strong tag */
		$this->method_description = sprintf( __( 'The %1$sEasypost.com%2$s plugin obtains rates dynamically from the easypost.com API during cart/checkout.', 'wc-easypost' ), '<strong>', '</strong>' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		$this->carrier_list       = wcep_get_carriers();
		$this->currency           = get_woocommerce_currency();
		$this->init();

		$this->apikey = wcep_get_api_key();
		\EasyPost\EasyPost::setApiKey( $this->apikey );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Initialize custom shiping method.
	 */
	public function init() {
		$this->instance_form_fields     = $this->init_form_fields();
		$this->title                    = $this->get_option( 'title' );
		$this->tax_status               = $this->get_option( 'tax_status' );
		$this->cost                     = $this->get_option( 'cost' );
		$this->type                     = $this->get_option( 'type', 'class' );
		$this->rate_types               = $this->get_option( 'rate_types' );
		$this->rates                    = array();
		$this->unpacked_item_costs      = 0;
		$this->enable_standard_services = true;
		$this->init_settings();
	}

	/**
	 * Calculate custom shipping method.
	 *
	 * @param array $package Holds the package information.
	 *
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {

		if ( ! isset( $package['contents'] ) || empty( $package['contents'] ) ) {
			return;
		}

		if ( ! $this->enable_standard_services ) {
			return;
		}

		$package_requests = $this->get_package_requests( $package );
		libxml_use_internal_errors( true );

		if ( empty( $package_requests ) ) {
			return;
		}

		$responses = array();
		foreach ( $package_requests as $key => $package_request ) {
			$responses[] = $this->get_rates( $package_request );
		}

		// Return if there's no response.
		if ( empty( $responses ) || ! is_array( $responses ) ) {
			return;
		}

		$found_rates = array();
		foreach ( $responses as $response ) {
			$shipment = $response['shipment'];

			if ( ! isset( $shipment->rates ) || empty( $shipment->rates ) ) {
				continue;
			}

			$rates = $shipment->rates;

			if ( empty( $this->carrier_list ) || ! is_array( $this->carrier_list ) ) {
				continue;
			}

			foreach ( $this->carrier_list as $carrier ) {
				foreach ( $rates as $rate ) {

					if ( $carrier === $rate->carrier ) {
						// Check for currency conversion.
						$converted_rate = 1;

						if ( $this->currency !== $rate->currency ) {
							if ( empty( WC()->session->get( 'wcep_converted_rate' ) ) ) {
								$converted_rate = $this->convert_currency( $rate->currency, $this->currency );

								if ( false !== $converted_rate ) {
									WC()->session->set( 'wcep_converted_rate', $converted_rate );
								}
							} else {
								$converted_rate = WC()->session->get( 'wcep_converted_rate' );
							}
						}

						$service_type = (string) $rate->service;
						$subservice   = $this->get_subservice_name( $service_type, $rate->carrier );
						$total_amount = $response['quantity'] * $rate->rate * $converted_rate;

						$found_rates[ $service_type ]['label']   = $subservice;
						$found_rates[ $service_type ]['cost']    = $total_amount;
						$found_rates[ $service_type ]['carrier'] = $rate->carrier;

						$shipping_rate = array(
							'id'        => $shipment->id,
							'label'     => $subservice,
							'cost'      => $total_amount,
							'meta_data' => array(
								'est_delivery_days' => (int) $rate->est_delivery_days,
								'carrier'           => $rate->carrier,
							),
						);

						$this->add_rate( $shipping_rate );
						do_action( 'woocommerce_' . $this->id . '_shipping_add_rate', $this, $shipping_rate );
					}
				}
			}
		}
	}

	/**
	 * Get the converted currencies.
	 *
	 * @param string $from_currency Holds the source currency.
	 * @param string $to_currency Holds the target currency.
	 * @return string
	 */
	private function convert_currency( $from_currency, $to_currency ) {
		// Check if both the currencies are not empty.
		if ( empty( $from_currency ) || empty( $to_currency ) ) {
			return false;
		}

		if ( $from_currency === $to_currency ) {
			return false;
		}

		try {
			$api_url       = "https://www.alphavantage.co/query?function=CURRENCY_EXCHANGE_RATE&from_currency={$from_currency}&to_currency={$to_currency}&apikey=ZYL9FJEAWZLHXL0H";
			$response      = wp_remote_get( $api_url );
			$response_code = wp_remote_retrieve_response_code( $response );

			if ( 200 === $response_code ) {
				$response_body = (array) json_decode( wp_remote_retrieve_body( $response ) );

				if ( ! empty( $response_body['Realtime Currency Exchange Rate'] ) ) {
					$obj = (array) $response_body['Realtime Currency Exchange Rate'];

					foreach ( $obj as $key => $data ) {
						if ( false !== stripos( $key, 'Exchange Rate' ) ) {
							return number_format( $obj[ $key ], 2 );
						}
					}
				}
			}
		} catch ( Exception $e ) {
			var_dump( $e->getMessage() );
			die( 'exchange-rate-not-found' );
		}
	}

	/**
	 * Get the subservice name from the list of services offered by easypost.
	 *
	 * @param string $service_type Holds the service type.
	 * @param string $carrier Holds the carrier name.
	 * @return string
	 */
	private function get_subservice_name( $service_type, $carrier ) {
		// Check if the received data is not empty.
		if ( empty( $service_type ) || empty( $carrier ) ) {
			return false;
		}

		$services_list = wcep_get_easypost_services_and_subservices();
		$subservices   = $services_list[ $carrier ];

		if ( empty( $subservices['subservices'] ) ) {
			return false;
		}

		$subservices = $subservices['subservices'];

		if ( ! array_key_exists( $service_type, $subservices ) ) {
			return false;
		}

		return $subservices[ $service_type ];
	}

	/**
	 * Get rates.
	 *
	 * @param object $package_request Holds the package data.
	 * @param string $predefined_package Holds the pre-defined package information.
	 * @return array
	 */
	private function get_rates( $package_request, $predefined_package = '' ) {
		try {
			$from_address            = wcep_get_business_location();
			$payload                 = array();
			$payload['from_address'] = array(
				'zip'     => ( ! empty( $from_address['postcode'] ) ) ? $from_address['postcode'] : '',
				'state'   => ( ! empty( $from_address['state'] ) ) ? $from_address['state'] : '',
				'country' => ( ! empty( $from_address['country'] ) ) ? $from_address['country'] : '',
			);

			$payload['to_address'] = array(
				'name'        => '-', // Not available until cart page.
				'street1'     => '-', // Not available until cart page.
				'residential' => ( ! empty( $this->rate_types ) && 'residential' === $this->rate_types ) ? true : false,
				'zip'         => $package_request['request']['Rate']['ToZIPCode'],
				'country'     => $package_request['request']['Rate']['ToCountry'],
			);

			$payload['parcel'] = array(
				'length' => $package_request['request']['Rate']['Length'],
				'width'  => $package_request['request']['Rate']['Width'],
				'height' => $package_request['request']['Rate']['Height'],
				'weight' => $package_request['request']['Rate']['WeightOz'],
			);

			if ( ! empty( $predefined_package ) ) {
				$payload['parcel']['predefined_package'] = strpos( $predefined_package, '-' ) ? substr( $predefined_package, 0, strpos( $predefined_package, '-' ) ) : $predefined_package;
			}

			$payload['options'] = array(
				'special_rates_eligibility' => 'USPS.LIBRARYMAIL,USPS.MEDIAMAIL',
			);

			$shipment = \EasyPost\Shipment::create( $payload );

			return array(
				'shipment' => $shipment,
				'quantity' => $package_request['quantity'],
			);
		} catch ( Exception $e ) {
			var_dump( $e->getMessage() );
			die( 'shipping error' );
		}
	}

	/**
	 * Get package requests.
	 *
	 * @param array $package Holds the package data.
	 * @return array
	 */
	private function get_package_requests( $package ) {
		$requests = $this->per_item_shipping( $package );

		return $requests;
	}

	/**
	 * Calculate shipping per item in the cart.
	 *
	 * @param array $package Holds the package data.
	 * @return array
	 */
	private function per_item_shipping( $package ) {
		$requests = array();
		$domestic = $this->domestic === $package['destination']['country'];

		if ( ! is_array( $package['contents'] ) ) {
			return array();
		}

		// Gather weight of all the items.
		foreach ( $package['contents'] as $item_id => $item ) {
			$prod_id = wcep_product_id( $item['product_id'], $item['variation_id'] );
			$product = wc_get_product( $prod_id );

			// Skip, if this is a virtual product.
			$needs_shipping = $product->needs_shipping();

			if ( ! $needs_shipping ) {
				continue;
			}

			// Product weight.
			$weight = $product->get_weight();

			if ( empty( $weight ) ) {
				$weight        = 1;
				$weight_in_oz  = wc_get_weight( $weight, 'oz' );
				$weight_in_lbs = wc_get_weight( $weight, 'lbs' );
			} else {
				$weight_in_oz  = wc_get_weight( $weight, 'oz' );
				$weight_in_lbs = wc_get_weight( $weight, 'lbs' );
			}

			$size = 'REGULAR';

			// Product dimentions.
			$length = $product->get_length();
			$width  = $product->get_width();
			$height = $product->get_height();

			if ( $length && $length && $length ) {
				$dimensions = array(
					wc_get_dimension( $length, 'in' ),
					wc_get_dimension( $width, 'in' ),
					wc_get_dimension( $height, 'in' ),
				);

				// This is done to know the longest side of the box, as the longest side is considered to be the box's length.
				sort( $dimensions );

				if ( max( $dimensions ) > 12 ) {
					$size = 'LARGE';
				}

				$girth = ( 2 * $dimensions[0] ) + ( 2 * $dimensions[1] );
			} else {
				$dimensions = array( 0, 0, 0 );
				$girth      = 0;
			}

			$quantity = $item['quantity'];

			// Check if the item is in rectangular shape.
			if ( 'LARGE' === $size ) {
				$rectangular_shaped = true;
			} else {
				$rectangular_shaped = false;
			}

			$from_address = wcep_get_business_location();

			if ( $domestic ) {
				$request['Rate'] = array(
					'FromZIPCode'       => ( ! empty( $from_address['postcode'] ) ) ? $from_address['postcode'] : '',
					'ToZIPCode'         => ( ! empty( $package['destination']['postcode'] ) ) ? $package['destination']['postcode'] : '',
					'ToCountry'         => ( ! empty( $package['destination']['country'] ) ) ? $package['destination']['country'] : '',
					'WeightLb'          => $weight_in_lbs,
					'WeightOz'          => $weight_in_oz,
					'PackageType'       => 'Package',
					'Length'            => $dimensions[2],
					'Width'             => $dimensions[1],
					'Height'            => $dimensions[0],
					'ShipDate'          => gmdate( 'Y-m-d', strtotime( gmdate( 'Y-m-d' ) . ' +1 day' ) ),
					'InsuredValue'      => $product->get_price(),
					'RectangularShaped' => $rectangular_shaped,
				);
			} else {
				$request['Rate'] = array(
					'FromZIPCode'       => ( ! empty( $from_address['postcode'] ) ) ? $from_address['postcode'] : '',
					'ToZIPCode'         => ( ! empty( $package['destination']['postcode'] ) ) ? $package['destination']['postcode'] : '',
					'ToCountry'         => ( ! empty( $package['destination']['country'] ) ) ? $package['destination']['country'] : '',
					'Amount'            => $product->get_price(),
					'WeightLb'          => $weight_in_lbs,
					'WeightOz'          => $weight_in_oz,
					'PackageType'       => 'Package',
					'Length'            => $dimensions[2],
					'Width'             => $dimensions[1],
					'Height'            => $dimensions[0],
					'ShipDate'          => gmdate( 'Y-m-d', strtotime( gmdate( 'Y-m-d' ) . ' +1 day' ) ),
					'InsuredValue'      => $product->get_price(),
					'RectangularShaped' => $rectangular_shaped,
				);
			}

			$request['unpacked'] = array();
			$request['packed']   = array( $product );

			$requests[] = array(
				'request'  => $request,
				'quantity' => $quantity,
			);
		}

		return $requests;
	}

	/**
	 * Init form fields.
	 *
	 * @return array|void
	 */
	public function init_form_fields() {
		$settings = array(
			'title'      => array(
				'title'       => __( 'Method title', 'wc-easypost' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'wc-easypost' ),
				'default'     => __( 'EasyPost', 'wc-easypost' ),
				'desc_tip'    => true,
			),
			'fallback'   => array(
				'title'       => __( 'Fallback Cost', 'wc-easypost' ),
				'type'        => 'number',
				'placeholder' => '0.00',
				'default'     => '0',
				'desc_tip'    => true,
				'description' => __( 'If Easypost.com returns no matching rates, offer this amount for shipping so that the user can still checkout. Leave blank to disable.', 'wc-easypost' ),
			),
			'rate_types' => array(
				'title'       => __( 'Rate Types', 'wc-easypost' ),
				'type'        => 'select',
				'class'       => 'wc-enhanced-select',
				'default'     => 'residential',
				'description' => __( 'Rates will be fetched based on the address type that you choose here. Please note this functionality will be available only for supported carriers.', 'wc-easypost' ),
				'desc_tip'    => true,
				'options'     => array(
					'residential' => __( 'Residential', 'wc-easypost' ),
					'commercial'  => __( 'Commercial', 'wc-easypost' ),
				),
			),
			'carriers'   => array(
				'title'             => __( 'Carriers Types', 'wc-easypost' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select',
				'description'       => __( 'Select your easypost carriers.', 'wc-easypost' ),
				'desc_tip'          => true,
				'options'           => $this->carrier_list,
				'custom_attributes' => array(
					'autocomplete' => 'off',
				),
			),
		);

		/**
		 * Shipping method fields filter.
		 *
		 * This filter allows you to modify the shipping method form fields.
		 *
		 * @param array $settings Holds the form fields array.
		 * @return array
		 */
		return apply_filters( 'wcep_shipping_method_fields', $settings );
	}

	/**
	 * Processes and saves options.
	 * If there is an error thrown, will continue to save and validate fields, but will leave the erroneous field out.
	 *
	 * @return bool was anything saved?
	 * @since 2.6.0
	 */
	public function process_admin_options() {
		parent::process_admin_options();

		if ( $this->instance_id ) {
			$this->init_instance_settings();

			return update_option(
				$this->get_instance_option_key(),
				apply_filters(
					'woocommerce_shipping_' . $this->id . '_instance_settings_values',
					$this->instance_settings,
					$this
				)
			);
		}
	}
}
