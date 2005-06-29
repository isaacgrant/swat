<?php

require_once 'Swat/SwatTableViewColumn.php';
require_once 'Swat/SwatHtmlTag.php';

/**
 * An orderable table view column.
 *
 * This column has a clickable header that allows the user to change the
 * ordering of the column. This behaviour is commonly used for databound table
 * columns.
 *
 * TODO: Implement this functionality with AJAX.
 *
 * @package   Swat
 * @copyright 2004-2005 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatTableViewOrderableColumn extends SwatTableViewColumn
{
	// {{{ class constants
	
	/**
	 * Indicates no ordering is done
	 */
	const ORDER_BY_DIR_NONE = 0;
	
	/**
	 * Indicates descending ordering is done
	 */
	const ORDER_BY_DIR_DESCENDING = 1;

	/**
	 * Indicates ascending ordering is done
	 */
	const ORDER_BY_DIR_ASCENDING = 2;

	// }}}
	// {{{ public properties
	
	/**
	 * The base of the link used when building column header links
	 *
	 * Additional GET variables are appended to this link in the getLink()
	 * method.
	 *
	 * @var string
	 *
	 * @see SwatTableViewOrderableColumn::getLink()
	 */
	public $link = '';

	/**
	 * HTTP GET variables to remove from the column header link
	 *
	 * An array of GET variable names to unset before building new links.
	 *
	 * @var array
	 */
	public $unset_get_vars = array();

	/**
	 * The direction of ordering
	 *
	 * The current direction of ordering for this column. Valid values are
	 * ORDER_BY_DIR_* constants.
	 *
	 * @var int
	 */
	public $direction = SwatTableViewOrderableColumn::ORDER_BY_DIR_NONE;

	// }}}
	// {{{ public function init()
	
	/**
	 * Initializes this column
	 *
	 * The current direction of ordering is grabbed from GET variables.
	 */
	public function init()
	{
		// TODO: is id a required field of table views?
		$key_orderby = $this->view->id.'_orderby';
		$key_orderbydir = $this->view->id.'_orderbydir';

		// TODO: is id a required field of this class?
		if (isset($_GET[$key_orderby]) && $_GET[$key_orderby] == $this->id) {
			$this->view->orderby_column = $this;

			if (isset($_GET[$key_orderbydir]))
				$this->setDirectionByString($_GET[$key_orderbydir]);
		}
	}

	// }}}
	// {{{ public function displayHeader()
	
	/**
	 * Displays the column header for this table view column
	 *
	 * This method displays an appropriate header based on the current
	 * direction of ordering of this table view column. If the column has been
	 * ordered, an image indicating the ordering is also displayed in the
	 * header.
	 */
	public function displayHeader()
	{
		$anchor = new SwatHtmlTag('a');
		$anchor->href = $this->getLink();

		$anchor->open();
		
		echo $this->title, '&nbsp;';

		// display image
		if ($this->view->orderby_column === $this &&
			$this->direction !=
				SwatTableViewOrderableColumn::ORDER_BY_DIR_NONE) {
			
			$img = new SwatHtmlTag('img');
	
			if ($this->direction ==
				SwatTableViewOrderableColumn::ORDER_BY_DIR_DESCENDING) {
				
				$img->src = 'swat/images/table-view-column-desc.png';
				$img->alt = Swat::_('Descending');
				
			} elseif ($this->direction ==
				SwatTableViewOrderableColumn::ORDER_BY_DIR_ASCENDING) {
				
				$img->src = 'swat/images/table-view-column-asc.png';
				$img->alt = Swat::_('Ascending');
				
			}

			$img->width = 16;
			$img->height = 16;
			
			$img->display();
		}

		$anchor->close();
	}

	// }}}
	// {{{ public function getDirectionAsString()
	
	/**
	 * Gets the direction of ordering as a string
	 *
	 * Retrieves the current ordering direction of this column or an arbitrary
	 * direction constant as a string. The string is returned the lowercase
	 * abbreviated form 'asc' or 'desc'.
	 *
	 * @param int $direction_id an optional direction constant to convert to
	 *                           a string rather than using this column's
	 *                           current direction.
	 *
	 * @return string the direction of ordering.
	 */
	public function getDirectionAsString($direction_id = null)
	{
		if ($direction_id === null)
			$direction_id = $this->direction;

		switch ($direction_id) {
		case SwatTableViewOrderableColumn::ORDER_BY_DIR_NONE:
			return '';

		case SwatTableViewOrderableColumn::ORDER_BY_DIR_ASCENDING:
			return 'asc';

		case SwatTableViewOrderableColumn::ORDER_BY_DIR_DESCENDING:
			return 'desc';

		default:
			throw new Exception(__CLASS__.': unknown ordering.');
		}
	}

	// }}}
	// {{{ public function setDirectionByString()
	
	/**
	 * Sets direction of ordering by a string
	 *
	 * Sets the current ordering direction of this column.
	 *
	 * @param string $direction ordering direction as a string. The direction
	 *                           is case insensitive and may be the short form
	 *                           'asc' or 'desc' or the long form 'ascending'
	 *                           or 'descending'.
	 */
	public function setDirectionByString($direction)
	{
		$direction = strtolower($direction);
		
		switch ($direction) {
		case 'ascending':
		case 'asc':
			$this->direction =
				SwatTableViewOrderableColumn::ORDER_BY_DIR_ASCENDING;

			break;

		case 'descending':
		case 'desc':
			$this->direction =
				SwatTableViewOrderableColumn::ORDER_BY_DIR_DESCENDING;
				
			break;

		default:
			$this->direction =
				SwatTableViewOrderableColumn::ORDER_BY_DIR_NONE;
		}
	}

	// }}}
	// {{{ private function getLink()
	
	/**
	 * Gets the link for this column's header
	 *
	 * This method builds the link by appending special GET variables and
	 * unsetting other ones.
	 *
	 * @return string the link for this column's header.
	 */
	private function getLink()
	{
		// unset GET vars that we want to ignore
		$vars = $_GET;

		foreach($vars as $name => $value)
 			if (in_array($name, $this->unset_get_vars))
				unset($vars[$name]);

		// TODO:: is id a required field for table views?
		$key_orderby = $this->view->id.'_orderby';
		$key_orderbydir = $this->view->id.'_orderbydir';

		unset($vars[$key_orderby]);
		unset($vars[$key_orderbydir]);

		$next_dir = $this->getNextDirection();

		if ($next_dir != SwatTableViewOrderableColumn::ORDER_BY_DIR_NONE) {
			$vars[$key_orderby] = $this->id;
			$vars[$key_orderbydir] = $this->getDirectionAsString($next_dir);
		}

		// start building the new link
		$link = $this->link.'?';

		foreach($vars as $name => $value)
			$link .= $name.'='.$value.'&';

		// remove trailing ampersand
		$link = substr($link, 0, -1);

		return $link;
	}

	// }}}
	// {{{ private function getNextDirection()
	
	/**
	 * Gets the next direction or ordering in the rotation
	 *
	 * As a user clicks on the comun headers the direction of ordering changes
	 * from 'none' => 'asc' => 'desc' => 'none' in a loop.
	 *
	 * @return integer the next direction of ordering for this column.
	 */
	private function getNextDirection()
	{
		switch ($this->direction) {
		case SwatTableViewOrderableColumn::ORDER_BY_DIR_NONE:
			return SwatTableViewOrderableColumn::ORDER_BY_DIR_ASCENDING;

		case SwatTableViewOrderableColumn::ORDER_BY_DIR_ASCENDING:
			return SwatTableViewOrderableColumn::ORDER_BY_DIR_DESCENDING;

		default:
			return SwatTableViewOrderableColumn::ORDER_BY_DIR_NONE;
		}
	}

	// }}}
}

?>
