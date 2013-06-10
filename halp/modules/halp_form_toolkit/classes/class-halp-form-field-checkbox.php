<?php

/**
 * Halp Checkbox Field Class
 *
 * Creates an HTML <input type="checkbox"/> element
 * Not to be used to create checkbox groups - ex. name="the_field_name[]"
 * - Use field type: checkbox_group (class Halp_Form_Field_Checkbox_Group) instead
 */
class Halp_Form_Field_Checkbox extends Halp_Form_Field {
	
	/**
	 * Constructor method for the Halp Checkbox Field object
	 * 
	 * @see Halp_Form_Field::_construct() for parameter details
	 */
	public function __construct( $field_name = '', $label = null, $attributes = array(), $options = array(), $wrapper_attributes = array() ) {
		
		// Always call the parent constructor
		parent::__construct( $field_name, $label, $attributes, $options, $wrapper_attributes );
		
		$this->attributes['type'] = 'checkbox';
		$this->attributes['name'] = $this->field_name;
		$this->attributes['value'] = empty($attributes['value']) ? 'on' : $attributes['value']; 
		
	}
	
	/**
	 * Hook called before beginning to render the HTML output
	 * 
	 * Applies Tab Indexes before rendering
	 * 
	 * @see Halp_Form_Field::_pre_render() for details
	 * 
	 * @internal
	 */
	protected function _pre_render() {
		
		parent::_pre_render();
		
		// Tab Index
		if ( $this->form instanceof Halp_Form && true == $this->form->tab_indexes_enabled() ) {
			$last_tab_index = $this->form->get_last_tab_index();
			$current_tab_index = $last_tab_index + 1;
			$this->attributes['tabindex'] = $current_tab_index;
			$this->form->set_last_tab_index( $current_tab_index );
		}
	
	}
	
	/**
	 * Populates (checks) the checkbox
	 *
	 * @see Halp_Form_Field::populate() for details
	 */
	public function populate( $values = array() ) {
		
		if ( array_key_exists($this->field_name, $values) ) {
			$this->attributes['checked'] = 'checked';
		}
		
	}
	
}