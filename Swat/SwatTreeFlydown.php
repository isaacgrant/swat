<?php

require_once 'Swat/SwatDataTreeNode.php';
require_once 'Swat/SwatFlydown.php';

/**
 * A flydown (aka combo-box) selection widget formatted into a tree
 *
 * @package   Swat
 * @copyright 2004-2005 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatTreeFlydown extends SwatFlydown
{
	/**
	 * Tree Path
	 *
	 * An array containing the branch of the selected node.
	 *
	 * @var array
	 */
	public $path = array();

	/**
	 * A tree collection of {@link SwatDataTreeNode} objects for this flydown
	 *
	 * When the {@link SwatFlydown::display()} method is called, this property
	 * overwrites the {@link SwatFlydown::$options} property.
	 *
	 * @var SwatDataTreeNode
	 */
	protected $tree = null;

	/**
	 * Displays this tree flydown
	 *
	 * The tree is represented by placing spaces in front of nodes on different
	 * levels.
	 */
	public function display()
	{
		if (!$this->visible)
			return;

		$this->options = array();

		if ($this->tree !== null) {
			foreach ($this->tree->toArray() as $path => $title) {
				$pad = str_repeat('&nbsp;&nbsp;', substr_count($path, '/'));
				$this->addOption($path, $pad.$title);
			}
		}

		// temporarily encode the path into the value for parent::display()
		$actual_value = $this->value;
		$this->value = implode('/', $this->path);
		parent::display();
		$this->value = $actual_value;
	}

	/**
	 * Sets the tree to use for display
	 *
	 * @param SwatDataTreeNode $tree the tree to use for display.
	 */
	public function setTree(SwatDataTreeNode $tree)
	{
		$this->tree = $tree;
	}

	/**
	 * Processes this tree flydown
	 *
	 * Populates the path property of this flydown with the path to the node
	 * selected by the user. The widget value is set to the last id in the
	 * path array.
	 */
	public function process()
	{
		parent::process();

		if ($this->value === null) {
			$this->path = array();
		} else {
			$this->path = explode('/', $this->value);
			$this->value = end($this->path);
		}
	}
}

?>
