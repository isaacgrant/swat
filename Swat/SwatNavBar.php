<?php

require_once 'Swat/SwatControl.php';
require_once 'Swat/SwatNavBarEntry.php';

/**
 * Visible navbar navigation tool
 *
 * @package   Swat
 * @copyright 2004-2005 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatNavBar extends SwatControl
{
	// {{{ private properties

	/**
	 * Array of SwatNavBarEntry objects displayed in this navbar
	 *
	 * @var array
	 *
	 * @see SwatNavBarEntry
	 */
	private $entries = array();

	// }}}
	// {{{ public function createEntry()

	/**
	 * Creates a SwatNavBarEntry and adds it to the end of this navigation bar
	 *
	 * @param string $title the entry title.
	 * @param string $uri an optional entry URI.
	 */
	public function createEntry($title, $uri = null)
	{
		$this->addEntry(new SwatNavBarEntry($title, $uri));
	}

	// }}}
	// {{{ public function addEntry()

	/**
	 * Adds a SwatNavBarEntry to the end of this navigation bar
	 *
	 * @param SwatNavBarEntry $entry the entry to add.
	 */
	public function addEntry($entry)
	{
		$this->entries[] = $entry;
	}

	// }}}
	// {{{ public function replaceElement()

	/**
	 * Replaces an element in this navigation bar
	 *
	 * If the element is not in this navigation bar, an exception is thrown.
	 *
	 * @param integer $position position of the element to replace.
	 * @param string $title the replacement element title.
	 * @param string $uri an optional replacement element URI.
	 *
	 * @thows SwatException
	 */
	/*
	public function replaceElement($position, $title, $uri = null)
	{
		if (isset($this->elements[$position])) {
			$new_element = array('title' => $title, 'uri' => $uri);
			$this->elements[$position] = &$new_element;
		} else {
			throw new SwatException('Cannot replace element at position '.
				"'$position' because element does not exist.",
		}
	}
	*/

	// }}}
	// {{{ public function getEntryByPosition()

	/**
	 * Gets an entry from this navigation bar
	 *
	 * If the entry is not in this navigation bar, an exception is thrown.
	 *
	 * @param integer $position zero-based ordinal position of the entry to 
	 *                           fetch.
	 *
	 * @return SwatNavBarEntry the entry.
	 *
	 * @throws SwatException
	 */
	public function getEntryByPosition($position)
	{
		if (isset($this->entries[$position]))
			return $this->entries[$position];
		else
			throw new SwatException('Navbar does not contain an entry at '.
				"position '{$position}'");
	}

	// }}}
	// {{{ public function getCount()

	/**
	 * Gets the number of entries in this navigational bar
	 *
	 * @return integer number of entries in this navigational bar.
	 */
	public function getCount()
	{
		return count($this->entries);
	}

	// }}}
	// {{{ public function popEntry()

	/**
	 * Pops the last entry off the end of this navigational bar
	 *
	 * If no entries currently exist, an exception is thrown.
	 *
	 * @return SwatNavBarEntry the entry that was popped.
	 *
	 * @throws SwatException
	 */
	public function popEntry()
	{
		if ($this->getCount() < 1) {
			throw new SwatException('Cannot pop entry. NavBar does not contain any elements.');
		} else {
			return array_pop($this->entries);
		}
	}

	// }}}
	// {{{ public function popEntries()

	/**
	 * Pops one or more entries off the end of this navigational bar
	 *
	 * If more entries are to be popped than currently exist, an exception is
	 * thrown.
	 *
	 * @param $number integer number of entries to pop off this navigational
	 *                         bar.
	 *
	 * @return array an array of SwatNavBarEntry objects that were popped off
	 *                the navagational bar.
	 *
	 * @throws SwatException
	 */
	public function popEntries($number)
	{
		if ($this->getCount() < $number) {
			$count = $this->getCount();

			throw new SwatException("NavBar only contains $class entries, ".
				"unable to pop $number entries.");

		} else {
			return array_splice($this->entries, -$number);
		}
	}

	// }}}
	// {{{ public function display()

	/**
	 * Displays this navigational bar
	 *
	 * Displays each entry separated by a special character and outputs
	 * entries with URI's as anchor tags.
	 *
	 * @param boolean $link_last_entry if true will ahow the last entry as
	 *                                  a hyperlink. If false, will show the
	 *                                  last entry as just text.
	 */
	public function display($link_last_entry = true)
	{
		if (!$this->visible)
			return;

		$count = $this->getCount();
		$i = 0;

		foreach ($this->entries as $entry) {
			if ($i++ != 0)
				echo ' &#187; ';

			if ($entry->uri !== null && ($link_last_entry || $i < $count)) {
				$link_tag = new SwatHtmlTag('a');
				$link_tag->href = $entry->uri;
				$link_tag->content = $entry->title;
				$link_tag->display();
			} else {
				echo $entry->title;
			}
		}
	}

	// }}}
}

?>
