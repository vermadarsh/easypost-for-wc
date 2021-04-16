<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Easypost_For_Wc
 * @subpackage Easypost_For_Wc/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Easypost_For_Wc
 * @subpackage Easypost_For_Wc/admin
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class Easypost_For_Wc_Admin {

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
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function wcep_admin_enqueue_scripts_callback() {
		global $current_section, $post;

		$enqueue_scripts         = false;
		$enqueue_print_label_css = false;

		if ( ! empty( $current_section ) && 'wceasypost' === $current_section ) {
			$enqueue_scripts = true;
		}

		if ( ! empty( $post->ID ) && 'shop_order' === $post->post_type ) {
			$enqueue_scripts         = true;
			$enqueue_print_label_css = true;
		}

		if ( ! $enqueue_scripts ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name . '-font-awesome',
			WCEP_PLUGIN_URL . 'admin/css/font-awesome.min.css',
			array(),
			filemtime( WCEP_PLUGIN_PATH . 'admin/css/font-awesome.min.css' ),
			'all'
		);

		if ( $enqueue_print_label_css ) {
			wp_enqueue_style(
				$this->plugin_name . '-print-label',
				WCEP_PLUGIN_URL . 'admin/css/easypost-print-label-admin.css',
				array(),
				filemtime( WCEP_PLUGIN_PATH . 'admin/css/easypost-print-label-admin.css' )
			);
		}

		wp_enqueue_style(
			$this->plugin_name,
			WCEP_PLUGIN_URL . 'admin/css/easypost-for-wc-admin.css',
			array(),
			filemtime( WCEP_PLUGIN_PATH . 'admin/css/easypost-for-wc-admin.css' )
		);

		wp_enqueue_script(
			$this->plugin_name,
			WCEP_PLUGIN_URL . 'admin/js/easypost-for-wc-admin.js',
			array( 'jquery' ),
			filemtime( WCEP_PLUGIN_PATH . 'admin/js/easypost-for-wc-admin.js' ),
			true
		);

		// Localize variables.
		wp_localize_script(
			$this->plugin_name,
			'WCEP_Admin_JS_Obj',
			array(
				'ajaxurl'                            => admin_url( 'admin-ajax.php' ),
				'carrier_empty'                      => __( 'Please select any carrier.', 'wc-easypost' ),
				'tracking_id_empty'                  => __( 'Please provide a tracking ID.', 'wc-easypost' ),
				'wcep_ajax_nonce'                    => wp_create_nonce( 'wcep-ajax-nonce' ),
				'processing_button_text'             => apply_filters( 'wcep_processing_button_text', __( 'Processing...', 'wc-easypost' ) ),
				'notification_success_header'        => apply_filters( 'wcep_notification_success_header', __( 'Success', 'wc-easypost' ) ),
				'notification_error_header'          => apply_filters( 'wcep_notification_error_header', __( 'Error', 'wc-easypost' ) ),
				'invalid_ajax_response'              => apply_filters( 'wcep_invalid_ajax_response', __( 'Invalid AJAX response.', 'wc-easypost' ) ),
				'ajax_nonce_failure'                 => apply_filters( 'wcep_ajax_nonce_failure_error', __( 'Action couldn\'t be taken due to security failure. Please try again later.', 'wc-easypost' ) ),
				'in_inches'                          => __( '(in inches)', 'wc-easypost' ),
				'generating_label_preview_btn_text'  => __( 'Generating label preview...', 'wc-easypost' ),
				'regenerate_shipping_label_btn_text' => __( 'Regenerate Shipping Label', 'wc-easypost' ),
				'regenerate_return_label_btn_text'   => __( 'Regenerate Shipping Label', 'wc-easypost' ),
				'no_labels_available_error'          => __( 'No labels generated yet.', 'wc-easypost' ),
				'available_labels_heading'           => __( 'Available Labels', 'wc-easypost' ),
				'email_recipients_heading'           => __( 'Email Recipients', 'wc-easypost' ),
				'postage_label_title'                => __( 'Postage', 'wc-easypost' ),
				'return_label_title'                 => __( 'Return', 'wc-easypost' ),
				'add_recipient_title'                => __( 'Add recipient', 'wc-easypost' ),
				'remove_recipient_title'             => __( 'Remove recipient', 'wc-easypost' ),
				'selected_labels_empty'              => __( 'Select labels that should be emailed.', 'wc-easypost' ),
				'email_recipients_empty'             => __( 'Emails are either not provided or are invalid.', 'wc-easypost' ),
			)
		);
	}

	/**
	 * Admin settings for EasyPost.
	 *
	 * @param array $sections Array of WC shipping tab sections.
	 */
	public function wcep_woocommerce_get_sections_shipping_callback( $sections ) {
		$sections['wceasypost'] = __( 'EasyPost', 'wc-easypost' );

		return $sections;
	}

	/**
	 * Add custom section to WooCommerce settings products tab.
	 *
	 * @param array $settings Holds the woocommerce settings fields array.
	 * @param array $current_section Holds the wcbogo settings fields array.
	 * @return array
	 */
	public function wcep_woocommerce_get_settings_shipping_callback( $settings, $current_section ) {
		// Check the current section is what we want.
		if ( 'wceasypost' === $current_section ) {
			return $this->wcep_general_settings_fields();
		} else {
			return $settings;
		}
	}

	/**
	 * Return the fields for general settings.
	 *
	 * @return array
	 */
	private function wcep_general_settings_fields() {
		$settings = include WCEP_PLUGIN_PATH . 'admin/settings/easypost-settings.php';

		return $settings;
	}

	/**
	 * Add custom shipping method to the default shipping methods.
	 *
	 * @param array $shipping_methods Holds the default shipping methods.
	 * @return array
	 */
	public function wcep_woocommerce_shipping_methods_callback( $shipping_methods = array() ) {

		if ( ! array_key_exists( 'wcep-easypost-shipping-method', $shipping_methods ) ) {
			$shipping_methods['wcep-easypost-shipping-method'] = 'WCEP_EasyPost_Shipping_Method';
		}

		return $shipping_methods;
	}

	/**
	 * Include the shipping class file.
	 */
	public function wcep_woocommerce_shipping_init_callback() {
		require_once WCEP_PLUGIN_PATH . 'admin/shipping/class-wcep-easypost-shipping-method.php';
	}

	/**
	 * Add custom metaboxes.
	 */
	public function wcep_add_meta_boxes_callback() {
		global $post;

		if ( empty( $post->ID ) || 'shop_order' !== $post->post_type ) {
			return;
		}

		$shipment_id = get_post_meta( $post->ID, 'wcep_shipment_id', true );

		if ( empty( $shipment_id ) ) {
			return;
		}

		// Add metabox on shop order for tracking.
		add_meta_box(
			'wcep-track-shipment',
			__( 'EasyPost: Shipment Tracking', 'wc-easypost' ),
			array(
				$this,
				'wcep_easypost_shipment_tracking_callback',
			),
			'shop_order',
			'side'
		);

		// Add metabox on shop order for shipment/return labels.
		add_meta_box(
			'wcep-shipment-label-metabox',
			__( 'EasyPost: Shipment/Return Labels', 'wc-easypost' ),
			array(
				$this,
				'wcep_easypost_shipment_return_labels_callback',
			),
			'shop_order',
			'normal'
		);
	}

	/**
	 * Shipment tracking metabox callback.
	 *
	 * @param object $post Holds the WordPress post object.
	 */
	public function wcep_easypost_shipment_tracking_callback( $post ) {
		$carriers = array_merge(
			array( '' => __( 'Select the carrier', 'wc-easypost' ) ),
			wcep_get_carriers()
		);

		$tracking_info = get_post_meta( $post->ID, 'wcep_tracking_info', true );

		echo '<ul class="order_notes wcep-tracking-information">';
		if ( ! empty( $tracking_info ) && is_array( $tracking_info ) ) {
			echo wp_kses_post( wcep_create_tracking_info_html( $tracking_info ) );
		}
		echo '</ul>';

		// Carrier.
		woocommerce_wp_select(
			array(
				'id'            => 'wcep-shipment-carrier',
				'wrapper_class' => 'wcep-shipment-carrier',
				'label'         => __( 'Carrier', 'wc-easypost' ),
				'description'   => __( 'The carrier that shall deliver this order.', 'wc-easypost' ),
				'desc_tip'      => true,
				'options'       => $carriers,
				'class'         => 'wcep-shipment-carrier',
				'value'         => ( ! empty( $tracking_info['carrier'] ) ) ? $tracking_info['carrier'] : '',
			)
		);

		// Tracking ID.
		woocommerce_wp_textarea_input(
			array(
				'id'          => 'wcep-shipment-tracking-id',
				'name'        => 'wcep-shipment-tracking-id',
				'class'       => 'wcep-shipment-tracking-id short',
				'label'       => __( 'Tracking ID', 'wc-easypost' ),
				'description' => __( 'Shipment tracking ID for this order.', 'wc-easypost' ),
				'desc_tip'    => true,
				'placeholder' => __( 'xxx', 'wc-easypost' ),
				'value'       => ( ! empty( $tracking_info['tracking_id'] ) ) ? $tracking_info['tracking_id'] : '',
			)
		);

		// Shipment Date.
		woocommerce_wp_text_input(
			array(
				'id'          => 'wcep-shipment-date',
				'name'        => 'wcep-shipment-date',
				'label'       => __( 'Shipment Date', 'wc-easypost' ),
				'placeholder' => 'YYYY-MM-DD',
				'description' => __( 'The date when the shipment needs to be delivered.', 'wc-easypost' ),
				'desc_tip'    => true,
				'type'        => 'text',
				'class'       => 'wcep-shipment-date',
				'value'       => ( ! empty( $tracking_info['shipment_date'] ) ) ? $tracking_info['shipment_date'] : '',
			)
		);

		// Submit button.
		submit_button(
			__( 'Save/Show Tracking Info', 'wc-easypost' ),
			'secondary',
			'wcep-show-tracking-info',
			true
		);
	}

	/**
	 * Shipment/return printing labels metabox callback.
	 *
	 * @param object $post Holds the WordPress post object.
	 */
	public function wcep_easypost_shipment_return_labels_callback( $post ) {
		require_once WCEP_PLUGIN_PATH . 'admin/settings/metaboxes/print-label.php';
	}

	/**
	 * Add custom assets to WordPress footer section.
	 */
	public function wcep_admin_footer_callback() {
		global $post;

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

		// Include the modals on order edit page only.
		if ( ! empty( $post->ID ) && 'shop_order' === $post->post_type ) {
			// Include the line item edit modal.
			echo $this->wcep_edit_line_item_modal_html();

			// Include the email label modal.
			echo $this->wcep_email_label_modal_html();
		}
	}

	/**
	 * Modal HTML for edit line item functionality.
	 *
	 * @return string
	 */
	private function wcep_edit_line_item_modal_html() {
		ob_start();
		?>
		<div class="wcep-modal" id="wcep-edit-line-item-modal">
			<article class="content-wrapper">
				<button class="close"></button>
				<header class="modal-header">
					<h2><?php esc_html_e( 'Update Item', 'wcep-easypost' ); ?></h2>
				</header>
				<div class="content" style="overflow-x: auto;">
					<table class="wcep-edit-line-items-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Length', 'wc-easypost' ); ?></th>
								<th><?php esc_html_e( 'Width', 'wc-easypost' ); ?></th>
								<th><?php esc_html_e( 'Height', 'wc-easypost' ); ?></th>
								<th><?php esc_html_e( 'Weight', 'wc-easypost' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<input type="number" class="wcep-item-shipping-data" id="wcep-item-length" />
								</td>
								<td>
									<input type="number" class="wcep-item-shipping-data" id="wcep-item-width" />
								</td>
								<td>
									<input type="number" class="wcep-item-shipping-data" id="wcep-item-height" />
								</td>
								<td>
									<input type="number" class="wcep-item-shipping-data" id="wcep-item-weight" />
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<footer class="modal-footer">
					<button type="button" class="button wcep-update-order-line-item"><?php esc_html_e( 'Update', 'wc-easypost' ); ?></button>
				</footer>
			</article>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Modal HTML for emailing labels functionality.
	 *
	 * @return string
	 */
	private function wcep_email_label_modal_html() {
		ob_start();
		?>
		<div class="wcep-modal" id="wcep-email-label-modal">
			<article class="content-wrapper">
				<button class="close"></button>
				<header class="modal-header">
					<h2><?php esc_html_e( 'Email Label', 'wcep-easypost' ); ?></h2>
				</header>
				<div class="content"></div>
				<footer class="modal-footer">
					<button type="button" class="button wcep-email-label"><?php esc_html_e( 'Email', 'wc-easypost' ); ?></button>
				</footer>
			</article>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * AJAX served to save tracking info.
	 */
	public function wcep_save_tracking_info_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'save_tracking_info' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcep_ajax_nonce = filter_input( INPUT_POST, 'wcep_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcep_ajax_nonce, 'wcep-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		$carrier       = filter_input( INPUT_POST, 'carrier', FILTER_SANITIZE_STRING );
		$tracking_id   = filter_input( INPUT_POST, 'tracking_id', FILTER_SANITIZE_STRING );
		$shipment_date = filter_input( INPUT_POST, 'shipment_date', FILTER_SANITIZE_STRING );
		$order_id      = (int) filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );

		// Trim the extra spaces in the tracking IDs.
		$tracking_id = trim( $tracking_id );

		// Get the tracker info from easypost.
		$apikey = wcep_get_api_key();
		// Require the easypost autoload file.
		require WCEP_PLUGIN_PATH . 'vendor/autoload.php';
		\EasyPost\EasyPost::setApiKey( $apikey );

		try {
			$tracker = \EasyPost\Tracker::create(
				array(
					'tracking_code' => $tracking_id,
					'carrier'       => $carrier,
				)
			);
		} catch ( \EasyPost\Error $e ) {
			wp_send_json_success(
				array(
					'code'                 => 'wcep-tracking-info-not-saved',
					/* translators: 1: %s: opening strong tag, 2: %s: closing strong tag, 3: %s: error message */
					'notification_message' => sprintf( __( 'Tracking info not saved due to the error, %1$s%3$s%2$s.', 'wc-easypost' ), '<strong>', '</strong>', $e->getMessage() ),
					'html'                 => '',
				)
			);
			wp_die();
		}

		$ep_tracking_id   = $tracker->id;
		$tracking_message = ( ! empty( $tracker->tracking_details[0]->message ) ) ? $tracker->tracking_details[0]->message : '';

		// Currently provided tracking info.
		$tracking_info = array(
			'carrier'                   => $carrier,
			'tracking_id'               => $tracking_id,
			'shipment_date'             => $shipment_date,
			'current_date'              => gmdate( 'Y-m-d H:i:s' ),
			'easypost_tracking_id'      => $ep_tracking_id,
			'easypost_tracking_message' => $tracking_message,
		);

		// Update the database.
		update_post_meta( $order_id, 'wcep_tracking_info', $tracking_info );

		// Send the AJAX response now.
		wp_send_json_success(
			array(
				'code'                 => 'wcep-tracking-info-saved',
				'notification_message' => __( 'Tracking information saved.', 'wc-easypost' ),
				'html'                 => wcep_create_tracking_info_html( $tracking_info ),
			)
		);
		wp_die();
	}

	/**
	 * AJAX served to generate shipping label.
	 */
	public function wcep_generate_shipping_label_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'generate_shipping_label' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcep_ajax_nonce = filter_input( INPUT_POST, 'wcep_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcep_ajax_nonce, 'wcep-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		// Posted order line items.
		$items = wp_unslash( $_POST['items'] );

		if ( empty( $items ) || ! is_array( $items ) ) {
			echo 0;
			wp_die();
		}

		// Rest of the posted data.
		$is_return      = filter_input( INPUT_POST, 'is_return', FILTER_SANITIZE_STRING );
		$order_id       = (int) filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		$order          = wc_get_order( $order_id );
		$postage_labels = array();
		$return_labels  = array();
		$labels         = array();

		// Get shipping insurance fee.
		$extra_fee = $order->get_items( 'fee' );
		$fee_cost  = 0.00;

		if ( ! empty( $extra_fee ) ) {
			foreach ( $extra_fee as $fee ) {
				$fee_name = $fee->get_name();

				if ( 'Insurance Cost' === $fee_name ) {
					$fee_cost = (float) $fee->get_total();
				}
			}
		}

		// Get the destination address.
		$shipping_first_name = $order->get_shipping_first_name();
		$shipping_last_name  = $order->get_shipping_last_name();
		$shipping_address_1  = $order->get_shipping_address_1();
		$shipping_city       = $order->get_shipping_city();
		$shipping_state      = $order->get_shipping_state();
		$shipping_postcode   = $order->get_shipping_postcode();
		$billing_phone       = $order->get_billing_phone();

		$customer_name = '';
		if ( ! empty( $shipping_first_name ) && ! empty( $shipping_last_name ) ) {
			$customer_name = "{$shipping_first_name} {$shipping_last_name}";
		} else {
			$billing_first_name = $order->get_billing_first_name();
			$billing_last_name  = $order->get_billing_last_name();
			$customer_name      = "{$billing_first_name} {$billing_last_name}";
		}

		// Get the easypost.com api key.
		$apikey = wcep_get_api_key();

		// Require the easypost autoload file.
		require WCEP_PLUGIN_PATH . 'vendor/autoload.php';
		\EasyPost\EasyPost::setApiKey( $apikey );

		// From address.
		$store_address = wcep_get_business_location();

		foreach ( $items as $item ) {
			$to_address   = \EasyPost\Address::create(
				array(
					'name'    => $customer_name,
					'street1' => ( ! empty( $shipping_address_1 ) ) ? $shipping_address_1 : $order->get_billing_address_1(),
					'city'    => ( ! empty( $shipping_city ) ) ? $shipping_city : $order->get_billing_city(),
					'state'   => ( ! empty( $shipping_state ) ) ? $shipping_state : $order->get_billing_state(),
					'zip'     => ( ! empty( $shipping_postcode ) ) ? $shipping_postcode : $order->get_billing_postcode(),
					'phone'   => ( ! empty( $billing_phone ) ) ? $billing_phone : '',
				)
			);
			$from_address = \EasyPost\Address::create(
				array(
					'company' => ( ! empty( $store_address['company'] ) ) ? $store_address['company'] : '',
					'street1' => ( ! empty( $store_address['address_1'] ) ) ? $store_address['address_1'] : '',
					'street2' => ( ! empty( $store_address['address_2'] ) ) ? $store_address['address_2'] : '',
					'city'    => ( ! empty( $store_address['city'] ) ) ? $store_address['city'] : '',
					'state'   => ( ! empty( $store_address['state'] ) ) ? $store_address['state'] : '',
					'zip'     => ( ! empty( $store_address['postcode'] ) ) ? $store_address['postcode'] : '',
					'phone'   => ( ! empty( $store_address['phone'] ) ) ? $store_address['phone'] : '',
				)
			);
			$parcel       = \EasyPost\Parcel::create(
				array(
					'length' => $item['length'],
					'width'  => $item['width'],
					'height' => $item['height'],
					'weight' => $item['weight'],
				)
			);

			$shipment_args = array(
				'to_address'   => $to_address,
				'from_address' => $from_address,
				'parcel'       => $parcel,
			);

			// Check to see if the request is for return label.
			if ( 'yes' === $is_return ) {
				$shipment_args['is_return'] = true;
			}

			// Create the shipment now.
			$shipment = \EasyPost\Shipment::create( $shipment_args );

			// Select the lowest shipment rate.
			$shipment->buy( $shipment->lowest_rate() );

			if ( 0.00 !== $fee_cost ) {
				$shipment->insure( array( 'amount' => $fee_cost ) );
			}

			// Add the postage/return label to the array collection.
			if ( 'yes' === $is_return ) {
				$return_labels[] = $shipment->postage_label->label_url;

				// Update this shipment ID to the item meta.
				wc_add_order_item_meta( $item['item_id'], 'return_shipment_id', $shipment->id, true );
			} else {
				$postage_labels[] = $shipment->postage_label->label_url;

				// Update this shipment ID to the item meta.
				wc_add_order_item_meta( $item['item_id'], 'shipment_id', $shipment->id, true );
			}
		}

		// Save the labels to the database.
		if ( ! empty( $postage_labels ) || ! empty( $return_labels ) ) {
			if ( 'yes' === $is_return ) {
				$notification_message = __( 'Return labels created.', 'wc-easypost' );
				$labels               = $return_labels;
				update_post_meta( $order_id, 'wcep_return_labels', $return_labels );
			} else {
				$notification_message = __( 'Postage labels created.', 'wc-easypost' );
				$labels               = $postage_labels;
				update_post_meta( $order_id, 'wcep_postage_labels', $postage_labels );
			}
			$code = 'wcep-postage-labels-created';
		} else {
			$code = 'wcep-postage-labels-not-created';
			if ( 'yes' === $is_return ) {
				$notification_message = __( 'Return labels not created.', 'wc-easypost' );
			} else {
				$notification_message = __( 'Postage labels not created.', 'wc-easypost' );
			}
		}

		wp_send_json_success(
			array(
				'code'                 => $code,
				'notification_message' => $notification_message,
				'postage_labels'       => $labels,
			)
		);
		wp_die();
	}

	/**
	 * AJAX served to generate preview of the postage labels.
	 */
	public function wcep_generate_postage_label_preview_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'generate_postage_label_preview' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcep_ajax_nonce = filter_input( INPUT_POST, 'wcep_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcep_ajax_nonce, 'wcep-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		// Get postage labels.
		$postage_labels = wp_unslash( $_POST['postage_labels'] );

		if ( empty( $postage_labels ) || ! is_array( $postage_labels ) ) {
			echo 0;
			wp_die();
		}

		$label_preview = '';

		foreach ( $postage_labels as $label_image ) {
			$label_preview .= wcep_get_label_preview_image_html( $label_image );
		}

		wp_send_json_success(
			array(
				'code'                 => 'wcep-postage-labels-preview-created',
				'notification_message' => __( 'Postage labels preview created.', 'wc-easypost' ),
				'html'                 => $label_preview,
			)
		);
		wp_die();
	}

	/**
	 * AJAX served to email labels.
	 */
	public function wcep_email_label_callback() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'email_label' !== $action ) {
			echo 0;
			wp_die();
		}

		// Check ajax nonce.
		$wcep_ajax_nonce = filter_input( INPUT_POST, 'wcep_ajax_nonce', FILTER_SANITIZE_STRING );

		if ( ! wp_verify_nonce( $wcep_ajax_nonce, 'wcep-ajax-nonce' ) ) {
			echo -1;
			wp_die();
		}

		// Get posted data.
		$email_recipients    = wp_unslash( $_POST['email_recipients'] );
		$order_id            = (int) filter_input( INPUT_POST, 'order_id', FILTER_SANITIZE_NUMBER_INT );
		$email_postage_label = filter_input( INPUT_POST, 'email_postage_label', FILTER_SANITIZE_STRING );
		$email_return_label  = filter_input( INPUT_POST, 'email_return_label', FILTER_SANITIZE_STRING );
		$labels              = array();
		$website_title       = get_bloginfo( 'title' );
		$admin_email         = get_option( 'admin_email' );

		// If postage labels are requested to be emailed.
		if ( 'yes' === $email_postage_label ) {
			$labels = array_merge( $labels, get_post_meta( $order_id, 'wcep_postage_labels', true ) );
		}

		// If return labels are requested to be emailed.
		if ( 'yes' === $email_return_label ) {
			$labels = array_merge( $labels, get_post_meta( $order_id, 'wcep_return_labels', true ) );
		}

		/**
		 * Email label attachments filter.
		 *
		 * This filter helps in customizing the list of labels that are sent as attachments.
		 *
		 * @param array $labels Holds the email label attachments.
		 * @param int   $order_id Holds the order ID.
		 * @return array
		 */
		$labels = apply_filters( 'wcep_email_attachments', $labels, $order_id );

		$subject = __( 'Labels', 'wc-easypost' );

		/**
		 * Email subject filter.
		 *
		 * This filter helps in customizing the email subject, of the email that shoots the labels.
		 *
		 * @param string $subject Holds the email subject.
		 * @param int    $order_id Holds the order ID.
		 * @return string
		 */
		$subject = apply_filters( 'wcep_email_label_subject', $subject, $order_id );

		// Email content.
		ob_start();
		?>
		<div>
			<p><?php esc_html_e( 'Hello,', 'wc-easypost' ); ?></p>
			<p><?php esc_html_e( 'Please find the following labels:', 'wc-easypost' ); ?></p>
			<?php
			foreach ( $labels as $label ) {
				echo "<img src='" . esc_url( $label ) . "' width='130px' style='margin-right: 20px;' />";
			}
			?>
			<p><?php esc_html_e( 'Thank you,', 'wc-easypost' ); ?></p>
			<p><?php echo wp_kses_post( $website_title ); ?></p>
		</div>
		<?php
		$email_content = ob_get_clean();

		/**
		 * Email content filter.
		 *
		 * This filter helps in customizing the email content, of the email that shoots the labels.
		 *
		 * @param string $subject Holds the email subject.
		 * @param int    $order_id Holds the order ID.
		 * @return string
		 */
		$email_content = apply_filters( 'wcep_email_label_content', $email_content, $order_id );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			"From: {$website_title} <{$admin_email}>",
		);
		wp_mail( $email_recipients, $subject, $email_content, $headers );

		wp_send_json_success(
			array(
				'code'                 => 'wcep-labels-emailed',
				'notification_message' => __( 'Labels are emailed. Please check your mailbox.', 'wc-easypost' ),
			)
		);
		wp_die();
	}

	/**
	 * Save the date when the order is marked as complete.
	 *
	 * @param int $order_id Holds the order ID.
	 */
	public function wcep_woocommerce_order_status_completed_callback( $order_id ) {
		update_post_meta( $order_id, 'wcep_order_completed', time() );
	}
}
