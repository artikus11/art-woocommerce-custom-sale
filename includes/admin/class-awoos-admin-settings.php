<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AWOOS_Admin_Settings {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Add settings to the settings array
		add_filter( 'woocommerce_get_sections_products', array( $this, 'add_section' ) );
		add_filter( 'woocommerce_get_settings_products', array( $this, 'add_settings' ), 10, 2 );
	}


	/**
	 * Add section.
	 *
	 * @param $sections
	 *
	 * @return mixed
	 * @since 1.0.0
	 *
	 */
	public function add_section( $sections ) {

		$sections['awoos_sale'] = __( 'Sale Labels', 'art-woocommerce-custom-sale' );

		return $sections;
	}


	/**
	 * Add settings.
	 *
	 * Add setting to the 'WooCommerce' -> 'Settings' -> 'Products' -> 'Sale'
	 * section.
	 *
	 * @param array $settings
	 * @param array $current_section
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function add_settings( $settings, $current_section ) {

		if ( 'awoos_sale' !== $current_section ) {
			return $settings;
		}

		$settings = array();

		$settings[] = array(
			'name' => __( 'Sale Label Settings', 'art-woocommerce-custom-sale' ),
			'type' => 'title',
			'id'   => 'awoos_title',
		);

		$settings[] = array(
			'name'     => __( 'Predefined Format', 'art-woocommerce-custom-sale' ),
			'id'       => 'awoos_format',
			'css'      => 'min-width:350px;',
			'class'    => 'wc-enhanced-select',
			'default'  => 'sale',
			'type'     => 'select',
			'options'  => array(
				'sale'         => __( 'Sale! (use field below)', 'art-woocommerce-custom-sale' ),
				'diff-percent' => __( 'Percent', 'art-woocommerce-custom-sale' ),
				'diff-price'   => __( 'Price difference', 'art-woocommerce-custom-sale' ),
			),
			'desc_tip' => true,
			'desc'     => __( 'Select the desired setting', 'art-woocommerce-custom-sale' ),
		);
		switch ( get_option( 'awoos_format' ) ) {
			case 'sale':
				$settings[] = array(
					'name'     => __( 'Sale Label', 'art-woocommerce-custom-sale' ),
					'id'       => 'awoos_custom_label',
					'type'     => 'text',
					'css'      => 'min-width:350px;',
					'desc'     => __( 'Custom label. If you leave the field blank, the standard label will be used', 'art-woocommerce-custom-sale' ),
					'desc_tip' => true,
				);

				break;

			case 'diff-percent':
				$settings[] = array(
					'name'     => __( 'Label', 'art-woocommerce-custom-sale' ),
					'id'       => 'awoos_percent_label',
					'type'     => 'text',
					'css'      => 'min-width:350px;',
					'desc'     => __( 'Custom title. If necessary, enter the title, coordinates will be displayed before or after interest.', 'art-woocommerce-custom-sale' ),
					'desc_tip' => true,
				);

				$settings[] = array(
					'name'     => __( 'After or before percent', 'art-woocommerce-custom-sale' ),
					'id'       => 'awoos_percent_after_before',
					'css'      => 'min-width:350px;',
					'class'    => 'wc-enhanced-select',
					'default'  => 'sale',
					'type'     => 'select',
					'options'  => array(
						'before' => __( 'Before percent', 'art-woocommerce-custom-sale' ),
						'after'  => __( 'After percent', 'art-woocommerce-custom-sale' ),
					),
					'desc_tip' => true,
					'desc'     => __( 'Select the desired setting', 'art-woocommerce-custom-sale' ),
				);

				break;

			case 'diff-price':
				$settings[] = array(
					'name'     => __( 'Label', 'art-woocommerce-custom-sale' ),
					'id'       => 'awoos_price_label',
					'type'     => 'text',
					'css'      => 'min-width:350px;',
					'desc'     => __( 'Custom title. If necessary, enter the title, coordinates will be displayed before or after interest.', 'art-woocommerce-custom-sale' ),
					'desc_tip' => true,
				);

				$settings[] = array(
					'name'     => __( 'After or before price ', 'art-woocommerce-custom-sale' ),
					'id'       => 'awoos_price_after_before',
					'css'      => 'min-width:350px;',
					'class'    => 'wc-enhanced-select',
					'default'  => 'sale',
					'type'     => 'select',
					'options'  => array(
						'before' => __( 'Before price difference', 'art-woocommerce-custom-sale' ),
						'after'  => __( 'After price difference', 'art-woocommerce-custom-sale' ),
					),
					'desc_tip' => true,
					'desc'     => __( 'Select the desired setting', 'art-woocommerce-custom-sale' ),
				);

				break;
		}

		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'awoos',
		);

		return $settings;

	}
}


