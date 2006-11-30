<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Swat/SwatObject.php';
require_once 'Swat/SwatContainer.php';
require_once 'Swat/SwatCellRendererMapping.php';
require_once 'Swat/SwatDate.php';

require_once 'Swat/exceptions/SwatFileNotFoundException.php';
require_once 'Swat/exceptions/SwatInvalidSwatMLException.php';
require_once 'Swat/exceptions/SwatWidgetNotFoundException.php';
require_once 'Swat/exceptions/SwatInvalidCallbackException.php';
require_once 'Swat/exceptions/SwatDuplicateIdException.php';
require_once 'Swat/exceptions/SwatInvalidClassException.php';
require_once 'Swat/exceptions/SwatDoesNotImplementException.php';
require_once 'Swat/exceptions/SwatClassNotFoundException.php';
require_once 'Swat/exceptions/SwatInvalidPropertyException.php';
require_once 'Swat/exceptions/SwatInvalidPropertyTypeException.php';
require_once 'Swat/exceptions/SwatUndefinedConstantException.php';
require_once 'Swat/exceptions/SwatInvalidConstantExpressionException.php';

/**
 * Generates a Swat widget tree from an XML UI file
 *
 * @package   Swat
 * @copyright 2004-2006 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatUI extends SwatObject
{
	// {{{ protected properties

	/**
	 * An array that maps other package classes to filenames
	 *
	 * The array is of the form:
	 *    package_prefix => path
	 * Where package prefix is the classname prefix used in this package and 
	 * path is the relative path where the source files for this package may 
	 * be included from.
	 *
	 * @var array
	 */
	protected $class_map = array('Swat' => 'Swat');

	// }}}
	// {{{ private properties

	/**
	 * An array of widgets populated when a UI file is parsed
	 *
	 * This array is used internally. The array is of the form:
	 *    id => object reference
	 * Where id is the unique identifier of the widget.
	 *
	 * @var array
	 */
	private $widgets = array();

	/**
	 * The root container of the widget tree created by this UI
	 *
	 * @var SwatContainer
	 */
	private $root = null;

	/**
	 * A stack of references to ancestors of the object currently being parsed.
	 *
	 * @var array
	 */
	private $stack = array();

	private $translation_callback = null;

	/**
	 * An array of document validation errors used by the custom error handler
	 * for loading XML documents
	 *
	 * @var array
	 */
	private static $validation_errors = array();

	// }}}
	// {{{ public function __construct()

	/**
	 * Creates a new UI
	 *
	 * @param SwatContainer $container an optional reference to a container
	 *                                  object that will be the root element of
	 *                                  the widget tree.
	 */
	public function __construct($container = null)
	{
		if ($container !== null && $container instanceof SwatContainer)
			$this->root = $container;
		else
			$this->root = new SwatContainer();
	}

	// }}}
	// {{{ public function mapClassPrefixToPath()

	/**
	 * Maps a class prefix to a path for filename lookup in this UI
	 *
	 * The class path map is used to find required files for widget classes
	 * specified in XML.
	 *
	 * @param string $class_prefix the prefix of the class to map to the given
	 *                              path.
	 * @param string $path the path to map the given class prefix to.
	 */
	public function mapClassPrefixToPath($class_prefix, $path)
	{
		$this->class_map[$class_prefix] = $path;
	}

	// }}}
	// {{{ public function loadFromXML()

	/**
	 * Loads a UI from an XML file
	 *
	 * @param string $filename the filename of the XML UI file to load.
	 * @param SwatContainer $root the container to load the XML UI into.
	 *
	 * @throws SwatFileNotFoundException, SwatInvalidSwatMLException,
	 *         SwatDuplicateIdException, SwatInvalidClassException, 
	 *         SwatInvalidPropertyException, SwatInvalidPropertyTypeException,
	 *         SwatDoesNotImplementException, SwatClassNotFoundException,
	 *         SwatInvalidConstantExpressionException,
	 *         SwatUndefinedConstantException
	 */
	public function loadFromXML($filename, $container = null)
	{
		if ($container === null)
			$container = $this->root;

		$xml_file = null;

		if (file_exists($filename)) {
			$xml_file = $filename;
		} else {
			$paths = explode(':', ini_get('include_path'));

			foreach ($paths as $path) {
				if (file_exists($path.'/'.$filename)) {
					$xml_file = $path.'/'.$filename;
					break;
				}
			}
		}

		// try to guess the translation callback based on the
		// filename of the xml
		$class_map_reversed = array_reverse($this->class_map, true);
		foreach ($class_map_reversed as $prefix => $path) {
			if (strpos($xml_file, $prefix) !== false &&
				is_callable(array($prefix, 'gettext'))) {

				$this->translation_callback = array($prefix, 'gettext');
			}
		}

		if ($xml_file === null)
			throw new SwatFileNotFoundException(
				"SwatML file not found: '{$filename}'.",
				0, $xml_file);

		$html_errors_value = self::initializeErrorHandler();

		$document = new DOMDocument();
		$document->resolveExternals = true;
		$document->validateOnParse = true;
		$document->load($xml_file);

		self::restoreErrorHandler($html_errors_value);

		// make sure we are using the correct document type
		if (count(self::$validation_errors) == 0 &&
			($document->doctype === null ||
			strcmp($document->doctype->name, 'swatml') != 0)) {
			throw new SwatInvalidSwatMLException(
				'SwatUI can only parse SwatML documents: ',
				0, $xml_file);
		}

		if (count(self::$validation_errors) > 0) {
			throw new SwatInvalidSwatMLException(
				"Invalid SwatML:\n".
				implode("\n", self::$validation_errors),
				0, $xml_file);
		}

		$this->parseUI($document->documentElement, $container);
	}

	// }}}
	// {{{ public function getWidget()

	/**
	 * Retrieves a widget from the internal widget list
	 *
	 * Looks up a widget in the widget list by the widget's unique identifier.
	 *
	 * @param string $id the id of the widget to retrieve.
	 *
	 * @return SwatWidget a reference to the widget.
	 *
	 * @throws SwatWidgetNotFoundException
	 */
	public function getWidget($id)
	{
		if (array_key_exists($id, $this->widgets))
			return $this->widgets[$id];
		else
			throw new SwatWidgetNotFoundException(
				"Widget with an id of '{$id}' not found.",
				0, $id);
	}

	// }}}
	// {{{ public function getRoot()

	/**
	 * Retrieves the topmost widget
	 *
	 * Looks up the widget at the root of the widget tree. The widget is
	 * always a container.
	 *
	 * @return SwatContainer a reference to the container widget.
	 */
	public function getRoot()
	{
		return $this->root;
	}

	// }}}
	// {{{ public function init()

	/**
	 * Initializes this interface
	 *
	 * Initializes this interface starting at the root element.
	 */
	public function init()
	{
		$this->root->init();
	}

	// }}}
	// {{{ public function process()

	/**
	 * Processes this interface
	 *
	 * Processes this interface starting at the root element.
	 */
	public function process()
	{
		$this->root->process();
	}

	// }}}
	// {{{ public function display()

	/**
	 * Displays this interface
	 *
	 * Displays this interface starting at the root element.
	 */
	public function display()
	{
		$this->root->display();
	}

	// }}}
	// {{{ public function displayTidy()

	/**
	 * Displays this interface with tidy XHTML
	 *
	 * The display() method is called and the output is cleaned up.
	 *
	 * @deprecated This method breaks some elements of swat by adding
	 *             whitespace between nodes. Use {@link SwatUI::display()}
	 *             instead.
	 */
	public function displayTidy()
	{
		$breaking_tags =
			'@</?(div|p|table|tr|td|ul|li|ol|dl|option)[^<>]*>@ui';

		ob_start();
		$this->display();
		$buffer = ob_get_clean();
		$tidy = preg_replace($breaking_tags, "\n\\0\n", $buffer);
		$tidy = str_replace("\n\n", "\n", $tidy);
		echo $tidy;
	}

	// }}}
	// {{{ public function setTranslationCallback()

	/**
	 * Sets the translation callback function for this UI
	 *
	 * UI properties marked as translatable are translated using this
	 * callback.
	 *
	 * The translation callback is usually set automatically but you may want
	 * to set it manually if automatic detection is not working.
	 *
	 * A callback in PHP is either a two element array or a string.
	 *
	 * @param callback $callback the callback function to use.
	 *
	 * @throws SwatInvalidCallbackException
	 */
	public function setTranslationCallback($callback)
	{
		if (is_callable($callback))
			$this->translation_callback = $callback;
		else
			throw new SwatInvalidCallbackException(
				'Cannot set translation callback to an uncallable callback.',
				0, $callback);
	}

	// }}}
	// {{{ public static function handleValidationErrors()

	/**
	 * Handles errors generated by loading and validation of malformed or
	 * non-conformant XML documents
	 *
	 * @param integer $errno the level of the error raised.
	 * @param string $errstring the error message.
	 */
	public static function handleValidationErrors($errno, $errstr)
	{
		$error = $errstr;
		self::$validation_errors[] = $error;
	}

	// }}}
	// {{{ private static function initializeErrorHandler()

	/**
	 * Initializes the custom error handler for loading and validating XML
	 * files
	 *
	 * Make sure to call {@link SwatUI::restoreErrorHandler()} some time after
	 * calling this function.
	 *
	 * @return boolean the old value of the ini value for html_errors.
	 */
	private static function initializeErrorHandler()
	{
		self::$validation_errors = array();
		$html_errors = ini_set('html_errors', false);
		set_error_handler(array('SwatUI', 'handleValidationErrors'),
			E_NOTICE | E_WARNING);

		return $html_errors;
	}

	// }}}
	// {{{ private static function restoreErrorHandler()

	/**
	 * Restores the custom error handler used for loading and validating XML
	 * files
	 *
	 * @param boolean $html_errors whether to turn on or off html_errors when
	 *                              restoring regular error handling.
	 *
	 * @see SwatUI::initializeErrorHandler()
	 */
	private static function restoreErrorHandler($html_errors)
	{
		ini_set('html_errors', $html_errors);
		restore_error_handler();
	}

	// }}}
	// {{{ private function parseUI()

	/**
	 * Recursivly parses an XML node into a widget tree
	 *
	 * Calls self on all node children.
	 *
	 * @param Object $node the XML node to begin with.
	 * @param SwatUIObject $parent the parent object (usually a SwatContainer)
	 *                              to add parsed objects to.
	 *
	 * @throws SwatDuplicateIdException, SwatInvalidClassException, 
	 *         SwatInvalidPropertyException, SwatInvalidPropertyTypeException,
	 *         SwatDoesNotImplementException, SwatClassNotFoundException,
	 *         SwatInvalidConstantExpressionException,
	 *         SwatUndefinedConstantException
	 */
	private function parseUI($node, SwatUIObject $parent)
	{
		array_push($this->stack, $parent);

		foreach ($node->childNodes as $child_node) {
			
			// only parse element nodes. ignore text nodes
			if ($child_node->nodeType == XML_ELEMENT_NODE) {

				if (strcmp($child_node->nodeName, 'property') == 0) {
					$this->parseProperty($child_node, $parent);
				} else {
					$parsed_object = $this->parseObject($child_node);

					$this->checkParsedObject($parsed_object,
						$child_node->nodeName);

					/*
					 * No exceptions were thrown and the widget has an id
					 * so add to widget list to make it look-up-able.
					 */
					if (strcmp($child_node->nodeName, 'widget') == 0 &&
						$parsed_object->id !== null) {
						$this->widgets[$parsed_object->id] = $parsed_object;
					}

					$this->attachToParent($parsed_object, $parent);
					$this->parseUI($child_node, $parsed_object);
				}
			}
		}

		array_pop($this->stack);
	}

	// }}}
	// {{{ private function checkParsedObject()

	/**
	 * Does some error checking on a parsed object
	 *
	 * Checks to make sure widget objects are created from widget elements
	 * and other objects are created from object elements.
	 *
	 * @param SwatUIObject $parsed_object an object that has been parsed from
	 *                                     SwatML.
	 * @param string $element_name the name of the XML element node the object
	 *                              was parsed from.
	 *
	 * @throws SwatDuplicateIdException, SwatInvalidClassException
	 */
	private function checkParsedObject(SwatUIObject $parsed_object,
		$element_name)
	{
		if ($element_name == 'widget') {
			if (class_exists('SwatWidget') &&
				$parsed_object instanceof SwatWidget &&
				$parsed_object->id !== null) {

				// make sure id is unique
				if (isset($this->widgets[$parsed_object->id]))
					throw new SwatDuplicateIdException(
						"A widget with an id of '{$parsed_object->id}' ".
						'already exists.',
						0, $parsed_object->id);

			} elseif (!class_exists('SwatWidget') ||
				!$parsed_object instanceof SwatWidget) {

				$class_name = get_class($parsed_object);

				throw new SwatInvalidClassException(
					"'{$class_name}' is defined in a widget element but is ".
					'not an instance of SwatWidget.',
					0, $parsed_object);
			}
		} elseif ($element_name == 'object') {
			if (class_exists('SwatWidget') &&
				$parsed_object instanceof SwatWidget) {

				$class_name = get_class($parsed_object);

				throw new SwatInvalidClassException(
					"'{$class_name}' is defined in an object element but is ".
					'and instance of SwatWidget and should be defined in a '.
					'widget element.',
					0, $parsed_object);
			}
		}
	}

	// }}}
	// {{{ private function attachToParent()

	/**
	 * Attaches a widget to a parent widget in the widget tree
	 *
	 * @param SwatUIObject $object the object to attach.
	 * @param SwatUIParent $parent the parent to attach the widget to.
	 *
	 * @throws SwatDoesNotImplementException
	 */
	private function attachToParent(SwatUIObject $object, SwatUIParent $parent)
	{
		if ($parent instanceof SwatUIParent) {
			$parent->addChild($object);
		} else {
			$class_name = get_class($parent);
			throw new SwatDoesNotImplementException(
				"Can not add object to parent. '{$class_name}' does not ".
				'implement SwatUIParent.', 0, $parent);
		}
	}

	// }}}
	// {{{ private function parseObject()

	/**
	 * Parses an XML object or widget element node into a PHP object
	 *
	 * @param array $node the XML element node to parse.
	 *
	 * @return SwatUIObject a reference to the object created.
	 *
	 * @throws SwatClassNotFoundException
	 */
	private function parseObject($node)
	{
		// class is required in the DTD
		$class = $node->getAttribute('class');

		if (!class_exists($class)) {

			$class_file = null;
			foreach ($this->class_map as $package_prefix => $path) {
				if (strncmp($class, $package_prefix, strlen($package_prefix)) == 0) {
					$class_file = "{$path}/{$class}.php";
					break;
				}
			}

			if ($class_file === null)
				throw new SwatClassNotFoundException(
					"Class '{$class}' is not defined and no suitable filename ".
					'for the class could be found. You may have forgotten to '.
					'map the class prefix to a path.',
					0, $class);

			require_once $class_file;
		}

		// NOTE: this works because SwatUIObject is abstract
		if (!is_subclass_of($class, 'SwatUIObject'))
			throw new SwatInvalidClassException(
				"Class '{$class}' is not a a descendant of SwatUIObject and ".
				'cannot be added to the widget tree.');

		$object = new $class();

		// id is optional in the DTD
		if ($node->hasAttribute('id'))
			$object->id = $node->getAttribute('id');

		return $object;
	}

	// }}}
	// {{{ private function parseProperty()

	/**
	 * Parses a single XML property node and applies it to an object
	 *
	 * @param array $property_node the XML property node to parse.
	 * @param SwatUIObject $object the object to apply the property to.
	 *
	 * @throws SwatInvalidPropertyException, SwatInvalidPropertyTypeException,
	 *         SwatInvalidConstantExpressionException,
	 *         SwatUndefinedConstantException
	 */
	private function parseProperty($property_node, SwatUIObject $object)
	{
		$class_properties = get_class_vars(get_class($object));

		// name is required in the DTD
		$name = trim($property_node->getAttribute('name'));
		$value = $property_node->nodeValue;
		
		$array_property = false;

		if (preg_match('/^(.*)\[(.*)\]$/u', $name, $regs)) {
			$array_property = true;
			$name = $regs[1];
			$array_key = strlen($regs[2]) == 0 ? null : $regs[2];
		}

		if (!array_key_exists($name, $class_properties)) {
			$class_name = get_class($object);
			throw new SwatInvalidPropertyException(
				"Property '{$name}' does not exist in class '{$class_name}' ".
				'but is used in SwatML.',
				0, $object, $name);
		}

		// translatable is always set in the DTD
		$translatable = (strcmp($property_node->getAttribute('translatable'),
			'yes') == 0);

		// type is always set in the DTD
		$type = $property_node->getAttribute('type');

		$parsed_value =
			$this->parseValue($name, $value, $type, $translatable, $object);

		if ($array_property) {
			if ($parsed_value instanceof SwatCellRendererMapping) {
				// it was a type=data property, 
				// so the parsed value is a mapping object
				$parsed_value->is_array = true;
				$parsed_value->array_key = $array_key;
			} else {
				if (!is_array($object->$name))
					$object->$name = array();

				$array_ref = &$object->$name;

				if ($array_key === null)
					$array_ref[] = $parsed_value;
				else
					$array_ref[$array_key] = $parsed_value;
			}
		} else {
			$object->$name = $parsed_value;
		}
	}

	// }}}
	// {{{ private function parseValue()

	/**
	 * Parses the value of a property
	 *
	 * Also does error notification in the event of a missing or unknown type
	 * attribute.
	 *
	 * @param string $name the name of the property.
	 * @param string $value the value of the property.
	 * @param string $type the type of the value.
	 * @param boolean translatable whether the property is translatable.
	 * @param SwatUIObject $object the object the property applies to.
	 *
	 * @return mixed the value of the property as an appropriate PHP datatype.
	 *
	 * @throws SwatInvalidPropertyTypeException,
	 *         SwatInvalidConstantExpressionException,
	 *         SwatUndefinedConstantException
	 */
	private function parseValue($name, $value, $type, $translatable,
		SwatUIObject $object)
	{
		switch ($type) {
		case 'string':
			return $this->translateValue($value, $translatable, $object);
		case 'boolean':
			return ($value == 'true') ? true : false;
		case 'integer':
			return intval($value);
		case 'float':
			return floatval($value);
		case 'constant':
			return $this->parseConstantExpression($value, $object);
		case 'data':
			return $this->parseMapping($name, $value, $object);
		case 'date':
			return new SwatDate($value);
		case 'implicit-string':
			if ($value == 'false' || $value == 'true' )
				trigger_error(__CLASS__.': Possible missing type="boolean" '.
					'attribute on property element', E_USER_NOTICE);

			if (is_numeric($value))
				trigger_error(__CLASS__.': Possible missing type="integer" '.
					' or type="float" attribute on property element',
					E_USER_NOTICE);

			return $this->translateValue($value, $translatable, $object);
		default:
			throw new SwatInvalidPropertyTypeException(
				"Property type '{$type}' is not a recognized type ".
				'but is used in SwatML.',
				0, $object, $type);
		}
	}

	// }}}
	// {{{ private function parseMapping()

	/**
	 * Handle a 'data' type property value by parsing it into a mapping object
	 *
	 * @param string $name the name of the property.
	 * @param string $value the value of the property.
	 * @param SwatUIObject $object the object the property applies to.
	 *
	 * @throws SwatInvalidPropertyTypeException
	 */
	private function parseMapping($name, $value, SwatUIObject $object)
	{
		$renderer = null;
		$renderer_container = null;

		foreach (array_reverse($this->stack) as $ancestor) {
			if ($renderer === null && $ancestor instanceof SwatCellRenderer) {
				$renderer = $ancestor;
			} else if ($ancestor instanceof SwatCellRendererContainer) {
				$renderer_container = $ancestor;
				break;
			}
		}

		if ($renderer === null || $renderer_container === null)
			throw new SwatInvalidPropertyTypeException(
				"Property type 'data' is only allowed on a SwatCellRenderer ".
				'or on a widget within a SwatWidgetCellRenderer.',
				0, $object, 'data');

		$mapping = $renderer_container->addMappingToRenderer($renderer,
			$value, $name, $object);

		return $mapping;
	}

	// }}}
	// {{{ private function translateValue()

	/**
	 * Translates a property value if possible
	 *
	 * @param string $value the value to be translated.
	 * @param boolean $translatable whether or not it is possible to translate
	 *                               the value.
	 * @param SwatUIObject $object the object the property value applies to.
	 *
	 * @return string the translated value if possible, otherwise $value.
	 */
	private function translateValue($value, $translatable, SwatUIObject $object)
	{
		if (!$translatable)
			return $value;

		if ($this->translation_callback !== null)
			return call_user_func($this->translation_callback, $value);
			
		return $value;
	}

	// }}}
	// {{{ private function parseConstantExpression()

	/**
	 * Evaluate a constant property value
	 *
	 * @param string $expression constant expression to evaluate.
	 * @param SwatUIObject $object the object that conatins the class constant.
	 *
	 * @return string the value of the class constant.
	 *
	 * @throws SwatInvalidConstantExpressionException,
	 *         SwatUndefinedConstantException
	 */
	private function parseConstantExpression($expression, SwatUIObject $object)
	{
		/*
		 * This method converts a constant expression into reverse polish
		 * notation and then evaluates it.
		 *
		 * Parsing the constant expression in this way makes it impossible
		 * for an expression to execute arbitrary code.
		 *
		 * The algorithm used is from Wikipedia:
		 * http://en.wikipedia.org/wiki/Reverse_Polish_Notation
		 */

		// operator => precedence
		$operators = array(
			'|' => 0,
			'&' => 1,
			'-' => 2,
			'+' => 2,
			'/' => 3,
			'*' => 3);

		// this includes parentheses
		$reg_exp  = '/([\|&\+\/\*\(\)-])/u';
		$tokens = preg_split($reg_exp, $expression, -1,
			PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		$stack = array();
		$queue = array();
		$eval_stack = array();
		$prev_token = null;

		$parenthesis_exception = new SwatInvalidConstantExpressionException(
			"Mismatched parentheses in constant expression '{$expression}' ".
			'in SwatML.',
			0, $expression);

		$syntax_exception = new SwatInvalidConstantExpressionException(
			"Invalid syntax in constant expression '{$expression}' in SwatML.",
			0, $expression);

		foreach ($tokens as $token) {
			
			if (strcmp($token, '(') == 0) {
				array_push($stack, $token);

			} elseif (strcmp($token, ')') == 0) {
				if (array_key_exists($prev_token, $operators))
					throw $syntax_exception;

				while (array_key_exists(end($stack), $operators)) {
					array_push($queue, array_pop($stack));
					if (count($stack) == 0)
						throw $parenthesis_exception;
				}

				if (strcmp(array_pop($stack), '(') != 0)
					throw $parenthesis_exception;

			} elseif (array_key_exists($token, $operators)) {
				if ($prev_token === null || strcmp($prev_token, '(') == 0 ||
					array_key_exists($prev_token, $operators))
					throw $syntax_exception;

				while (count($stack) > 0 &&
					array_key_exists(end($stack), $operators) &&
					$operators[$token] <= $operators[end($stack)])
					array_push($queue, array_pop($stack));

				array_push($stack, $token);

			} else {
				$constant = trim($token);

				// get a default scope for the constant
				if (strpos($constant, '::') === false)
					$constant = get_class($object) . '::' . $constant;

				// evaluate constant
				if (defined($constant))
					$constant = constant($constant);
				else
					throw new SwatUndefinedConstantException(
						"Undefined constant '{$constant}' in constant ".
						"expression '{$expression}' in SwatML.",
						0, $constant);

				array_push($queue, $constant);
			}

			$prev_token = $token;
		}

		// collect left over operators
		while (count($stack) > 0) {
			$operator = array_pop($stack);
			if (strcmp($operator, '(') == 0)
				throw $parenthesis_exception;

			array_push($queue, $operator);
		}

		$eval_stack = array();
		foreach ($queue as $value) {
			if (array_key_exists($value, $operators)) {
				$b = array_pop($eval_stack);
				$a = array_pop($eval_stack);

				if ($a === null || $b === null)
					throw $syntax_exception;

				switch ($value){
				case '|':
					array_push($eval_stack, $a | $b);
					break;
				case '&':
					array_push($eval_stack, $a & $b);
					break;
				case '-':
					array_push($eval_stack, $a - $b);
					break;
				case '+':
					array_push($eval_stack, $a + $b);
					break;
				case '/':
					array_push($eval_stack, $a / $b);
					break;
				case '*':
					array_push($eval_stack, $a * $b);
					break;
				}
			} else {
				array_push($eval_stack, $value);
			}
		}

		return array_pop($eval_stack);
	}

	// }}}
}

?>
