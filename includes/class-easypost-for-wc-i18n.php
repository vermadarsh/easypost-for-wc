<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/vermadarsh/
 * @since      1.0.0
 *
 * @package    Easypost_For_Wc
 * @subpackage Easypost_For_Wc/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Easypost_For_Wc
 * @subpackage Easypost_For_Wc/includes
 * @author     Adarsh Verma <adarsh.srmcem@gmail.com>
 */
class Easypost_For_Wc_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'easypost-for-wc',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
