<?php
require_once('Swat/SwatControl.php');
require_once('Swat/SwatHtmlTag.php');
require_once('Swat/SwatState.php');

/**
 * A single line text entry widget
 *
 * @package Swat
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright silverorange 2004
 */
class SwatEntry extends SwatControl implements SwatState {

	/**
	 * Entry value
	 *
	 * Text content of the widget, or null.
	 * @var string
	 */
	public $value = null;

	/**
	 * Required
	 *
	 * Must have a non-empty value when processed.
	 * @var bool
	 */
	public $required = false;
	
	/**
	 * Input size
	 *
	 * Size in characters of the HTML text form input, or null.
	 * @var int
	 */
	public $size = 50;
	
	/**
	 * Max length
	 *
	 * Maximum number of allowable characters in HTML text form input, or null.
	 * @var int
	 */
	public $maxlength = null;

	protected $html_input_type = 'text';

	public function display() {
		$input_tag = new SwatHtmlTag('input');
		$input_tag->type = $this->html_input_type;
		$input_tag->name = $this->name;
		$input_tag->id = $this->name;
		$input_tag->onfocus = "this.select();";

		if ($this->value !== null)
			$input_tag->value = $this->value;

		if ($this->size !== null)
			$input_tag->size = $this->size;

		if ($this->maxlength !== null)
			$input_tag->maxlength = $this->maxlength;

		$input_tag->display();
	}	

	public function process() {
		if (strlen($_POST[$this->name]) == 0)
			$this->value = null;
		else
			$this->value = $_POST[$this->name];

		if ($this->required && !strlen($this->value)) {
			$msg = _S("The %s field is required.");
			$this->addMessage(new SwatMessage($msg, SwatMessage::USER_ERROR));
		}
	}
	
	public function getState() {
		return $this->value;
	}

	public function setState($state) {
		$this->value = $state;
	}
}

?>
