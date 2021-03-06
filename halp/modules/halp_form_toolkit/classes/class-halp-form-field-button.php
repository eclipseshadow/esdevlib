<?php

/**
 * Halp Button Field Class
 * 
 * Creates an HTML <input type="button"/> element
 */
class Halp_Form_Field_Button extends Halp_Form_Field {
	
	/**
	 * Constructor method for the Halp Button Field object
	 * 
	 * @see Halp_Form_Field::_construct() for parameter details
	 */
	public function __construct( $field_name = '', $label = null, $attributes = array(), $options = array(), $wrapper_attributes = array() ) {
		
		// Always call the parent constructor
		parent::__construct( $field_name, $label, $attributes, $options, $wrapper_attributes );
		
		$this->attributes['type'] = 'button';
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
	
}