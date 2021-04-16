<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Easypost_For_Wc
 * @subpackage Easypost_For_Wc/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Easypost_For_Wc
 * @subpackage Easypost_For_Wc/includes
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class Easypost_For_Wc {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Easypost_For_Wc_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WCEP_PLUGIN_VERSION' ) ) {
			$this->version = WCEP_PLUGIN_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'easypost-for-wc';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Easypost_For_Wc_Loader. Orchestrates the hooks of the plugin.
	 * - Easypost_For_Wc_i18n. Defines internationalization functionality.
	 * - Easypost_For_Wc_Admin. Defines all hooks for the admin area.
	 * - Easypost_For_Wc_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		// The class responsible for orchestrating the actions and filters of the core plugin.
		require_once WCEP_PLUGIN_PATH . 'includes/class-easypost-for-wc-loader.php';

		// The class responsible for defining internationalization functionality of the plugin.
		require_once WCEP_PLUGIN_PATH . 'includes/class-easypost-for-wc-i18n.php';

		// The class responsible for defining all actions that occur in the admin area.
		require_once WCEP_PLUGIN_PATH . 'admin/class-easypost-for-wc-admin.php';

		// The file responsible for defining all custom functions for admin/public areas.
		require_once WCEP_PLUGIN_PATH . 'includes/easypost-for-wc-functions.php';

		// The class responsible for defining all actions that occur in the public-facing side of the site.
		require_once WCEP_PLUGIN_PATH . 'public/class-easypost-for-wc-public.php';

		$this->loader = new Easypost_For_Wc_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Easypost_For_Wc_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Easypost_For_Wc_I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Easypost_For_Wc_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'wcep_admin_enqueue_scripts_callback' );
		$this->loader->add_filter( 'woocommerce_get_sections_shipping', $plugin_admin, 'wcep_woocommerce_get_sections_shipping_callback' );
		$this->loader->add_filter( 'woocommerce_get_settings_shipping', $plugin_admin, 'wcep_woocommerce_get_settings_shipping_callback', 10, 2 );

		// Return if the EasyPost is not enabled.
		if ( ! is_wcep_enabled() ) {
			return;
		}

		$this->loader->add_filter( 'woocommerce_shipping_methods', $plugin_admin, 'wcep_woocommerce_shipping_methods_callback' );
		$this->loader->add_action( 'woocommerce_shipping_init', $plugin_admin, 'wcep_woocommerce_shipping_init_callback' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'wcep_add_meta_boxes_callback' );
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'wcep_admin_footer_callback' );
		$this->loader->add_action( 'wp_ajax_save_tracking_info', $plugin_admin, 'wcep_save_tracking_info_callback' );
		$this->loader->add_action( 'wp_ajax_generate_shipping_label', $plugin_admin, 'wcep_generate_shipping_label_callback' );
		$this->loader->add_action( 'wp_ajax_generate_postage_label_preview', $plugin_admin, 'wcep_generate_postage_label_preview_callback' );
		$this->loader->add_action( 'wp_ajax_email_label', $plugin_admin, 'wcep_email_label_callback' );
		$this->loader->add_action( 'woocommerce_order_status_completed', $plugin_admin, 'wcep_woocommerce_order_status_completed_callback' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		// Return if the EasyPost is not enabled.
		if ( ! is_wcep_enabled() ) {
			return;
		}

		$plugin_public = new Easypost_For_Wc_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'wcep_wp_enqueue_scripts_callback' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'wcep_wp_footer_callback' );
		$this->loader->add_filter( 'woocommerce_cart_totals_fee_html', $plugin_public, 'wcep_woocommerce_cart_totals_fee_html_callback' );
		$this->loader->add_action( 'woocommerce_checkout_order_processed', $plugin_public, 'wcep_woocommerce_checkout_order_processed_callback', 10, 2 );
		$this->loader->add_action( 'wp_ajax_insure_shipment', $plugin_public, 'wcep_insure_shipment_callback' );
		$this->loader->add_action( 'wp_ajax_nopriv_insure_shipment', $plugin_public, 'wcep_insure_shipment_callback' );
		$this->loader->add_action( 'woocommerce_cart_calculate_fees', $plugin_public, 'wcep_woocommerce_cart_calculate_fees_callback', 20 );
		$this->loader->add_action( 'woocommerce_after_shipping_rate', $plugin_public, 'wcep_woocommerce_after_shipping_rate_callback', 20 );
		$this->loader->add_filter( 'woocommerce_checkout_posted_data', $plugin_public, 'wcep_woocommerce_checkout_posted_data_callback', 20 );
		$this->loader->add_action( 'woocommerce_checkout_process', $plugin_public, 'wcep_woocommerce_checkout_process_callback' );
		$this->loader->add_action( 'woocommerce_order_details_after_order_table', $plugin_public, 'wcep_woocommerce_order_details_after_order_table_callback' );
		$this->loader->add_action( 'init', $plugin_public, 'wcep_init_callback' );
		$this->loader->add_filter( 'wc_order_statuses', $plugin_public, 'wcep_wc_order_statuses_callback' );
		$this->loader->add_action( 'wp_ajax_pickup_form_fields', $plugin_public, 'wcep_pickup_form_fields_callback' );
		$this->loader->add_action( 'wp_ajax_create_pickup', $plugin_public, 'wcep_create_pickup_callback' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Easypost_For_Wc_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
