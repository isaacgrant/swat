<?php

require_once 'Swat/SwatFormField.php';

/**
 * A container to use around control widgets in a form
 *
 * Adds a label and space to output messages.
 *
 * @package   Swat
 * @copyright 2005-2006 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatFooterFormField extends SwatFormField
{
	// {{{ protected function getCSSClassNames()

	/**
	 * Gets the array of CSS classes that are applied to this footer form field
	 *
	 * @return array the array of CSS classes that are applied to this footer
	 *                form field.
	 */
	protected function getCSSClassNames()
	{
		$classes = array('swat-footer-form-field');
		$classes = array_merge($classes, $this->classes);
		return $classes;
	}

	// }}}
}

?>
