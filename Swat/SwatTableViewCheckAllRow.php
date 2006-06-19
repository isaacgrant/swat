<?php

require_once 'Swat/SwatTableViewRow.php';
require_once 'Swat/SwatCheckAll.php';

/**
 * A an extra row containing a check-all widget
 *
 * @package   Swat
 * @copyright 2005-2006 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatTableViewCheckAllRow extends SwatTableViewRow
{
	// {{{ private properties

	/**
	 * The unique identifier of the checkbox column that this check-all row
	 * is bound to
	 *
	 * @var string
	 */
	private $column_id;

	/**
	 * The check-all widget for this row 
	 *
	 * @var SwatCheckAll
	 */
	private $check_all;

	// }}}
	// {{{ public function __construct()

	/**
	 * Creates a new check-all row
	 *
	 * @param string $column_id the unique identifier of the checkbox column
	 *                           that this check-all row is bound to.
	 */
	public function __construct($column_id)
	{
		parent::__construct();
		$this->column_id = $column_id;
		$this->check_all = new SwatCheckAll();
	}

	// }}}
	// {{{ public function getHtmlHeadEntries()

	/**
	 * Gets the SwatHtmlHeadEntry objects needed by this check-all row
	 *
	 * @return SwatHtmlHeadEntrySet the SwatHtmlHeadEntry objects needed by
	 *                               this check-all row.
	 *
	 * @see SwatUIObject::getHtmlHeadEntries()
	 */
	public function getHtmlHeadEntries()
	{
		$out = new SwatHtmlHeadEntrySet($this->html_head_entries);
		$out->addEntrySet($this->check_all->getHtmlHeadEntries());
		return $out;
	}

	// }}}
	// {{{ public function init()

	/**
	 * Initializes this check-all row
	 */
	public function init()
	{
		parent::init();
		$this->check_all->init();
	}

	// }}}
	// {{{ public function process()

	/**
	 * Processes this check-all row
	 */
	public function process()
	{
		parent::process();
		$this->check_all->process();
	}

	// }}}
	// {{{ public function display()

	/**
	 * Displays this check-all row
	 */
	public function display()
	{
		$columns = $this->view->getVisibleColumns();

		if ($this->view->model->getRowCount() < 2)
			return;

		echo '<tr>';

		// find checkbox column position
		$position = 0;
		foreach ($columns as $column)
			if ($column->id == $this->column_id)
				break;
			else
				$position++;

		if ($position > 0) {
			$td_before_tag = new SwatHtmlTag('td');
			if ($position > 1)
				$td_before_tag->colspan = $position;

			$td_before_tag->open();
			echo '&nbsp;';
			$td_before_tag->close();
		}

		$td_tag = new SwatHtmlTag('td');
		if (count($columns) - $position > 1)
			$td_tag->colspan = count($columns) - $position;

		$td_tag->open();
		$this->check_all->display();
		$td_tag->close();

		echo '</tr>';
	}

	// }}}
	// {{{ public function getInlineJavaScript()

	/**
	 * Gets the inline JavaScript required for this row
	 *
	 * @return string the inline JavaScript required for this row.
	 *
	 * @see SwatTableViewRow::getInlineJavaScript()
	 */
	public function getInlineJavaScript()
	{
		if ($this->view->model->getRowCount() < 2)
			return '';

		// set the controller of the check-all widget
		return sprintf("%s_obj.setController(%s);",
			$this->check_all->id, $this->column_id);
	}

	// }}}
}

?>
