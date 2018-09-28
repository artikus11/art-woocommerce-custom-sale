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
	 * @since 1.0.0
	 * @return string
	 */
	public function sale_callback( $original, $post, $product ) {
		
		$format = get_option( 'awoos_format' );
		
		switch ( $format ) {
			case 'sale':
				$custom_label = get_option( 'awoos_custom_label' );
				if ( ! empty( $custom_label ) ) {
					return $this->out( esc_html( $custom_label ) );
				} else {
					return $original;
				}
				break;
			case 'diff-percent':
				return $this->get_percent( $product );
				break;
			case 'diff-price':
				return $this->get_price_difference( $product );
				break;
			default:
				return $original;
				break;
		}
		
	}
	
	/**
	 * Returns the sale price in html
	 *
	 * @param $format_sale
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function out( $format_sale ) {
		return '<span class="onsale">' . $format_sale . '</span>';
	}
	
	/**
	 * Returns the percentage of the price difference
	 *
	 * @param $product
	 *
	 * @since 1.0.0.
	 * @return string
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
	 * @param      $product
	 * @param bool $percent
	 *
	 * @since 1.0.0
	 * @return bool|float
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
			
			
		} else if ( $product->is_type( 'variable' ) ) {
			
			$available_variations = $product->get_available_variations();
			$maximumper           = 0;
			
			for ( $i = 0; $i < count( $available_variations ); ++ $i ) {
				$variation_id     = $available_variations[ $i ]['variation_id'];
				$variable_product = new WC_Product_Variation( $variation_id );
				
				if ( ! $variable_product->is_on_sale() ) {
					continue;
				}
				
				$r_price = $variable_product->get_regular_price();
				$s_price = $variable_product->get_sale_price();
				if ( false !== $percent ) {
					$percent = round( ( ( floatval( $r_price ) - floatval( $s_price ) ) / floatval( $r_price ) ) * 100 );
				} else {
					$percent = ( ( floatval( $r_price ) - floatval( $s_price ) ) );
				}
				
				if ( $percent > $maximumper ) {
					$maximumper = $percent;
				}
			}
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
	 * @since 1.0.0
	 * @return string
	 */
	public function after_before_label( $value, $option_label, $option_switch ) {
		$label = get_option( $option_label );
		if ( $label ) {
			switch ( get_option( $option_switch ) ) {
				case 'before':
					return esc_html( $label ) . '&nbsp;' . $value;
					break;
				case 'after':
					return $value . '&nbsp;' . esc_html( $label );
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
	 * @since 1.0.0
	 * @return string
	 */
	public function get_price_difference( $product ) {
		$diff  = $this->get_sale( $product, false );
		$value = wc_price( '-' . $diff );
		
		$value = $this->after_before_label( $value, 'awoos_price_label', 'awoos_price_after_before' );
		
		return $this->out( $value );
	}
}