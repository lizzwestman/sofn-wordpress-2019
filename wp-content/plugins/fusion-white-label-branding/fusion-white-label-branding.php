<?php
/**
 * Plugin Name: Fusion White Label Branding
 * Plugin URI: http://www.theme-fusion.com
 * Description: White Label Branding plugin for ThemeFusion Products.
 * Version: 1.0.1
 * Author: ThemeFusion
 * Author URI: http://www.theme-fusion.com
 *
 * @package Fusion-White-Label-Branding
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin version.
if ( ! defined( 'FUSION_WHITE_LABEL_BRANDING_VERSION' ) ) {
	define( 'FUSION_WHITE_LABEL_BRANDING_VERSION', '1.0.1' );
}
// Plugin Folder Path.
if ( ! defined( 'FUSION_WHITE_LABEL_BRANDING_PLUGIN_DIR' ) ) {
	define( 'FUSION_WHITE_LABEL_BRANDING_PLUGIN_DIR', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
}
// Plugin Folder URL.
if ( ! defined( 'FUSION_WHITE_LABEL_BRANDING_PLUGIN_URL' ) ) {
	define( 'FUSION_WHITE_LABEL_BRANDING_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
// Plugin Root File.
if ( ! defined( 'FUSION_WHITE_LABEL_BRANDING_PLUGIN_FILE' ) ) {
	define( 'FUSION_WHITE_LABEL_BRANDING_PLUGIN_FILE', __FILE__ );
}

register_activation_hook( __FILE__, array( 'Fusion_White_Label_Branding', 'activation' ) );

if ( ! class_exists( 'Fusion_White_Label_Branding' ) ) {

	/**
	 * Main Fusion_White_Label_Branding Class.
	 *
	 * @since 1.0
	 */
	class Fusion_White_Label_Branding {

		/**
		 * The one, true instance of this object.
		 *
		 * @since 1.0
		 * @static
		 * @access private
		 * @var object
		 */
		private static $instance;

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @since 1.0
		 * @static
		 * @access public
		 */
		public static function get_instance() {

			// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
			if ( null === self::$instance ) {
				self::$instance = new Fusion_White_Label_Branding();
			}
			return self::$instance;
		}

		/**
		 * Initializes the plugin by setting localization, hooks, filters,
		 * and administrative functions.
		 *
		 * @since 1.0
		 * @access private
		 */
		private function __construct() {

			// Include required files.
			$this->includes();

			// Load plugin textdomain.
			$this->textdomain();
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private function includes() {
			require_once FUSION_WHITE_LABEL_BRANDING_PLUGIN_DIR . 'inc/fusion-branding-admin.php';
		}

		/**
		 * Run on plugin activation. Process options migration etc.
		 *
		 * @access private
		 * @since 2.0
		 * @return void
		 */
		public static function activation() {
			$settings               = get_option( 'fusion_branding_settings', array() );
			$fusion_slider_options  = isset( $settings['fusion_branding']['fusion_slider'] ) ? $settings['fusion_branding']['fusion_slider'] : array();
			$avada_options          = isset( $settings['fusion_branding']['avada'] ) ? $settings['fusion_branding']['avada'] : array();
			$option_update_required = false;

			// Do settings migration for Fusion Slider Admin Menu Icon.
			if ( ( ! isset( $fusion_slider_options['fusion_slider_icon_image'] ) || '' === $fusion_slider_options['fusion_slider_icon_image'] ) && isset( $avada_options['fusion_slider_icon_image'] ) && '' !== $avada_options['fusion_slider_icon_image'] ) {
				$fusion_slider_icon_image                          = ( isset( $avada_options['fusion_slider_icon_image'] ) && '' !== $avada_options['fusion_slider_icon_image'] ) ? $avada_options['fusion_slider_icon_image'] : '';
				$fusion_slider_options['fusion_slider_icon_image'] = $fusion_slider_icon_image;

				// Update value from Avada to Fusion Slider settings.
				$settings['fusion_branding']['fusion_slider']['fusion_slider_icon_image'] = $fusion_slider_icon_image;
				$settings['fusion_branding']['avada']['fusion_slider_icon_image']         = '';
				$option_update_required = true;
			}

			// Do settings migration for Fusion Slider Admin Menu Label.
			if ( ( ! isset( $fusion_slider_options['admin_menu_label'] ) || '' === $fusion_slider_options['admin_menu_label'] ) && isset( $avada_options['rename_admin_menu']['slider'] ) && '' !== $avada_options['rename_admin_menu']['slider'] ) {
				$admin_menu_label                          = ( isset( $avada_options['rename_admin_menu']['slider'] ) && '' !== $avada_options['rename_admin_menu']['slider'] ) ? $avada_options['rename_admin_menu']['slider'] : '';
				$fusion_slider_options['admin_menu_label'] = $admin_menu_label;

				// Update value from Avada to Fusion Slider settings.
				$settings['fusion_branding']['fusion_slider']['admin_menu_label']    = $admin_menu_label;
				$settings['fusion_branding']['avada']['rename_admin_menu']['slider'] = '';
				$option_update_required = true;
			}

			if ( $option_update_required ) {
				update_option( 'fusion_branding_settings', $settings );
			}
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since 1.0.1
		 * @return void
		 */
		public function textdomain() {
			// Set text domain.
			$domain = 'fusion-white-label-branding';

			// Load textdomain for plugin.
			load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
	}
} // End if().

/**
 * Instantiate Fusion_White_Label_Branding class.
 *
 * @since 1.0
 * @return void
 */
function fusion_white_label_branding_activate() {
	Fusion_White_Label_Branding::get_instance();
}
add_action( 'after_setup_theme', 'fusion_white_label_branding_activate', 11 );
