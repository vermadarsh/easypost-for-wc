<?php
/**
 * EasyPost admin settings.
 *
 * @version 1.0.0
 * @package Easypost_For_Wc
 * @subpackage Easypost_For_Wc/admin/settings
 */

defined( 'ABSPATH' ) || exit;

$sandbox_enabled      = wcep_is_sandbox_mode_on();
$sandbox_key_class    = ( true === $sandbox_enabled ) ? '' : 'dnone';
$production_key_class = ( true === $sandbox_enabled ) ? 'dnone' : '';

$settings_fields = array(
	array(
		'title' => __( 'EasyPost Settings', 'wc-easypost' ),
		'type'  => 'title',
		'desc'  => __( 'These settings are built to integrate EasyPost.', 'wc-easypost' ),
		'id'    => 'wcep_general_settings_title',
	),
	array(
		'name' => __( 'Enable EasyPost', 'wc-easypost' ),
		'type' => 'checkbox',
		'desc' => __( 'Check this checkbox to allow the integration function.', 'wc-easypost' ),
		'id'   => 'wcep_enable_easypost',
	),
	array(
		'name' => __( 'Sandbox Mode', 'wc-easypost' ),
		'type' => 'checkbox',
		'desc' => __( 'Check this checkbox to enable easypost sandbox mode.', 'wc-easypost' ),
		'id'   => 'wcep_enable_easypost_sandbox',
	),
	array(
		'title'       => __( 'Sandbox API Key', 'wc-easypost' ),
		'desc'        => __( 'This sets the API key for the sandbox mode.', 'wc-easypost' ),
		'desc_tip'    => true,
		'id'          => 'wcep_sandbox_api_key',
		'type'        => 'password',
		'placeholder' => 'EZ******************',
		'class'       => "{$sandbox_key_class}",
	),
	array(
		'title'       => __( 'Production API Key', 'wc-easypost' ),
		'desc'        => __( 'This sets the API key for the production mode.', 'wc-easypost' ),
		'desc_tip'    => true,
		'id'          => 'wcep_production_api_key',
		'type'        => 'password',
		'placeholder' => 'EZ******************',
		'class'       => "{$production_key_class}",
	),
	array(
		'title'             => __( 'Shipment Insurance Amount', 'wc-easypost' ),
		'desc'              => __( 'This sets the amount for shipment insurance. Leave blank to skip shipment insurance.', 'wc-easypost' ),
		'desc_tip'          => true,
		'id'                => 'wcep_shipment_insurance_cost',
		'type'              => 'number',
		'placeholder'       => 0.00,
		'custom_attributes' => array(
			'min'  => 0,
			'step' => 0.001,
		),
	),
	array(
		'title'    => __( 'Estimated Delivery Text Color', 'wc-easypost' ),
		'desc'     => __( 'This sets the text color of the estimated delivery time in the shipping section in public end.', 'wc-easypost' ),
		'desc_tip' => true,
		'id'       => 'wcep_estimated_delivery_text_color',
		'type'     => 'color',
		'class'    => '',
	),
	array(
		'title'    => __( 'Estimated Delivery Background Color', 'wc-easypost' ),
		'desc'     => __( 'This sets the background color of the estimated delivery time in the shipping section in public end.', 'wc-easypost' ),
		'desc_tip' => true,
		'id'       => 'wcep_estimated_delivery_background_color',
		'type'     => 'color',
		'class'    => '',
	),
	array(
		'name' => __( 'Checkout Address Verification', 'wc-easypost' ),
		'type' => 'checkbox',
		'desc' => __( 'Check this checkbox to allow the easypost API to verify customer\'s shipping address before the order is placed.', 'wc-easypost' ),
		'id'   => 'wcep_verify_address_at_checkout',
	),
	array(
		'title'             => __( 'Pickup Facility Avaiability after Order Completion', 'wc-easypost' ),
		'desc'              => __( 'This sets the number of days until when the customer can avail the pickup facility after the order is marked as complete. Leave blank to disallow pickup facility.', 'wc-easypost' ),
		'desc_tip'          => true,
		'id'                => 'wcep_pickup_avaiability_after_order_completion',
		'type'              => 'number',
		'placeholder'       => 0,
		'custom_attributes' => array(
			'min'  => 1,
			'step' => 1,
		),
	),
	array(
		'type' => 'sectionend',
		'id'   => 'wcep_general_settings_end',
	),
	array(
		'title' => __( 'Source Location', 'wc-easypost' ),
		'type'  => 'title',
		'desc'  => '',
		'id'    => 'wcep_source_location_settings_title',
	),
	array(
		'title'    => __( 'Company', 'wc-easypost' ),
		'desc'     => __( 'The official name of your business.', 'wc-easypost' ),
		'id'       => 'wcep_woocommerce_store_name',
		'default'  => '',
		'type'     => 'text',
		'desc_tip' => true,
	),
	array(
		'title'    => __( 'Address line 1', 'wc-easypost' ),
		'desc'     => __( 'The street address for your business location.', 'wc-easypost' ),
		'id'       => 'wcep_woocommerce_store_address',
		'default'  => '',
		'type'     => 'text',
		'desc_tip' => true,
	),
	array(
		'title'    => __( 'Address line 2', 'wc-easypost' ),
		'desc'     => __( 'An additional, optional address line for your business location.', 'wc-easypost' ),
		'id'       => 'wcep_woocommerce_store_address_2',
		'default'  => '',
		'type'     => 'text',
		'desc_tip' => true,
	),
	array(
		'title'    => __( 'City', 'wc-easypost' ),
		'desc'     => __( 'The city in which your business is located.', 'wc-easypost' ),
		'id'       => 'wcep_woocommerce_store_city',
		'default'  => '',
		'type'     => 'text',
		'desc_tip' => true,
	),
	array(
		'title'    => __( 'Country / State', 'wc-easypost' ),
		'desc'     => __( 'The country and state or province, if any, in which your business is located.', 'wc-easypost' ),
		'id'       => 'wcep_woocommerce_default_country',
		'default'  => 'US',
		'type'     => 'single_select_country',
		'desc_tip' => true,
	),
	array(
		'title'    => __( 'Postcode / ZIP', 'wc-easypost' ),
		'desc'     => __( 'The postal code, if any, in which your business is located.', 'wc-easypost' ),
		'id'       => 'wcep_woocommerce_store_postcode',
		'css'      => 'min-width:50px;',
		'default'  => '',
		'type'     => 'text',
		'desc_tip' => true,
	),
	array(
		'title'    => __( 'Phone', 'wc-easypost' ),
		'desc'     => __( 'The official contact number of your business.', 'wc-easypost' ),
		'id'       => 'wcep_woocommerce_store_phone_number',
		'default'  => '',
		'type'     => 'text',
		'desc_tip' => true,
	),
	array(
		'type' => 'sectionend',
		'id'   => 'wcep_source_location_settings_end',
	),
);

/**
 * EasyPost settings filter.
 *
 * Filter to modify general settings for EasyPost Integration. Using this filter, you can add/remove
 * settings fields.
 *
 * @param array $settings_fields Holds the settings fields.
 * @return array
 */
return apply_filters( 'woocommerce_wcep_general_settings', $settings_fields );
