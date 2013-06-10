<?php

/**
 * Halp Hidden Field Class
 *
 * Creates an HTML <input type="hidden"/> element
 */
class Halp_Form_Field_Hidden extends Halp_Form_Field {
	
	/**
	 * Constructor method for the Halp Hidden Field object
	 * 
	 * @see Halp_Form_Field::_construct() for parameter details
	 */
	public function __construct( $field_name = '', $label = null, $attributes = array(), $options = array(), $wrapper_attributes = array() ) {
	
		// Always call the parent constructor
		parent::__construct( $field_name, $label, $attributes, $options, $wrapper_attributes );
	
		$this->attributes['type'] = 'hidden';
		$this->attributes['name'] = $this->field_name;
	
	}
	
	/**
	 * Populates the field value
	 *
	 * @see Halp_Form_Field::populate() for details
	 */
	public function populate( $values = array() ) {
	
		if ( array_key_exists($this->field_name, $values) ) {
			$this->attributes['value'] = $values[$this->field_name];
		}
	
	}
	
}