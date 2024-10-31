<?php
/**
 * Dynamic Calculations Field class
 *
 * @package Extra Product Options/Fields
 * @version 6.4.3
 * phpcs:disable PEAR.NamingConventions.ValidClassName
 */

defined( 'ABSPATH' ) || exit;

/**
 * Dynamic Calculations Field class
 *
 * @package Extra Product Options/Fields
 * @version 6.4.3
 */
class THEMECOMPLETE_EPO_FIELDS_dynamic extends THEMECOMPLETE_EPO_FIELDS {

	/**
	 * Display field array
	 *
	 * @param array<mixed> $element The element array.
	 * @param array<mixed> $args Array of arguments.
	 * @return array<mixed>
	 * @since 6.4.3
	 */
	public function display_field( $element = [], $args = [] ) {
		$class_label = '';
		if ( 'yes' === THEMECOMPLETE_EPO_DATA_STORE()->get( 'tm_epo_select_fullwidth' ) ) {
			$class_label = ' fullwidth';
		}

		$operation_mode = $this->get_value( $element, 'mode', '' );

		$hide = $this->get_value( $element, 'hide', '' );

		$hide_amount = '';
		if ( ! empty( $hide ) || 'override_product_price' !== $operation_mode ) {
			$hide_amount = 'hidden';
		}

		$result_label = $this->get_value( $element, 'result_label', '' );

		$operation_mode = $this->get_value( $element, 'mode', '' );
		if ( 'dynamic_product_price' === $operation_mode ) {
			$result_label = '';
		}

		$rules      = isset( $element['rules_filtered'] ) ? wp_json_encode( ( $element['rules_filtered'] ) ) : '';
		$rules_type = isset( $element['rules_type'] ) ? wp_json_encode( ( $element['rules_type'] ) ) : '';

		$display = [
			'textbeforeprice'  => $this->get_value( $element, 'text_before_price', '' ),
			'textafterprice'   => $this->get_value( $element, 'text_after_price', '' ),
			'class_label'      => $class_label,
			'hide_amount'      => $hide_amount,
			'operation_mode'   => $operation_mode,
			'hide'             => $hide,
			'result_label'     => $result_label,
			'formula'          => $rules,
			'calculation_type' => $rules_type,
		];

		if ( 'override_product_price' !== $operation_mode ) {
			$display['rules']          = '';
			$display['original_rules'] = '';
			$display['rules_type']     = '';
		}

		return apply_filters( 'wc_epo_display_field_dynamic', $display, $this, $element, $args );
	}

	/**
	 * Field validation
	 *
	 * @return array<mixed>
	 * @since 6.4.3
	 */
	public function validate() {
		return [
			'passed'  => true,
			'message' => false,
		];
	}

	/**
	 * Function to filter the result data
	 *
	 * @param array<mixed> $ret The data result.
	 * @param string       $type 'normal' or 'fee'.
	 * @return array<mixed>
	 * @since 6.4.3
	 */
	public function result( $ret = [], $type = '' ) {
		$ret['hidelabelincart']  = 'hidden';
		$ret['hidevalueincart']  = 'hidden';
		$ret['hidelabelinorder'] = 'hidden';
		$ret['hidevalueinorder'] = 'hidden';

		return apply_filters( 'wc_epo_save_cart_item_data', $ret, $type, $this );
	}
}
