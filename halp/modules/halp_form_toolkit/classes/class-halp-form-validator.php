<?php

/**
 * Halp Form Validator Class
 * 
 * Simplifies the task of validating submitted form values by using function/method
 * pointers to easily add rules to specified field names and allowing for auto-population
 * of an array of respective errors
 */
class Halp_Form_Validator {
	
	/** @type array Associative array of added rules (associative arrays), each containing function pointers, error messages & optional data */
	protected $rules = array();
	
	/** @type array Associative array of field values - Same format as $_POST or $_GET */
	protected $fields = array();
	
	/** @type array Associative array of error messages for each field in $this->fields */
	protected $errors = array();
	
	/** @type string Default error message */
	protected static $default_error_msg = 'Please correct this field';
	
	/**
	 * Cosntructor for Halp Form Validator object
	 * 
	 * @param array $request Associative array of field values (format ex. $_POST|$_GET)
	 */
	public function __construct( $request = array() ) {
		
		$this->fields = $request;
		
	}
	
	/**
	 * Adds a validation rule for a specified field name
	 * 
	 * Param: $rule also takes any string method names of pre-packaged validator methods
	 * - Available pre-packaged validator methods are:
	 * -- (is_not_empty|is_equal_to|is_exactly_equal_to|is_identical_to|is_valid_email|matches_pattern)
	 * 
	 * @param string $field_name
	 * @param string|array $rule String function/method name or array('Class_Name', 'method_name')
	 * @param string|null $error_msg Error message or null to use defaults
	 * @param mixed|null $data Mixed data of any kind you'd like to pass to the validator function/method
	 * 						   - See individual validator methods for expected $data values
	 */
	public function add_rule( $field_name = '', $rule = '', $error_msg = null, $data = null ) {
		
		$rule_not_found = false;
		$rule_pointer = null;
		
		if ( is_string( $rule ) ) {
			if ( method_exists( 'Halp_Form_Validator', $rule ) ) {
				$rule_pointer = array( 'Halp_Form_Validator', $rule );
			} else if ( function_exists($rule) ) {
				$rule_pointer = $rule;
			}
			else {
				$rule_not_found = true;
			}
		}
		else if ( is_array( $rule ) ) {
			if ( method_exists( $rule[0], $rule[1] ) ) {
				$rule_pointer = $rule;
			}
			else {
				$rule_not_found = true;
			}
		}
		
		if ( $rule_not_found ) {
			// Possibly Throw Error
			return false;
		}
		
		// It's a valid rule, go ahead and add it to the rules array
		if ( !array_key_exists($field_name, $this->rules) ) {
			$this->rules[$field_name] = array();
		}
		
		array_push( $this->rules[$field_name], array( 'pointer' => $rule_pointer, 'error_msg' => $error_msg, 'data' => $data ) );
		
	}
	
	/**
	 * Adds an error to $this->errors for a given field name
	 * 
	 * @param string $field_name
	 * @param array $rule Current rule as an associative array (same format as stored in $this->rules)
	 * @param string|null $method_error_msg String error message or null to use defaults
	 * 
	 * @internal
	 */
	protected function _add_error( $field_name = '', $rule, $method_error_msg = null ) {
		
		$error_msg = $rule['error_msg'];
		
		if ( is_null( $error_msg ) ) $error_msg = $method_error_msg;
		
		if ( is_null( $error_msg ) ) $error_msg = self::$default_error_msg;
		
		if ( !array_key_exists($field_name, $this->errors) ) {
			$this->errors[$field_name] = array();
		}
		
		array_push( $this->errors[$field_name], $error_msg );
		
	}
	
	/**
	 * Retrieves $this->errors
	 * 
	 * @return array Associative array of error messages--keys matching field names
	 */
	public function get_errors() {
		
		return $this->errors;
		
	}
	
	/**
	 * Validates all fields against any respective added rules
	 * 
	 * @return bool Whether or not there are any errors (true if there are, false if not)
	 */
	public function validate() {
		
		foreach( $this->rules as $field_name => $rules ) {
			foreach( $rules as $rule ) {
				$result = call_user_func( $rule['pointer'], $this->fields, $field_name, $rule['data']);
					
				if ( false === $result ) {
					$this->_add_error( $field_name, $rule );
				}
				else if ( is_string( $result ) ) {
					$error_msg = $result;
					
					$this->_add_error( $field_name, $rule, $error_msg );
				}
			}
		}
		
		return sizeof( $this->errors ) < 1;
		
	}
	
	/**
	 * Checkes to see if a field was left blank
	 * 
	 * @uses empty() to check for empty strings, empty arrays, null values, etc
	 * 
	 * @return bool True if field is "not" empty
	 */
	public static function is_not_empty( $values = array(), $field_name = '', $data = null ) {
		
		$field_value = null;
		if ( array_key_exists($field_name, $values) ) {
			$field_value = $values[$field_name];
		}
		
		return empty( $field_value ) ? 'This field cannot be empty' : true;
		
	}
	
	/**
	 * Compares field value to given value $data using ==
	 * 
	 * @param array $values $this->fields
	 * @param string $field_name
	 * @param mixed $data Expected value is whatever the field values is to be compared to
	 * 
	 * @return bool|string True if values are equal, string error message if not
	 */
	public static function is_equal_to( $values = array(), $field_name = '', $data = null ) {
	
		$field_value = null;
		if ( array_key_exists($field_name, $values) ) {
			$field_value = $values[$field_name];
		}
		
		$equal_to = $data;
		
		$result = $field_value == $equal_to;
		
		if ( is_string($data) || is_numeric($data) ) {
			return $result ? true : 'This field must be equal to '. $data;
		}
		else {
			return $result;
		}
		
	}
	
	/**
	 * Compares field value to given value $data using ===
	 * 
	 * @param array $values $this->fields
	 * @param string $field_name
	 * @param mixed $data Expected value is whatever the field values is to be compared to
	 * 
	 * @return bool|string True if values are exactly equal, string error message if not
	 */
	public static function is_exactly_equal_to( $values = array(), $field_name = '', $data = null ) {
	
		$field_value = null;
		if ( array_key_exists($field_name, $values) ) {
			$field_value = $values[$field_name];
		}
	
		$equal_to = $data;
	
		$result = $field_value === $equal_to;
		
		if ( is_string($data) || is_numeric($data) ) {
			return $result ? true : 'This field must be equal to '. $data;
		}
		else {
			return $result;
		}
	
	}
	
	/**
	 * Checks to see if a field's value is identical to another field's value
	 * 
	 * @param array $values $this->fields
	 * @param string $field_name
	 * @param string $data Expected value is the "other" string field name
	 * 
	 * @return bool|string True if values are identical, string error message if not
	 */
	public static function is_identical_to( $values = array(), $field_name = '', $data = null ) {
		
		$error_msg = 'This field must be equal to '. $data;
		
		$field_value = null;
		if ( array_key_exists($field_name, $values) ) {
			$field_value = $values[$field_name];
		}
		
		$other_field_name = $data;
		$other_field_value = null;
		if ( array_key_exists($other_field_name, $values) ) {
			$other_field_value = $values[$other_field_name];
		}
		var_dump($field_value, $other_field_value);
		if ( is_array( $field_value ) ) {
			if ( !is_array($other_field_value) ) return $error_msg;
				
			// Array Compare
			$diff = array_diff($field_value, $other_field_value);
			$rdiff = array_diff($other_field_value, $field_value);
				
			return sizeof($diff) < 1 && sizeof($rdiff) < 1;
		}
		else if ( is_string( $field_value ) ) {
			if ( !is_string( $other_field_value ) ) return $error_msg;
				
			// String Compare
			return trim( $field_value ) == trim( $other_field_value );
		}
	
		return $error_msg;
	
	}
	
	/**
	 * Checks to see if field is a valid email
	 * 
	 * @param array $values $this->fields
	 * @param string $field_name
	 * @param null $data Not used
	 * 
	 * @return bool|string True if values are identical, string error message if not
	 */
	public static function is_valid_email( $values = array(), $field_name = '', $data = null ) {
		
		$email = null;
		if ( array_key_exists($field_name, $values) ) {
			$email = $values[$field_name];
		}
		
		if ( empty( $email ) ) {
			return 'Email must not be blank';
		}
		
		// Test for the minimum length the email can be
		if ( strlen( $email ) < 3 ) {
			return 'Email is too short';
		}
	
		// Test for an @ character after the first position
		if ( strpos( $email, '@', 1 ) === false ) {
			return 'Email has no @ symbol';
		}
	
		// Split out the local and domain parts
		list( $local, $domain ) = explode( '@', $email, 2 );
	
		// LOCAL PART
		// Test for invalid characters
		if ( !preg_match( '/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+$/', $local ) ) {
			return 'This is not a valid email';
		}
	
		// DOMAIN PART
		// Test for sequences of periods
		if ( preg_match( '/\.{2,}/', $domain ) ) {
			return 'This is not a valid email';
		}
	
		// Test for leading and trailing periods and whitespace
		if ( trim( $domain, " \t\n\r\0\x0B." ) !== $domain ) {
			return 'This is not a valid email';
		}
	
		// Split the domain into subs
		$subs = explode( '.', $domain );
	
		// Assume the domain will have at least two subs
		if ( 2 > count( $subs ) ) {
			return 'This is not a valid email';
		}
	
		// Loop through each sub
		foreach ( $subs as $sub ) {
			// Test for leading and trailing hyphens and whitespace
			if ( trim( $sub, " \t\n\r\0\x0B-" ) !== $sub ) {
				return 'This is not a valid email';
			}
	
			// Test for invalid characters
			if ( !preg_match('/^[a-z0-9-]+$/i', $sub ) ) {
				return 'This is not a valid email';
			}
		}
	
		// Congratulations your email made it!
		return true;
		
	}
	
	/**
	 * Checks to see if field value matches a given pattern
	 * 
	 * @param array $values $this->fields
	 * @param string $field_name
	 * @param string $data Pattern to test against
	 * 
	 * @return bool True if field value matches given pattern
	 */
	public static function matches_pattern( $values = array(), $field_name = '', $data = null ) {
		
		$field_value = null;
		if ( array_key_exists($field_name, $values) ) {
			$field_value = $values[$field_name];
		}
		
		if ( !is_string( $field_value ) ) return false;
		
		$pattern = $data;
		
		return (bool) preg_match($pattern, $field_value);
		
	}
	
}