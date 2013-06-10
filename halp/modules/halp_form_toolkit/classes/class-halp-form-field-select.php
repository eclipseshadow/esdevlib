<?php

/**
 * Halp Select Field Class
 *
 * Creates an HTML <select> element
 */
class Halp_Form_Field_Select extends Halp_Form_Field {
	
	protected $values = array();
	protected $select_options = array();
	protected $option_groups = array();
	protected $ungrouped_options = array();
	protected $_sort = false;
	protected $sort_order = 'asc';
	
	/**
	 * Constructor method for the Halp Select Field object
	 *
	 * @see Halp_Form_Field::_construct() for parameter details
	 */
	public function __construct( $field_name = '', $label = null, $attributes = array(), $select_options = array(), $wrapper_attributes = array() ) {
		
		// Always call the parent constructor
		parent::__construct( $field_name, $label, $attributes, $select_options, $wrapper_attributes );
		
		$is_multiple = array_key_exists('multiple', $attributes) && 'multiple' == $attributes['multiple'] ? true : false;
		
		$this->attributes['type'] = 'select';
		$this->attributes['name'] = true === $is_multiple ? $this->field_name .'[]' : $this->field_name;
		$this->attributes['value'] = null;
		
		if ( array_key_exists( 'sort', $this->options ) ) {
			$this->sort( $this->options['sort'] );
		}
		
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
	 * Renders HTML output for all checkbox <input> elements & their labels
	 *
	 * @return string HTML output
	 *
	 * @internal
	 */
	protected function _render_form_el() {
		
		$html = $this->options['before'];
	
		$html .= '<select ';
			
		foreach( $this->attributes as $att => $val ) {
			if ( is_null( $val ) ) continue;
			
			$html .= $att .'="'. $val .'" ';
		}
			
		$html .= '>';
		
		// Render Options
		if ( sizeof( $this->option_groups ) > 0 ) {
			
			// Sort optgroups
			
			if ( true === $this->_sort ) {
				if ( 'asc' == $this->sort_order ) {
					ksort( $this->option_groups );
				}
				else {
					krsort( $this->option_groups );
				}
			}
			
			foreach( $this->option_groups as $option_group => $option_labels ) {
				$html .= '<optgroup label="'. $option_group .'">';
				
				// Sort options within group
				
				if ( true === $this->_sort ) {
					if ( 'asc' == $this->sort_order ) {
						ksort( $option_labels );
					}
					else {
						krsort( $option_labels );
					}
				}
				
				foreach( $option_labels as $option_label ) {
					$option = $this->select_options[$option_label];
					
					$html .= $this->_render_select_option( $option );
				}
				
				$html .= '</optgroup>';
			}
			
			if ( true === $this->_sort ) {
				if ( 'asc' == $this->sort_order ) {
					ksort( $this->ungrouped_options );
				}
				else {
					krsort( $this->ungrouped_options );
				}
			}
			
			foreach( $this->ungrouped_options as $option_label ) {
				$option = $this->select_options[$option_label];
					
				$html .= $this->_render_select_option( $option );
			}
		}
		else {
			// Sort options
			
			if ( true === $this->_sort ) {
				if ( 'asc' == $this->sort_order ) {
					ksort( $this->select_options );
				}
				else {
					krsort( $this->select_options );
				}
			}
			
			foreach( $this->select_options as $option_label => $option ) {
				$html .= $this->_render_select_option( $option );
			}
		}
		
		$html .= '</select>';
	
		$html .= $this->options['after'];
			
		return $html;
	
	}
	
	/**
	 * Renders a select <option> element
	 * 
	 * @param array $option Associative array with keys 'value' and 'label'
	 * 
	 * @return string HTML output
	 * 
	 * @internal
	 */
	protected function _render_select_option( $option = array() ) {
		
		$html = '<option value="'. $option['value'] .'" '. $this->_selected( $option['value'] ) .'>'. $option['label'] .'</option>';
		
		return $html;
		
	}
	
	/**
	 * Checks if the current select option is "selected"
	 *
	 * @param string|null $value Value of the select option in question
	 *
	 * @return string 'selected="selected"' if selected, empty string if not
	 * 
	 * @internal
	 */
	protected function _selected( $value = null ) {
		
		if ( in_array( $value, $this->values ) ) {
			return 'selected="selected"';
		}
		
		return '';
		
	}
	
	/**
	 * Populates (selects) select options
	 *
	 * @see Halp_Form_Field::populate() for details
	 */
	public function populate( $values = array() ) {
		
		if ( array_key_exists($this->field_name, $values) ) {
			if ( is_array($values[$this->field_name]) ) {
				$this->values = $values[$this->field_name];
			}
			else {
				// multiple="multiple"
				$this->values = array( $values[$this->field_name] );
			}
		}
	
	}
	
	/**
	 * Adds a select option
	 *
	 * @param string $value Checkbox value
	 * @param string $label Checkbox textual label
	 * @param string|null $option_group Option group the select option should fall in
	 */
	public function add_select_option( $value = '', $label = '', $option_group = null ) {
		
		if ( !is_null( $option_group ) ) {
			if ( !array_key_exists($option_group, $this->option_groups) ) {
				$this->option_groups[$option_group] = array();
			}
			
			array_push( $this->option_groups[$option_group], $label );
		}
		else {
			array_push( $this->ungrouped_options, $label );
		}
		
		if ( !array_key_exists($label, $this->select_options) ) {
			$this->select_options[$label] = array( 'value' => $value, 'label' => $label, 'option_group' => $option_group );
		}
		
	}
	
	/**
	 * Adds select options
	 *
	 * Each associative array in array $select_options should follow this format:
	 * array('value'=>'the_value', 'label'=>'The Label', 'option_group'=>'The Option Group')
	 * - array key 'option_group' is optional
	 *
	 * @param array $select_options Array of select option associative arrays
	 */
	public function add_select_options( $select_options = array() ) {
		
		foreach( $select_options as $option ) {
			if ( !in_array('option_group', $option) ) {
				$option['option_group'] = null;
			}
			$this->add_select_option( $option['value'], $option['label'], $option['option_group'] );
		}
		
	}
	
	/**
	 * Adds select options, overwriting any previously added select options
	 *
	 * @see $this->add_select_option() for array format details
	 *
	 * @param array $select_options Array of select option associative arrays
	 */
	public function set_select_options( $select_options = array() ) {
		
		$this->select_options = array();
		
		$this->add_select_options( $select_options );
		
	}
	
	/**
	 * Alpha Sort select options ASC or DESC by label
	 * 
	 * If there are option groups present, the groups will be sorted first by group name,
	 * then the select options within those groups will be sorted, then any remaining ungrouped
	 * select options will be sorted and fall underneath any option groups
	 * 
	 * @param string $order Sort order (asc|desc)
	 */
	public function sort( $order = 'asc' ) {
		
		if ( !in_array( strtolower($order), array('asc', 'desc') ) ) return;
		
		$this->_sort = true;
		$this->sort_order = strtolower($order);
		
	}
	
}