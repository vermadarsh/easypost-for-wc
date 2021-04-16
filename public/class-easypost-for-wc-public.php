<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Easypost_For_Wc
 * @subpackage Easypost_For_Wc/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Easypost_For_Wc
 * @subpackage Easypost_For_Wc/public
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class Easypost_For_Wc_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function wcep_wp_enqueue_scripts_callback() {
		// Get WC current endpoint.
		$endpoint = WC()->query->get_current_endpoint();

		wp_enqueue_style(
			$this->plugin_name . '-admin',
			WCEP_PLUGIN_URL . 'admin/css/easypost-for-wc-admin.css',
			array(),
			filemtime( WCEP_PLUGIN_PATH . 'admin/css/easypost-for-wc-admin.css' )
		);

		wp_enqueue_style(
			$this->plugin_name,
			WCEP_PLUGIN_URL . 'public/css/easypost-for-wc-public.css',
			array(),
			filemtime( WCEP_PLUGIN_PATH . 'public/css/easypost-for-wc-public.css' )
		);

		// Enqueue the style on view-order only.
		if ( ! empty( $endpoint ) || 'view-order' === $endpoint ) {
			wp_enqueue_style(
				'jquery-ui',
				'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css',
				array(),
				time()
			);
		}

		wp_enqueue_script(
			$this->plugin_name,
			WCEP_PLUGIN_URL . 'public/js/easypost-for-wc-public.js',
			array( 'jquery', 'jquery-ui-datepicker' ),
			filemtime( WCEP_PLUGIN_PATH . 'public/js/easypost-for-wc-public.js' ),
			true
		);

		// Localize variables.
		wp_localize_script(
			$this->plugin_name,
			'WCEP_Public_JS_Obj',
			array(
				'ajaxurl'                               => admin_url( 'admin-ajax.php' ),
				'wcep_ajax_nonce'                       => wp_create_nonce( 'wcep-ajax-nonce' ),
				'invalid_ajax_response'                 => apply_filters( 'wcep_invalid_ajax_response', __( 'Invalid AJAX response.', 'wc-easypost' ) ),
				'ajax_nonce_failure'                    => apply_filters( 'wcep_ajax_nonce_failure_error', __( 'Action couldn\'t be taken due to security failure. Please try again later.', 'wc-easypost' ) ),
				'is_cart'                               => is_cart() ? 'yes' : 'no',
				'is_checkout'                           => is_checkout() ? 'yes' : 'no',
				'notification_success_header'           => apply_filters( 'wcep_notification_success_header', __( 'Success', 'wc-easypost' ) ),
				'notification_error_header'             => apply_filters( 'wcep_notification_error_header', __( 'Error', 'wc-easypost' ) ),
				'loader_image'                          => includes_url( 'images/wpspin-2x.gif' ),
				'waiting_message'                       => __( 'Please wait..', 'wc-easypost' ),
				'pickup_items_not_selected'             => apply_filters( 'wcep_pickup_items_not_selected_error', __( 'No item is selected for pickup.', 'wc-easypost' ) ),
				'pickup_reference_missing'              => apply_filters( 'wcep_pickup_reference_missing_error', __( 'Reference info is missing.', 'wc-easypost' ) ),
				'pickup_date_missing'                   => apply_filters( 'wcep_pickup_date_missing_error', __( 'Pickup date is missing.', 'wc-easypost' ) ),
				'pickup_customer_first_name_missing'    => apply_filters( 'wcep_pickup_customer_first_name_missing_error', __( 'Customer\'s first name is missing.', 'wc-easypost' ) ),
				'pickup_customer_last_name_missing'     => apply_filters( 'wcep_pickup_customer_last_name_missing_error', __( 'Customer\'s last name is missing.', 'wc-easypost' ) ),
				'pickup_customer_company_missing'       => apply_filters( 'wcep_pickup_customer_company_missing_error', __( 'Customer\'s company is missing.', 'wc-easypost' ) ),
				'pickup_customer_address_1_missing'     => apply_filters( 'wcep_pickup_customer_address_1_missing_error', __( 'Customer\'s address line 1 is missing.', 'wc-easypost' ) ),
				'pickup_customer_address_2_missing'     => apply_filters( 'wcep_pickup_customer_address_2_missing_error', __( 'Customer\'s address line 2 is missing.', 'wc-easypost' ) ),
				'pickup_customer_city_missing'          => apply_filters( 'wcep_pickup_customer_city_missing_error', __( 'Customer\'s city is missing.', 'wc-easypost' ) ),
				'pickup_customer_postcode_missing'      => apply_filters( 'wcep_pickup_customer_postcode_missing_error', __( 'Customer\'s postcode is missing.', 'wc-easypost' ) ),
				'pickup_customer_country_state_missing' => apply_filters( 'wcep_pickup_customer_country_state_missing_error', __( 'Customer\'s country/state is missing.', 'wc-easypost' ) ),
				'pickup_customer_phone_missing'         => apply_filters( 'wcep_pickup_customer_phone_missing_error', __( 'Customer\'s phone number is missing.', 'wc-easypost' ) ),
			)
		);
	}

	/**
	 * Add custom assets to WordPress footer section.
	 */
	public function wcep_wp_footer_callback() {
		ob_start();
		?>
		<div class="wcep_notification_popup">
			<span class="wcep_notification_close"></span>
			<div class="wcep_notification_icon"><i class="fa" aria-hidden="true"></i></div>
			<div class="wcep_notification_message">
				<h3 class="title"></h3>
				<p class="message"></p>
			</div>
		</div>
		<?php

		echo wp_kses_post( ob_get_clean() );

		// Include the pickup modal.
		echo wp_kses_post( $this->wcep_pickup_modal_html() );
	}

	/**
	 * Modal HTML for emailing labels functionality.
	 *
	 * @return string
	 */
	private function wcep_pickup_modal_html() {
		$endpoint = WC()->query->get_current_endpoint();

		if ( empty( $endpoint ) || 'view-order' !== $endpoint ) {
			return;
		}

		ob_start();
		?>
		<div class="wcep-modal" id="wcep-pickup-modal">
			<article class="content-wrapper">
				<button class="close"></button>
				<header class="modal-header">
					<h2><?php esc_html_e( 'Pickup', 'wcep-easypost' ); ?></h2>
				</header>
				<div class="content"></div>
				<footer class="modal-footer">
					<button type="button" class="button wcep-generate-pickup"><?php esc_html_e( 'Generate Pickup', 'wc-easypost' ); ?></button>
				</footer>
			</article>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Add extra HTML for insurance fees.
	 *
	 * @param string $cart_fee_html Holds the cart fee html.
	 * @return string
	 */
	public function wcep_woocommerce_cart_totals_fee_html_callback( $cart_fee_html ) {
		/**
		 * Get the current shipping method.
		 * Show the shipment-insurance-tr only if the shipping method is one among that EasyPost provides.
		 */
		$chosen_shipping_method = WC()->session->chosen_shipping_methods;

		if ( empty( $chosen_shipping_method[0] ) || false === stripos( $chosen_shipping_method[0], 'shp_' ) ) {
			return $cart_fee_html;
		}

		// If the current chosen shipping method is one among the ones provided by easypost.com.
		$shipment_insurance = wcep_get_shipment_insurance_cost();

		if ( false === $shipment_insurance || empty( $shipment_insurance ) ) {
			return $cart_fee_html;
		}

		$shipment_insurance = (float) $shipment_insurance;
		$cart_fee_html     .= wcep_shipment_insurance_checkbox( $shipment_insurance );

		return $cart_fee_html;
	}

	/**
	 * Save meta data to order meta as the order is processed on checkout.
	 *
	 * @param int   $order_id Holds the order ID.
	 * @param array $posted_data Holds the posted data array.
	 */
	public function wcep_woocommerce_checkout_order_processed_callback( $order_id, $posted_data ) {
		// Check to see if the easypost shipping method is selected.
		if ( empty( $posted_data['shipping_method'][0] ) ) {
			return;
		}

		// Save the shipment ID to order meta.
		$shipment_id = $posted_data['shipping_method'][0];

		if ( 'shp_' === substr( $shipment_id, 0, 4 ) ) {
			update_post_meta( $order_id, 'wcep_shipment_id', $shipment_id );
		}

		// Save the estimated delivery days to order meta.
		if ( ! empty( $posted_data['estimated_delivery'] ) ) {
			update_post_meta( $order_id, 'wcep_estimated_delivery', $posted_data['estimated_delivery'] );
		}

		// Save the shipping carrier to order meta.
		if ( ! empty( $posted_data['carrier'] ) ) {
			update_post_meta( $order_id, 'wcep_shipping_carrier', $posted_data['carrier'] );
		}

		// Unset the session holding insurance cost.
		WC()->session->__unset( 'wcep_shipment_insurance_cost' );
	}

	/**
	 * AJAX served to add insurance cost to the shipment.
	 */
	public function wcep_insure_shipment_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'insure_shipment' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcep_ajax_nonce = filter_input( INPUT_POST, 'wcep_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcep_ajax_nonce, 'wcep-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		$insurance_cost  = 0.00;
		$insure_shipment = filter_input( INPUT_POST, 'insure_shipment', FILTER_SANITIZE_STRING );
		$shipment_id     = filter_input( INPUT_POST, 'shipment_id', FILTER_SANITIZE_STRING );

		if ( 'yes' === $insure_shipment ) {
			$insurance_cost = filter_input( INPUT_POST, 'insurance_cost', FILTER_SANITIZE_STRING );
			$insurance_cost = (float) $insurance_cost;
		}

		// Save this amount in the session variable.
		WC()->session->set( 'wcep_shipment_insurance_cost', $insurance_cost );

		// Send the AJAX response now.
		wp_send_json_success(
			array(
				'code'                 => 'wcep-shipment-insurance-saved',
				'notification_message' => ( 0.00 === $insurance_cost ) ? __( 'Shipment insurance cost removed.', 'wc-easypost' ) : __( 'Shipment insurance cost added.', 'wc-easypost' ),
			)
		);
		wp_die();
	}

	/**
	 * Add shipment insurance fee to the cart.
	 *
	 * @param object $cart Holds the cart object.
	 */
	public function wcep_woocommerce_cart_calculate_fees_callback( $cart ) {
		$insurance_fee = WC()->session->get( 'wcep_shipment_insurance_cost' );
		$cart->add_fee( __( 'Insurance Cost', 'wc-easypost' ), $insurance_fee, false );
	}

	/**
	 * Add estimated delibery days html after each shipping method.
	 *
	 * @param object $method Holds the shipping method object.
	 */
	public function wcep_woocommerce_after_shipping_rate_callback( $method ) {
		$shipping_meta_data = $method->get_meta_data();
		$delivery_days      = ( ! empty( $shipping_meta_data['est_delivery_days'] ) ) ? (int) $shipping_meta_data['est_delivery_days'] : -1;
		$carrier            = ( ! empty( $shipping_meta_data['carrier'] ) ) ? $shipping_meta_data['carrier'] : '';

		// Skip if the delivery data is not available.
		if ( -1 === $delivery_days ) {
			return;
		}

		/* translators: 1: %s: delivery days count string */
		$day_count = sprintf( _n( '%d day', '%d days', $delivery_days, 'wc-easypost' ), number_format_i18n( $delivery_days ) );

		// Dynamic text styles.
		$text_color = get_option( 'wcep_estimated_delivery_text_color' );
		$bg_color   = get_option( 'wcep_estimated_delivery_background_color' );
		$style      = "color: {$text_color}; background-color: {$bg_color};";

		ob_start();
		?>
		<div class="wcep-est-delivery-days" style="<?php echo esc_attr( $style ); ?>">
			<?php
			/* translators: 1: %s: day count */
			echo wp_kses_post( sprintf( __( 'Est. delivery within %1$s.', 'wc-easypost' ), $day_count ) );
			?>
			<input type="hidden" name="<?php echo esc_attr( "{$method->id}-estimated-delivery-days" ); ?>" value="<?php echo esc_html( $delivery_days ); ?>" />
			<input type="hidden" name="<?php echo esc_attr( "{$method->id}-carrier" ); ?>" value="<?php echo esc_html( $carrier ); ?>" />
		</div>
		<?php
		$estimated_delivery = ob_get_clean();

		/**
		 * Estimated delivery filter.
		 *
		 * This filter helps in modifying the estimated delivery markup.
		 *
		 * @param string $estimated_delivery Holds the estimated delivery html.
		 * @param object $method Holds the shipping method object.
		 * @return string
		 */
		echo wp_kses(
			apply_filters(
				'wcep_estimates_shipment_delivery_markup',
				$estimated_delivery,
				$method
			),
			array(
				'input' => array(
					'type'  => array(),
					'name'  => array(),
					'value' => array(),
				),
				'div'   => array(
					'class' => array(),
					'style' => array(),
				),
			)
		);
	}

	/**
	 * Add the estimated delivery days to the posted data.
	 *
	 * @param array $posted_data Holds the posted data array.
	 * @return array
	 */
	public function wcep_woocommerce_checkout_posted_data_callback( $posted_data ) {
		if ( empty( $posted_data['shipping_method'][0] ) ) {
			return $posted_data;
		}

		$shipment_id = $posted_data['shipping_method'][0];

		// Estimated delivery days.
		$estimated_delivery = filter_input( INPUT_POST, "{$shipment_id}-estimated-delivery-days", FILTER_SANITIZE_STRING );

		if ( ! empty( $estimated_delivery ) ) {
			$posted_data['estimated_delivery'] = $estimated_delivery;
		}

		// Carrier.
		$carrier = filter_input( INPUT_POST, "{$shipment_id}-carrier", FILTER_SANITIZE_STRING );

		if ( ! empty( $carrier ) ) {
			$posted_data['carrier'] = $carrier;
		}

		return $posted_data;
	}

	/**
	 * Verify the customer's delivery address before order placement.
	 */
	public function wcep_woocommerce_checkout_process_callback() {
		// Check if the address verification is enabled.
		if ( ! wcep_is_address_verification_enabled() ) {
			return;
		}

		$customer_data = WC()->session->get( 'customer' );
		$error_message = '';
		$apikey        = wcep_get_api_key();

		// Require the easypost autoload file.
		require WCEP_PLUGIN_PATH . 'vendor/autoload.php';
		\EasyPost\EasyPost::setApiKey( $apikey );

		try {
			$address_params = array(
				'verify'  => array( 'delivery' ),
				'street1' => ( ! empty( $customer_data['address_1'] ) ) ? $customer_data['address_1'] : '',
				'street2' => ( ! empty( $customer_data['address_2'] ) ) ? $customer_data['address_2'] : '',
				'city'    => ( ! empty( $customer_data['city'] ) ) ? $customer_data['city'] : '',
				'state'   => ( ! empty( $customer_data['state'] ) ) ? $customer_data['state'] : '',
				'zip'     => ( ! empty( $customer_data['postcode'] ) ) ? $customer_data['postcode'] : '',
				'country' => ( ! empty( $customer_data['country'] ) ) ? $customer_data['country'] : '',
				'company' => ( ! empty( $customer_data['company'] ) ) ? $customer_data['company'] : '',
				'phone'   => ( ! empty( $customer_data['phone'] ) ) ? $customer_data['phone'] : '',
			);
			$address        = \EasyPost\Address::create( $address_params );

			if ( ! empty( $address->verifications->delivery->errors ) ) {

				// Check to see if there's some error message.
				if ( ! empty( $address->verifications->delivery->errors[0]->message ) ) {
					$error_message = $address->verifications->delivery->errors[0]->message;
				}
			}
		} catch ( \EasyPost\Error $e ) {
			$error_message = $e->getMessage();
		}

		if ( ! empty( $error_message ) ) {
			/* translators: 1: %s: error message, 2: %s: opening strong tag, 3: %s: closing strong tag */
			$message = sprintf( __( 'Unable to verify address due to the error: %2$s%1$s%3$s.', 'wc-easypost' ), $error_message, '<strong>', '</strong>' );
			wc_add_notice( $message, 'error' );
		}
	}

	/**
	 * Add custom action after the order details table.
	 *
	 * @param object $order Holds the WC order object.
	 * @return void
	 */
	public function wcep_woocommerce_order_details_after_order_table_callback( $order ) {
		// Check the order status.
		$order_status = $order->get_status();

		if ( 'completed' !== $order_status ) {
			return;
		}

		// Check if the pickup is available to be made.
		$pickup_available = wcep_get_pickup_avaiability_after_order_completion();

		if ( false === $pickup_available ) {
			return;
		}

		// Check for the number of days in difference when the order was marked as completed.
		$order_completed_timestamp = wcep_order_completed_date( $order->get_id() );
		$today                     = time();

		// Formulate the Difference between two dates.
		$diff = abs( $today - $order_completed_timestamp );

		// Difference in years.
		$years_diff = floor( $diff / ( 365 * 60 * 60 * 24 ) );

		// Difference in months.
		$months_diff = floor( ( $diff - $years_diff * 365 * 60 * 60 * 24 ) / ( 30 * 60 * 60 * 24 ) );

		// Difference in days.
		$days_diff = floor( ( $diff - $years_diff * 365 * 60 * 60 * 24 - $months_diff * 30 * 60 * 60 * 24 ) / ( 60 * 60 * 24 ) );

		// Disallow the pickup if more no. of days have passed after the order was completed.
		if ( $days_diff > $pickup_available ) {
			return;
		}

		$pickup_button_text = __( 'Return order and generate pickup', 'wc-easypost' );

		/**
		 * Pickup button text filter.
		 *
		 * This filter is added to help customize the pickup button text.
		 *
		 * @param string $pickup_button_text Holds the button text.
		 * @param object $order Holds the WC order object.
		 * @return string
		 */
		$pickup_button_text = apply_filters( 'wcep_pickup_button_text', $pickup_button_text, $order );
		?>
		<p class="wcep-return-order-and-generate-pickup">
			<a href="#" class="button" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>" title="<?php echo esc_html( $pickup_button_text ); ?>"><?php echo esc_html( $pickup_button_text ); ?></a>
		</p>
		<?php
	}

	/**
	 * Do something on WordPress init.
	 */
	public function wcep_init_callback() {
		// Register new post status.
		register_post_status(
			'wc-returned',
			array(
				'label'                     => _x( 'Returned', 'Order status', 'wc-easypost' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: 1: %s: order count */
				'label_count'               => _n_noop( 'Returned <span class="count">(%s)</span>', 'Returned<span class="count">(%s)</span>', 'wc-easypost' ),
			)
		);
	}

	/**
	 * Add custom order status to registered order statusses.
	 *
	 * @param array $order_statuses Holds the array of order statusses.
	 * @return array
	 */
	public function wcep_wc_order_statuses_callback( $order_statuses ) {
		$order_statuses['wc-returned'] = _x( 'Returned', 'Order status', 'wc-easypost' );

		return $order_statuses;
	}

	/**
	 * AJAX served to get the pickup form fields.
	 */
	public function wcep_pickup_form_fields_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'pickup_form_fields' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcep_ajax_nonce = filter_input( INPUT_POST, 'wcep_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcep_ajax_nonce, 'wcep-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		// Order id.
		$order_id = (int) filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		$wc_order = wc_get_order( $order_id );

		// Line items.
		$items = $wc_order->get_items();

		// List of country and states.
		$country_states = wcep_get_country_states_list();

		// Customer's country.
		$customer_country = $wc_order->get_shipping_country();

		// Customer's state.
		$customer_state = $wc_order->get_shipping_state();

		// Customer's country and state.
		$customer_country_state = $customer_country;
		if ( ! empty( $customer_state ) ) {
			$customer_country_state = $customer_country . ':' . $customer_state;
		}

		// Prepate the HTML.
		ob_start();
		?>
		<table class="woocommerce-table woocommerce-table--order-details shop_table order_details wcep-order-details">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Item', 'wc-easypost' ); ?></th>
					<th>#</th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( ! empty( $items ) && is_array( $items ) ) {
					foreach ( $items as $item ) {
						$product_id   = $item->get_product_id();
						$variation_id = $item->get_variation_id();
						$prod_id      = wcep_product_id( $product_id, $variation_id );
						$product      = $item->get_product();
						?>
						<tr class="woocommerce-table__line-item order_item wcep-order-item" data-product-id="<?php echo esc_attr( $prod_id ); ?>" data-item-id="<?php echo esc_attr( $item->get_id() ); ?>">
							<td>
								<a href="<?php echo esc_url( get_permalink( $prod_id ) ); ?>"><?php echo wp_kses_post( $product->get_title() ); ?></a>
							</td>
							<td class="wcep-pickup-item">
								<input type="checkbox" />
							</td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
		<div class="wcep-pickup-other-data">
			<div class="customer-shipping-address">
				<h3><?php esc_html_e( 'Pickup from', 'wc-easypost' ); ?></h3>
				<p>
					<label for="customer-first-name"><?php esc_html_e( 'Name', 'wc-easypost' ); ?></label>
					<input type="text" id="customer-first-name" placeholder="<?php esc_html_e( 'first name..', 'wc-easypost' ); ?>" value="<?php echo esc_html( $wc_order->get_shipping_first_name() ); ?>" />
					<input type="text" id="customer-last-name" placeholder="<?php esc_html_e( 'last name..', 'wc-easypost' ); ?>" value="<?php echo esc_html( $wc_order->get_shipping_last_name() ); ?>" />
				</p>
				<p>
					<label for="customer-company"><?php esc_html_e( 'Company', 'wc-easypost' ); ?></label>
					<input type="text" id="customer-company" value="<?php echo esc_html( $wc_order->get_shipping_company() ); ?>" />
				</p>
				<p>
					<label for="customer-address"><?php esc_html_e( 'Address', 'wc-easypost' ); ?></label>
					<input type="text" id="customer-address" placeholder="<?php esc_html_e( 'address line 1..', 'wc-easypost' ); ?>" value="<?php echo esc_html( $wc_order->get_shipping_address_1() ); ?>" />
					<input type="text" id="customer-address-2" placeholder="<?php esc_html_e( 'address line 2..', 'wc-easypost' ); ?>" value="<?php echo esc_html( $wc_order->get_shipping_address_2() ); ?>" />
				</p>
				<p>
					<label for="customer-city"><?php esc_html_e( 'City & Postcode', 'wc-easypost' ); ?></label>
					<input type="text" id="customer-city" placeholder="<?php esc_html_e( 'city..', 'wc-easypost' ); ?>" value="<?php echo esc_html( $wc_order->get_shipping_city() ); ?>" />
					<input type="text" id="customer-postcode" placeholder="<?php esc_html_e( 'postcode..', 'wc-easypost' ); ?>" value="<?php echo esc_html( $wc_order->get_shipping_postcode() ); ?>" />
				</p>
				<p>
					<label for="customer-country-state"><?php esc_html_e( 'Country & State', 'wc-easypost' ); ?></label>
					<select id="customer-country-state">
						<option value=""><?php esc_html_e( 'Select country & state', 'wc-easypost' ); ?></option>
						<?php
						if ( ! empty( $country_states ) && is_array( $country_states ) ) {
							foreach ( $country_states as $country_code => $country_state ) {
								// Check if the country has states.
								if ( ! empty( $country_state['states'] ) ) {
									echo wp_kses(
										'<optgroup label="' . $country_state['name'] . '">',
										array(
											'optgroup' => array(
												'label' => array(),
											),
										)
									);
									foreach ( $country_state['states'] as $state_code => $state ) {
										$selected = ( "{$country_code}:{$state_code}" === $customer_country_state ) ? 'selected' : '';
										echo wp_kses(
											'<option value="' . $country_code . ':' . $state_code . '" ' . $selected . '>' . $country_state['name'] . ' — ' . $state . '</option>',
											array(
												'option' => array(
													'value'    => array(),
													'selected' => array(),
												),
											)
										);
									}
									echo wp_kses_post( '</optgroup>' );
								} else {
									// Country does not have any state.
									$selected = ( $customer_country_state === $country_code ) ? 'selected' : '';
									echo wp_kses(
										'<option value="' . $country_code . '" ' . $selected . '>' . $country_state['name'] . '</option>',
										array(
											'option' => array(
												'value'    => array(),
												'selected' => array(),
											),
										)
									);
								}
							}
						}
						?>
					</select>
				</p>
				<p>
					<label for="customer-phone"><?php esc_html_e( 'Phone', 'wc-easypost' ); ?></label>
					<input type="tel" id="customer-phone" value="<?php echo esc_html( $wc_order->get_billing_phone() ); ?>" />
				</p>
			</div>
			<div class="other-details">
				<h3><?php esc_html_e( 'Other details', 'wc-easypost' ); ?></h3>
				<p>
					<label for="pickup-reference"><?php esc_html_e( 'Reference', 'wc-easypost' ); ?></label>
					<input type="text" id="pickup-reference" placeholder="<?php esc_html_e( 'e.g.: my-first-pickup', 'wc-easypost' ); ?>" />
				</p>
				<p>
					<label for="pickup-date"><?php esc_html_e( 'Pickup date', 'wc-easypost' ); ?></label>
					<input type="text" id="pickup-date" />
				</p>
				<p>
					<label for="pickup-instructions"><?php esc_html_e( 'Any Special Message', 'wc-easypost' ); ?></label>
					<textarea id="pickup-instructions" placeholder="<?php esc_html_e( 'E.g.: Call me 10mins before collecting the item(s).', 'wc-easypost' ); ?>"></textarea>
				</p>
				<p>
					<label for="is-account-address"><?php esc_html_e( 'Is Account Address?', 'wc-easypost' ); ?></label>
					<input type="checkbox" id="is-account-address" />
				</p>
			</div>
			<input type="hidden" id="wcep-order-id" value="<?php echo esc_attr( $order_id ); ?>" />
		</div>
		<?php
		$html = ob_get_clean();

		// Send the AJAX response now.
		wp_send_json_success(
			array(
				'code' => 'wcep-pickup-form-fields-fetched',
				'html' => $html,
			)
		);
		wp_die();
	}

	/**
	 * AJAX served to generate pickup.
	 */
	public function wcep_create_pickup_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'create_pickup' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcep_ajax_nonce = filter_input( INPUT_POST, 'wcep_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcep_ajax_nonce, 'wcep-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		// Posted data.
		$order_id               = (int) filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		$item_ids               = wp_unslash( $_POST['item_ids'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- Items IDs not validated.
		$reference              = filter_input( INPUT_POST, 'reference', FILTER_SANITIZE_STRING );
		$pickup_date            = filter_input( INPUT_POST, 'pickup_date', FILTER_SANITIZE_STRING );
		$instructions           = filter_input( INPUT_POST, 'instructions', FILTER_SANITIZE_STRING );
		$is_account_address     = filter_input( INPUT_POST, 'is_account_address', FILTER_SANITIZE_STRING );
		$customer_first_name    = filter_input( INPUT_POST, 'customer_first_name', FILTER_SANITIZE_STRING );
		$customer_last_name     = filter_input( INPUT_POST, 'customer_last_name', FILTER_SANITIZE_STRING );
		$customer_company       = filter_input( INPUT_POST, 'customer_company', FILTER_SANITIZE_STRING );
		$customer_address       = filter_input( INPUT_POST, 'customer_address', FILTER_SANITIZE_STRING );
		$customer_address_2     = filter_input( INPUT_POST, 'customer_address_2', FILTER_SANITIZE_STRING );
		$customer_city          = filter_input( INPUT_POST, 'customer_city', FILTER_SANITIZE_STRING );
		$customer_postcode      = filter_input( INPUT_POST, 'customer_postcode', FILTER_SANITIZE_STRING );
		$customer_country_state = filter_input( INPUT_POST, 'customer_country_state', FILTER_SANITIZE_STRING );
		$customer_phone         = filter_input( INPUT_POST, 'customer_phone', FILTER_SANITIZE_STRING );
		$apikey                 = wcep_get_api_key();
		$wc_order               = wc_get_order( $order_id );

		// Separate the country and state.
		$customer_country_state_exploded = explode( ':', $customer_country_state );

		// Require the easypost autoload file.
		require WCEP_PLUGIN_PATH . 'vendor/autoload.php';
		\EasyPost\EasyPost::setApiKey( $apikey );

		if ( empty( $item_ids ) || ! is_array( $item_ids ) ) {
			echo 0;
			wp_die();
		}

		// Loop in the items.
		foreach ( $item_ids as $item_id ) {
			// From address.
			$from_address = \EasyPost\Address::create(
				array(
					'street1' => $customer_address,
					'street2' => $customer_address_2,
					'city'    => $customer_city,
					'state'   => ( ! empty( $customer_country_state_exploded[1] ) ) ? $customer_country_state_exploded[1] : '',
					'zip'     => $customer_postcode,
					'country' => ( ! empty( $customer_country_state_exploded[0] ) ) ? $customer_country_state_exploded[0] : '',
					'company' => $customer_company,
					'phone'   => $customer_phone,
				)
			);

			// Shipment.
			$shipment_id = wc_get_order_item_meta( $item_id, 'shipment_id', true );
			$shipment    = \EasyPost\Shipment::retrieve( $shipment_id );

			try {
				$pickup = \EasyPost\Pickup::create(
					array(
						'address'            => $from_address,
						'shipment'           => $shipment,
						'reference'          => $reference,
						'min_datetime'       => $pickup_date,
						'max_datetime'       => gmdate( 'Y-m-d', strtotime( $pickup_date . ' +1 day' ) ),
						'is_account_address' => ( 'yes' === $is_account_address ) ? true : false,
						'instructions'       => $instructions,
					)
				);

				wc_add_order_item_meta( $item_id, 'pickup_id', $pickup->id, true );
			} catch ( \EasyPost\Error $e ) {
				$error_message = $e->getMessage();
				// Send the AJAX error response.
				wp_send_json_success(
					array(
						'code'                 => 'wcep-pickup-not-created',
						/* translators: 1: %s: error message */
						'notification_message' => sprintf( __( 'Pickup not created due to the error: %1$s', 'wc-easypost' ), $error_message ),
					)
				);
				wp_die();
			}
		}

		// Update the order status to return.
		$wc_order->update_status( 'returned' );

		// Send the AJAX response now.
		wp_send_json_success(
			array(
				'code'                 => 'wcep-pickup-created',
				'notification_message' => __( 'Pick is successfully created for the selected items.', 'wc-easypost' ),
			)
		);
		wp_die();
	}
}
