<?php

/**
 * Halp Radio Group Field Class
 *
 * Creates a group of <input type="radio"/> elements using the name="the_field_name[]" attribute
 * to group values into an array upon submission
 */
class Halp_Form_Field_Radio_Group extends Halp_Form_Field {
	
	protected $radios = array();
	protected $values = array();
	protected $start_tab_index = null;
	
	/**
	 * Constructor method for the Halp Radio Group Field object
	 *
	 * @see Halp_Form_Field::_construct() for parameter details
	 */
	public function __construct( $field_name = '', $label = null, $attributes = array(), $options = array(), $wrapper_attributes = array() ) {
		
		$this->default_options['group_before'] = '';
		$this->default_options['group_after'] = '';
		$this->default_options['label_position'] = 'before'; // before or after
		
		// Always call the parent constructor
		parent::__construct( $field_name, $label, $attributes, $options, $wrapper_attributes );
		
		$this->attributes['type'] = 'radio';
		$this->attributes['value'] = null;
		$this->attributes['name'] = $field_name. '[]';
		
	}
	
	/**
	 * Adds a radio to the group
	 * 
	 * @param string $value Radio value
	 * @param string $label Radio textual label
	 */
	public function add_radio( $value = '', $label = '' ) {
		
		array_push( $this->radios, array( 'value' => $value, 'label' => $label ) );
		
	}
	
	/**
	 * Adds radios to the group
	 *
	 * Each associative array in array $radios should follow this format:
	 * array('value'=>'the_value', 'label'=>'The Label')
	 *
	 * @param array $radios Array of radio associative arrays
	 */
	public function add_radios( $radios = array() ) {
		
		foreach( $radios as $radio ) {
			$this->add_radio( $radio['value'], $radio['label'] );
		}
		
	}
	
	/**
	 * Adds radios to the group, overwriting any previously added radios
	 *
	 * @see $this->add_radios() for array format details
	 *
	 * @param array $radios Array of radio associative arrays
	 */
	public function set_radios( $radios = array() ) {
		
		$this->radios = array();
		
		$this->add_radios( $radios );
				
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
			$this->start_tab_index = $last_tab_index + 1;
			$this->form->set_last_tab_index( $last_tab_index + sizeof( $this->radios ) );
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
		
		$current_tab_index = $this->start_tab_index;
		
		$html = $this->options['group_before'];
		
		foreach( $this->radios as $radio ) {
			
			if ( !is_null( $current_tab_index ) ) $this->attributes['tabindex'] = $current_tab_index;
			
			$html .= $this->options['before'];
			
			$html .= 'before' == $this->options['label_position'] ? $this->_render_radio_label( $radio['label'], $radio['value'] ) : '';
			
			$html .= '<input ';
				
			foreach( $this->attributes as $att => $val ) {
				if ( is_null( $val ) ) continue;
				
				if ( 'id' == $att ) $val = $val . '_'. $radio['value'];
				
				$html.= $att .'="'. $val .'" ';
			}
			
			$html .= ' value="'. $radio['value'] .'"'. $this->_checked( $radio['value'] ) .'/>';
			
			$html .= 'after' == $this->options['label_position'] ? $this->_render_radio_label( $radio['label'], $radio['value'] ) : '';
			
			$html .= $this->options['after'];
			
			if ( !is_null( $current_tab_index ) ) $current_tab_index++;
		}
		
		$html .= $this->options['group_after'];
			
		return $html;
	
	}
	
	/**
	 * Checks if the current radio is "checked"
	 *
	 * @param string|null $value Value of the radio in question
	 *
	 * @return string 'checked="checked"' if checked, empty string if not
	 * 
	 * @internal
	 */
	protected function _checked( $value = null ) {
		
		if ( in_array( $value, $this->values ) ) {
			return 'checked="checked"';
		}
		
		return '';
		
	}
	
	/**
	 * Renders a radio label
	 *
	 * Also renders a matching HTML "for" attribute if HTML "id" attribute is
	 * set on the HTML respective <input> element
	 *
	 * @param string $label The textual radio label
	 * @param string $value The value of the radio
	 *
	 * @return string HTML output
	 *
	 * @internal
	 */
	protected function _render_radio_label( $label = '', $value = '' ) {
		
		$html = $this->options['label_before'];
	
		$html .= '<label ';
		
		$html .= !is_null( $this->attributes['id'] ) ? 'for="'. $this->attributes['id'] .'_'. $value .'"' : '';
		
		$html .= '>';
			
		$html .= $this->options['label_before_inner'];
		
		$html .= $label;
		
		$html .= $this->options['label_after_inner'];
		
		$html .= '</label>';
		
		$html .= $this->options['label_after'];
		
		return $html;
		
	}
	
	/**
	 * Renders field HMTL <label> element
	 *
	 * Overridden from Halp_Form_Field. This method outputs nothing for this child class.
	 *
	 * @return string Empty string
	 *
	 * @internal
	 */
	protected function _render_label( $close = false ) {
		
		// Silence is golden
		return '';
		
	}
	
	/**
	 * Populates (checks) radios in group
	 *
	 * @see Halp_Form_Field::populate() for details
	 */
	public function populate( $values = array() ) {
		
		if ( array_key_exists($this->field_name, $values) ) {
			$this->values = $values[$this->field_name];
		}
		
	}
	
}