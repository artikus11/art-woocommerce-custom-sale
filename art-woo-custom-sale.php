<?php
/**
 * Plugin Name: Art Woocommerce Custom Sale
 * Plugin URI: https://wpruse.ru/my-plugins/art-woocommerce-custom-sale/
 * Text Domain: art-woocommerce-custom-sale
 * Domain Path: /languages
 * Description: Customize the sale tag that appears on WooCommerce product thumbnails when a product sale price is set lower than the retail price.
 * Version:           1.1.0
 * Author:            Artem Abramovich
 * Author URI:        https://wpruse.ru/
 * License:           GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt Text Domain: Domain Path:
 * Tags:
 *
 * WC requires at least: 3.3.0
 * WC tested up to: 3.6
 *
 *
 * Copyright Artem Abramovich
 *
 *     This file is part of Art Woocommerce Custom Sale,
 *     a plugin for WordPress.
 *
 *     Art Woocommerce Custom Sale is free software:
 *     You can redistribute it and/or modify it under the terms of the
 *     GNU General Public License as published by the Free Software
 *     Foundation, either version 3 of the License, or (at your option)
 *     any later version.
 *
 *     Art Woocommerce Custom Sale is distributed in the hope that
 *     it will be useful, but WITHOUT ANY WARRANTY; without even the
 *     implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 *     PURPOSE. See the GNU General Public License for more details.
 *
 *     You should have received a copy of the GNU General Public License
 *     along with WordPress. If not, see <http://www.gnu.org/licenses/>.
 *
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

register_uninstall_hook( __FILE__, array( 'AWOOS_Custom_Sale', 'uninstall' ) );

/**
 * Class AWOOS_Custom_Sale
 *
 * Main AWOOS class, initialized the plugin
 *
 * @class       AWOOS_Custom_Sale
 * @version     1.0.0
 * @author      Artem Abramovich
 */
class AWOOS_Custom_Sale {

	/**
	 * Instance of AWOOS_Custom_Sale.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var object $instance The instance of AWOOS_Custom_Sale.
	 */
	private static $instance;

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var string $version Plugin version number.
	 */
	public $version;

	/**
	 * Plugin name.
	 *
	 * @since 1.0.0
	 * @var string $name Plugin name.
	 */
	public $name;

	/**
	 * Object settings.
	 *
	 * @since 1.0.0
	 * @var string $admin_settings
	 */
	public $admin_settings;

	/**
	 * Object Front end.
	 *
	 * @since 1.0.0
	 * @var string $admin_settings
	 */
	public $front_end;


	/**
	 * Construct.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->version = $this->get_plugin_data()['ver'];
		$this->name    = $this->get_plugin_data()['name'];

		// Check if WooCommerce is active
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && ! function_exists( 'WC' ) ) {
			return;
		}

		$this->init();

	}


	/**
	 * Get the name and version of the plugin
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_plugin_data() {

		return get_file_data(
			__FILE__,
			array(
				'ver'  => 'Version',
				'name' => 'Plugin Name',
			)
		);
	}


	/**
	 * Init.
	 *
	 * Initialize plugin parts.
	 *
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( version_compare( PHP_VERSION, '5.6', 'lt' ) ) {
			add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
		}

		if ( is_admin() ) {
			/**
			 * Settings
			 */
			require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-awoos-admin-settings.php';
			$this->admin_settings = new AWOOS_Admin_Settings();

		}

		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			/**
			 * Front end
			 */
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-awoos-front-end.php';
			$this->front_end = new AWOOS_Front_End();
		}

		// Plugin update function
		add_action( 'admin_init', array( $this, 'plugin_update' ) );

		// Load textdomain
		$this->load_textdomain();

		global $pagenow;
		if ( 'plugins.php' === $pagenow ) {
			// Plugins page
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ), 10, 2 );
		}

	}


	/**
	 * Textdomain.
	 *
	 * Load the textdomain based on WP language.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {

		$locale = apply_filters( 'plugin_locale', get_locale(), 'art-woocommerce-custom-sale' );

		// Load textdomain
		load_textdomain( 'art-woocommerce-custom-sale', WP_LANG_DIR . '/art-woocommerce-custom-sale/art-woocommerce-custom-sale-' . $locale . '.mo' );
		load_plugin_textdomain( 'art-woocommerce-custom-sale', false, basename( dirname( __FILE__ ) ) . '/languages' );

	}


	/**
	 * Instance.
	 *
	 * An global instance of the class. Used to retrieve the instance
	 * to use on other files/plugins/themes.
	 *
	 * @return object Instance of the class.
	 * @since 1.0.0
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) :
			self::$instance = new self();
		endif;

		return self::$instance;

	}


	/**
	 * Update plugin.
	 *
	 * Plugin update function, update data when required.
	 *
	 * @since 1.0.0
	 */
	public function plugin_update() {

		$settings = get_option( 'awoos_format' );
		update_option( 'awoos_format', isset( $settings ) ? $settings : 'sale' );
	}


	/**
	 * Plugin action links.
	 *
	 * Add links to the plugins.php page below the plugin name
	 * and besides the 'activate', 'edit', 'delete' action links.
	 *
	 * @param array  $links List of existing links.
	 * @param string $file  Name of the current plugin being looped.
	 *
	 * @return    array            List of modified links.
	 * @since 1.0.0
	 *
	 */
	public function add_plugin_action_links( $links, $file ) {

		if ( plugin_basename( __FILE__ ) === $file ) :
			$links = array_merge(
				array(
					'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=products&section=awoos_sale' ) ) . '">' . __( 'Settings', 'art-woocommerce-custom-sale' ) .
					'</a>',
				),
				$links
			);
		endif;

		return $links;

	}


	/**
	 * Display PHP 5.6 required notice.
	 *
	 * Display a notice when the required PHP version is not met.
	 *
	 * @since 1.0.0
	 */
	public function php_version_notice() {

		?>
		<div class="notice notice-error">

			<p>
				<?php

				printf(
					/* translators: 1: Name plugins, 2:PHP version */
					esc_html__( '%1$s requires PHP 5.6 or higher and your current PHP version is %2$s. Please (contact your host to) update your PHP version.', 'art-woocommerce-custom-sale' ),
					esc_html( $this->name ),
					PHP_VERSION
				);
				?>
			</p>
		</div>
		<?php

	}


	/**
	 * Deleting settings when uninstalling the plugin
	 *
	 * @since 1.0.0
	 */
	public function uninstall() {

		delete_option( 'awoos_format' );
		delete_option( 'awoos_custom_label' );
		delete_option( 'awoos_percent_label' );
		delete_option( 'awoos_percent_after_before' );
		delete_option( 'awoos_price_label' );
		delete_option( 'awoos_price_after_before' );
	}

}

/**
 * The main function responsible for returning the AWOOS_Custom_Sale object.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * Example: <?php awoos_custom_sale()->method_name(); ?>
 *
 * @return object AWOOS_Custom_Sale class object.
 * @since 1.0.0
 *
 */
if ( ! function_exists( 'awoos_custom_sale' ) ) :

	function awoos_custom_sale() {

		return AWOOS_Custom_Sale::instance();
	}

endif;

awoos_custom_sale();

// Backwards compatibility
$GLOBALS['awoos'] = awoos_custom_sale();
