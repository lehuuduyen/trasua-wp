<?php
/**
 * Textfield Element
 *
 * @package Extra Product Options/Classes/Builder
 * @version 6.4
 */

defined( 'ABSPATH' ) || exit;

/**
 * Textfield Element
 *
 * @package Extra Product Options/Classes/Builder
 * @version 6.4
 */
class THEMECOMPLETE_EPO_BUILDER_ELEMENT_TEXTFIELD extends THEMECOMPLETE_EPO_BUILDER_ELEMENT {

	/**
	 * Class Constructor
	 *
	 * @param string $name The element name.
	 * @since 6.0
	 */
	public function __construct( $name = '' ) {
		$this->element_name     = $name;
		$this->is_addon         = false;
		$this->namespace        = $this->elements_namespace;
		$this->name             = esc_html__( 'Text Field', 'woocommerce-tm-extra-product-options' );
		$this->description      = '';
		$this->width            = 'w100';
		$this->width_display    = '100%';
		$this->icon             = 'tcfa-i-cursor';
		$this->is_post          = 'post';
		$this->type             = 'single';
		$this->post_name_prefix = 'textfield';
		$this->fee_type         = 'single';
		$this->tags             = 'price content';
		$this->show_on_backend  = true;
	}

	/**
	 * Initialize element properties
	 *
	 * @since 6.0
	 * @return void
	 */
	public function set_properties() {
		$this->properties = $this->add_element(
			$this->element_name,
			[
				'enabled',
				'required',
				'price_type2',
				'lookuptable',
				'freechars',
				'price',
				'sale_price',
				'fee',
				'hide_amount',
				'text_before_price',
				'text_after_price',
				'quantity',
				'placeholder',
				'min_chars',
				'max_chars',
				'default_value',
				'min',
				'max',
				[
					'rangestep',
					[
						'desc'     => esc_html__( 'Enter the step.', 'woocommerce-tm-extra-product-options' ),
						'required' => [
							'.tc-validateas' => [
								'operator' => 'is',
								'value'    => 'number',
							],
						],
					],
				],
				'validation1',
			]
		);
	}
}
