<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Class AWOOS_Front_End
 *
 * @author Artem Abramovich
 * @since  1.0.0
 */
class AWOOS_Front_End {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_filter( 'woocommerce_sale_flash', array( $this, 'sale_callback' ), 1, 3 );
	}


	/**
	 * The return function of the finished type of sale price
	 *
	 * @param $original
	 * @param $post
	 * @param $product
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function sale_callback( $original, $post, $product ) {

		$format = get_option( 'awoos_format' );

		switch ( $format ) {
			case 'sale':
				$custom_label = get_option( 'awoos_custom_label' );

				if ( ! empty( $custom_label ) ) {
					$original = $this->out( esc_html( $custom_label ) );
				}

				break;
			case 'diff-percent':
				$original = $this->get_percent( $product );
				break;
			case 'diff-price':
				$original = $this->get_price_difference( $product );
				break;

		}

		return $original;

	}


	/**
	 * Returns the sale price in html
	 *
	 * @param $format_sale
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public function out( $format_sale ) {

		return '<span class="onsale">' . $format_sale . '</span>';
	}


	/**
	 * Returns the percentage of the price difference
	 *
	 * @param WC_Product $product
	 *
	 * @return string
	 * @since 1.0.0.
	 */
	public function get_percent( $product ) {

		$percent = $this->get_sale( $product );
		$value   = '-' . esc_html( $percent ) . '%';

		$value = $this->after_before_label( $value, 'awoos_percent_label', 'awoos_percent_after_before' );

		return $this->out( $value );
	}


	/**
	 * Calculating the difference in percentage or difference in prices for simple and variant products
	 *
	 * @param WC_Product $product
	 * @param bool       $percent
	 *
	 * @return bool|float
	 * @since 1.0.0
	 */
	public function get_sale( $product, $percent = true ) {

		if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) ) {

			$r_price = $product->get_regular_price();
			$s_price = $product->get_sale_price();

			if ( false !== $percent ) {
				$percent = round( ( ( floatval( $r_price ) - floatval( $s_price ) ) / floatval( $r_price ) ) * 100 );
			} else {
				$percent = ( ( floatval( $r_price ) - floatval( $s_price ) ) );
			}
		} elseif ( $product->is_type( 'variable' ) ) {

			$variations = $product->get_available_variations();
			$max_sale   = [];

			foreach ( $variations as $key => $variation ) {

				$variable_product = new WC_Product_Variation( $variation['variation_id'] );

				if ( ! $variable_product->is_on_sale() ) {
					continue;
				}

				$r_price = $variable_product->get_regular_price();
				$s_price = $variable_product->get_sale_price();

				$difference_price = ( floatval( $r_price ) - floatval( $s_price ) );
				$percent_price    = round( ( ( floatval( $r_price ) - floatval( $s_price ) ) / floatval( $r_price ) ) * 100 );

				if ( false !== $percent ) {
					$max_sale[] = $percent_price;
				} else {
					$max_sale[] = $difference_price;
				}
			}

			$percent = max( $max_sale );

		}

		return $percent;
	}


	/**
	 * Output of the price with the inscription, before or after the price, depending on the settings
	 *
	 * @param $value
	 * @param $option_label
	 * @param $option_switch
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function after_before_label( $value, $option_label, $option_switch ) {

		$label = get_option( $option_label );

		if ( $label ) {
			switch ( get_option( $option_switch ) ) {
				case 'before':
					$value = esc_html( $label ) . '&nbsp;' . $value;
					break;
				case 'after':
					$value = $value . '&nbsp;' . esc_html( $label );
					break;
			}
		}

		return $value;
	}


	/**
	 * Returns the price difference
	 *
	 * @param $product
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_price_difference( $product ) {

		$diff  = $this->get_sale( $product, false );
		$value = wc_price( '-' . $diff );

		$value = $this->after_before_label( $value, 'awoos_price_label', 'awoos_price_after_before' );

		return $this->out( $value );
	}
}
