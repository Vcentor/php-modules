<?php
/**
 * Database expression can be used to add unescaped SQL fragments to a builder object.
 *
 * For example, you can use an expression to generate a column alias:
 *
 *     // SELECT CONCAT(first_name, last_name) AS full_name
 *     $query = DB::select(array(array(DB::expr('CONCAT(first_name, last_name)'), 'full_name')));
 * 
 * @package 	Dao\Mysql\Db\Query;
 * @category 	Query
 * @author 		vcentor
 */

namespace Dao\Mysql\Db\Query;

class Expression {

	// Unquoted parameters
	protected $_parameters;

	// Raw expression string
	protected $_value;

	/**
	 * Sets the expression string.
	 *
	 *    $expression = new Expression('COUNT(users.id)')
	 * @param 	string $value 		raw SQL expression string
	 * @param 	array  $parameters 	unescape parameter values
	 * @return 	void
	 */
	public function __construct($value, $parameters = array()) {
		$this->_value = $value;
		$this->_parameters = $parameters;
	}

	/**
	 * Bind a variable to a parameter.
	 *
	 * @param 	string 	$param 	parameter key to replace
	 * @param 	mixed 	$var 	variable to use
	 * @return 	$this
	 */
	public function bind($param, & $var) {
		$this->_parameters[$param] = & $var;

		return $this;
	}

	/**
	 * Set the value of a parameter.
	 *
	 * @param 	string 	$param 	parameter key to replace
	 * @param 	mixed 	$value 	value to use
	 * @return 	$this
	 */
	public function param($param, $value) {
		$this->_parameters[$param] = $value;
	}

	/**
	 * Add multiple parameter values.
	 *
	 * @param 	array 	$params list of parameter values
	 * @return 	$this
	 */
	public function parameters(array $params) {
		$this->_parameters = $this->_parameters + $params;
		return $this;
	}

	/**
	 * Get the expression value as a string.
	 *
	 * @param  void
	 * @return $this
	 */
	public function value() {
		return (string) $this->_value;
	}

	/**
	 * Return the value of the expression as a string.
	 *
	 * @param 	void
	 * @return 	string
	 */
	public function __toString() {
		return $this->value();
	}

	/**
	 * Compile the SQL expression and return it.
	 *
	 * @param 	mixed 	$db 	Database instance or name of instance
	 * @return 	$this
	 */
	public function compile($db = NULL) {
		if ( ! is_object($db)) {
			$db = Database::instance($db);
		}

		$value = $this->value();

		if ( ! empty($this->_parameters)) {
			$params = array_map(array($db, 'quote'), $this->_parameters);

			$value = strtr($value, $params);
		}

		return $value;
	}
}
