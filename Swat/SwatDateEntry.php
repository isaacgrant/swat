<?php

/* vim: set noexpandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

require_once 'Swat/SwatInputControl.php';
require_once 'Swat/SwatFlydown.php';
require_once 'Swat/SwatDate.php';
require_once 'Swat/SwatState.php';
require_once 'Swat/SwatYUI.php';
require_once 'Swat/SwatHtmlTag.php';

/**
 * A date entry widget
 *
 * @package   Swat
 * @copyright 2004-2007 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatDateEntry extends SwatInputControl implements SwatState
{
	// {{{ class constants

	const YEAR     = 1;
	const MONTH    = 2;
	const DAY      = 4;
	const TIME     = 8;
	const CALENDAR = 16;

	// }}}
	// {{{ public properties

	/**
	 * Date of this date entry widget
	 *
	 * @var Date
	 */
	public $value = null;

	/**
	 * Required date parts
	 *
	 * Bitwise combination of {@link SwatDateEntry::YEAR},
	 * {@link SwatDateEntry::MONTH}, {@link SwatDateEntry::DAY} and
	 * {@link SwatDateEntry::TIME}.
	 *
	 * For example, to require the month and day to be entered in a date
	 * selector widget use the following:
	 *
	 * <code>
	 * $date->required_parts = SwatDateEntry::MONTH | SwatDateEntry::DAY;
	 * </code>
	 *
	 * @var integer
	 */
	public $required_parts;

	/**
	 * Displayed date parts
	 *
	 * Bitwise combination of {@link SwatDateEntry::YEAR},
	 * {@link SwatDateEntry::MONTH}, {@link SwatDateEntry::DAY},
	 * {@link SwatDateEntry::TIME} and {@link SwatDateEntry::CALENDAR}.
	 *
	 * For example, to show a date selector widget with just the month and year
	 * use the following:
	 *
	 * <code>
	 * $date->display_parts = SwatDateEntry::YEAR | SwatDateEntry::MONTH;
	 * </code>
	 *
	 * @var integer
	 */
	public $display_parts;

	/**
	 * Start date of the valid range (inclusive)
	 *
	 * Defaults to 20 years in the past.
	 *
	 * @var Date
	 */
	public $valid_range_start;

	/**
	 * End date of the valid range (exclusive)
	 *
	 * Defaults to 20 years in the future.
	 *
	 * @var Date
	 */
	public $valid_range_end;

	/**
	 * Whether the numeric month code is displayed in the month flydown
	 *
	 * This is useful for credit card date entry
	 *
	 * @var boolean
	 */
	public $show_month_number = false;

	// }}}
	// {{{ private properties

	/**
	 * A reference to the internal year flydown
	 *
	 * @var SwatFlydown
	 */
	private $year_flydown = null;

	/**
	 * A reference to the internal month flydown
	 *
	 * @var SwatFlydown
	 */
	private $month_flydown = null;

	/**
	 * A reference to the internal day flydown
	 *
	 * @var SwatFlydown
	 */
	private $day_flydown = null;

	/**
	 * A reference to the internal time entry
	 *
	 * @var SwatTimeEntry
	 */
	private $time_entry = null;

	/**
	 * A reference to the internal calendar widget
	 *
	 * @var SwatCalendar
	 */
	private $calendar = null;

	/**
	 * An internal flag that is set to true when embedded widgets have been
	 * created
	 *
	 * @var boolean
	 *
	 * @see SwatDateEntry::createEmbeddedWidgets()
	 */
	private $widgets_created = false;

	// }}}
	// {{{ public function __construct()

	/**
	 * Creates a new date entry widget
	 *
	 * Sets default required and display parts and sets default valid range
	 * for this date entry.
	 *
	 * @param string $id a non-visible unique id for this widget.
	 *
	 * @see SwatWidget::__construct()
	 */
	public function __construct($id = null)
	{
		parent::__construct($id);

		$this->required_parts = self::YEAR | self::MONTH | self::DAY;
		$this->display_parts  = self::YEAR | self::MONTH |
		                        self::DAY | self::CALENDAR;

		$this->setValidRange(-20, 20);

		$this->requires_id = true;

		$yui = new SwatYUI(array('event'));
		$this->html_head_entry_set->addEntrySet($yui->getHtmlHeadEntrySet());
		$this->addJavaScript('packages/swat/javascript/swat-date-entry.js',
			Swat::PACKAGE_ID);
	}

	// }}}
	// {{{ public function __clone()

	/**
	 * Clones the embedded widgets of this date widget
	 */
	public function __clone()
	{
		$this->valid_range_start = clone $this->valid_range_start;
		$this->valid_range_end = clone $this->valid_range_end;

		if ($this->widgets_created) {
			if ($this->display_parts & self::YEAR)
				$this->year_flydown = clone $this->year_flydown;

			if ($this->display_parts & self::MONTH)
				$this->month_flydown = clone $this->month_flydown;

			if ($this->display_parts & self::DAY)
				$this->day_flydown = clone $this->day_flydown;

			if ($this->display_parts & self::TIME)
				$this->time_entry = clone $this->time_entry;

			if ($this->display_parts & self::CALENDAR)
				$this->calendar = clone $this->calendar;
		}
	}

	// }}}
	// {{{ public function setValidRange()

	/**
	 * Set the valid date range
	 *
	 * Convenience method to set the valid date range by year offsets.
	 *
	 * @param integer $start_offset offset from the current year in years used
	 *                               to set the starting year of the valid
	 *                               range.
	 * @param integer $end_offset offset from the current year in years used
	 *                             to set the ending year of the valid range.
	 */
	public function setValidRange($start_offset, $end_offset)
	{
		// Beginning of this year
		$date = new SwatDate();
		$date->setMonth(1);
		$date->setDay(1);
		$date->setHour(0);
		$date->setMinute(0);
		$date->setSecond(0);
		$date->setTZ('UTC');

		$this->valid_range_start = clone $date;
		$this->valid_range_end = clone $date;

		$year = $date->getYear();
		$this->valid_range_start->setYear($year + $start_offset);
		$this->valid_range_end->setYear($year + $end_offset + 1);
	}

	// }}}
	// {{{ public function display()

	/**
	 * Displays this date entry
	 *
	 * Creates internal widgets if they do not exits then displays required
	 * JavaScript, then displays internal widgets.
	 */
	public function display()
	{
		if (!$this->visible)
			return;

		parent::display();

		$this->createEmbeddedWidgets();

		$div_tag = new SwatHtmlTag('div');
		$div_tag->id = $this->id;
		$div_tag->class = $this->getCSSClassString();
		$div_tag->open();

		echo '<span class="swat-date-entry-span">';

		foreach ($this->getDatePartOrder() as $datepart) {
			if ($datepart == 'd' && $this->display_parts & self::DAY) {
				if ($this->day_flydown->value === null &&
					$this->value !== null) {
					$this->day_flydown->value = $this->value->getDay();
				}

				$this->day_flydown->display();
			} elseif ($datepart == 'm' && $this->display_parts & self::MONTH) {
				if ($this->month_flydown->value === null &&
					$this->value !== null) {
					$this->month_flydown->value = $this->value->getMonth();
				}

				$this->month_flydown->display();
			} elseif ($datepart == 'y' && $this->display_parts & self::YEAR) {
				if ($this->year_flydown->value === null &&
					$this->value !== null) {
					$this->year_flydown->value = $this->value->getYear();
				}

				$this->year_flydown->display();
			}
		}

		echo '</span>';

		if ($this->display_parts & self::CALENDAR) {
			$this->calendar->display();
		}

		if ($this->display_parts & self::TIME) {
			echo ' ';
			if ($this->time_entry->value === null && $this->value !== null)
				$this->time_entry->value = $this->value;

			$this->time_entry->display();
		}

		Swat::displayInlineJavaScript($this->getInlineJavaScript());

		$div_tag->close();
	}

	// }}}
	// {{{ public function process()

	/**
	 * Processes this date entry
	 *
	 * Creates internal widgets if they do not exist and then assigns their
	 * values based on the date entered by the user. If the date is not valid,
	 * an error message is attached to this date entry.
	 */
	public function process()
	{
		parent::process();

		if (!$this->isVisible())
			return;

		$this->createEmbeddedWidgets();

		$year = 0;
		$month = 1;
		$day = 1;
		$hour = 0;
		$minute = 0;
		$second = 0;

		$all_empty = true;
		$any_empty = false;

		if ($this->display_parts & self::YEAR) {
			$this->year_flydown->process();
			$year = $this->year_flydown->value;
			if ($year === null) {
				if ($this->required_parts & self::YEAR) {
					$any_empty = true;
				} else {
					$year = 0;
				}
			} else {
				$all_empty = false;
			}
		}

		if ($this->display_parts & self::MONTH) {
			$this->month_flydown->process();
			$month = $this->month_flydown->value;
			if ($month === null) {
				if ($this->required_parts & self::MONTH) {
					$any_empty = true;
				} else {
					$month = 1;
				}
			} else {
				$all_empty = false;
			}
		}

		if ($this->display_parts & self::DAY) {
			$this->day_flydown->process();
			$day = $this->day_flydown->value;
			if ($day === null) {
				if ($this->required_parts & self::DAY) {
					$any_empty = true;
				} else {
					$day = 1;
				}
			} else {
				$all_empty = false;
			}
		}

		if ($this->display_parts & self::TIME) {
			$this->time_entry->process();
			if ($this->time_entry->value === null) {
				if ($this->required_parts & self::TIME) {
					$any_empty = true;
				} else {
					$hour = 0;
					$minute = 0;
					$second = 0;
				}
			} else {
				$hour = $this->time_entry->value->getHour();
				$minute = $this->time_entry->value->getMinute();
				$second = $this->time_entry->value->getSecond();
				$all_empty = false;
			}
		}

		if ($all_empty) {
			if ($this->required && $this->isSensitive()) {
				$message = Swat::_('The %s field is required.');
				$this->addMessage(new SwatMessage($message,
					SwatMessage::ERROR));
			}
			$this->value = null;
		} elseif ($any_empty) {
			$message = Swat::_('The %s field is not a valid date.');
			$this->addMessage(new SwatMessage($message, SwatMessage::ERROR));
			$this->value = null;
		} else {
			$this->value = new SwatDate();
			$this->value->setYear($year);
			$this->value->setMonth($month);
			$this->value->setDay($day);
			$this->value->setHour($hour);
			$this->value->setMinute($minute);
			$this->value->setSecond($second);
			$this->value->setTZ('UTC');

			$this->validateRanges();
		}
	}

	// }}}
	// {{{ public function getState()

	/**
	 * Gets the current state of this date entry widget
	 *
	 * @return boolean the current state of this date entry widget.
	 *
	 * @see SwatState::getState()
	 */
	public function getState()
	{
		if ($this->value === null)
			return null;
		else
			return $this->value->getDate();
	}

	// }}}
	// {{{ public function setState()

	/**
	 * Sets the current state of this date entry widget
	 *
	 * @param boolean $state the new state of this date entry widget.
	 *
	 * @see SwatState::setState()
	 */
	public function setState($state)
	{
		$this->value = new SwatDate($state);
	}

	// }}}
	// {{{ public function getHtmlHeadEntrySet()

	/**
	 * Gets the SwatHtmlHeadEntry objects needed by this date entry
	 *
	 * @return SwatHtmlHeadEntrySet the SwatHtmlHeadEntry objects needed by
	 *                               this date entry.
	 *
	 * @see SwatUIObject::getHtmlHeadEntrySet()
	 */
	public function getHtmlHeadEntrySet()
	{
		$set = parent::getHtmlHeadEntrySet();
		$this->createEmbeddedWidgets();

		if ($this->display_parts & self::TIME)
			$set->addEntrySet($this->time_entry->getHtmlHeadEntrySet());

		if ($this->display_parts & self::CALENDAR)
			$set->addEntrySet($this->calendar->getHtmlHeadEntrySet());

		return $set;
	}

	// }}}
	// {{{ public function isValid()

	/**
	 * Checks if the entered date is within the valid range
	 *
	 * @return boolean true if the entered date is within the valid range and
	 *                  false if the entered date is not within the valid range.
	 */
	public function isValid()
	{
		return ($this->isStartDateValid() && $this->isEndDateValid());
	}

	// }}}
	// {{{ protected function getInlineJavaScript()

	/**
	 * Gets the inline JavaScript required for this control
	 *
	 * @return string the inline JavaScript required for this control.
	 */
	protected function getInlineJavaScript()
	{
		$javascript = sprintf("var %s_obj = new SwatDateEntry('%s');",
			$this->id, $this->id);

		if ($this->display_parts & self::DAY) {
			$lookup_days = array();
			foreach ($this->day_flydown->options as $key => $option)
				$lookup_days[] = sprintf('%s: %s',
					$option->value,
					($this->day_flydown->show_blank) ? $key + 1 : $key);

			$javascript.= sprintf("\n%s_obj.addLookupTable('day', {%s});",
				$this->id, implode(', ', $lookup_days));
		}

		if ($this->display_parts & self::MONTH) {
			$lookup_months = array();
			foreach ($this->month_flydown->options as $key => $option)
				$lookup_months[] = sprintf('%s: %s',
					$option->value,
					($this->month_flydown->show_blank) ? $key + 1 : $key);

			$javascript.= sprintf("\n%s_obj.addLookupTable('month', {%s});",
				$this->id, implode(', ', $lookup_months));
		}

		if ($this->display_parts & self::YEAR) {
			$lookup_years = array();
			foreach ($this->year_flydown->options as $key => $option)
				$lookup_years[] = sprintf('%s: %s',
					$option->value,
					($this->year_flydown->show_blank) ? $key + 1 : $key);

			$javascript.= sprintf("\n%s_obj.addLookupTable('year', {%s});",
				$this->id, implode(', ', $lookup_years));
		}

		if ($this->display_parts & self::TIME)
			$javascript.= sprintf("\n%s_obj.setSwatTime(%s_time_entry_obj);",
				$this->id, $this->id);

		if ($this->display_parts & self::CALENDAR)
			$javascript.= sprintf("\n%s_calendar_obj.setSwatDateEntry(%s_obj);",
				$this->id, $this->id);

		return $javascript;
	}

	// }}}
	// {{{ protected function getCSSClassNames()

	/**
	 * Gets the array of CSS classes that are applied to this date entry widget
	 *
	 * @return array the array of CSS classes that are applied to this date
	 *                entry widget.
	 */
	protected function getCSSClassNames()
	{
		$classes = array('swat-date-entry');
		$classes = array_merge($classes, parent::getCSSClassNames());
		return $classes;
	}

	// }}}
	// {{{ protected function isStartDateValid()

	/**
	 * Checks if the entered date is valid with respect to the valid start
	 * date
	 *
	 * @return boolean true if the entered date is on or after the valid start
	 *                  date and false if the entered date is before the valid
	 *                  start date.
	 */
	protected function isStartDateValid()
	{
		$this->valid_range_start->setTZ('UTC');
		return (Date::compare(
			$this->value, $this->valid_range_start, true) >= 0);
	}

	// }}}
	// {{{ protected function isEndDateValid()

	/**
	 * Checks if the entered date is valid with respect to the valid end date
	 *
	 * @return boolean true if the entered date is before the valid end date
	 *                  and false if the entered date is on or after the valid
	 *                  end date.
	 */
	protected function isEndDateValid()
	{
		$this->valid_range_end->setTZ('UTC');
		return (Date::compare(
			$this->value, $this->valid_range_end, true) < 0);
	}

	// }}}
	// {{{ protected function validateRanges()

	/**
	 * Makes sure the date the user entered is within the valid range
	 *
	 * If the date is not within the valid range, this method attaches an
	 * error message to this date entry.
	 */
	protected function validateRanges()
	{
		if (!$this->isStartDateValid()) {
			$message = sprintf(Swat::_('The date you have entered is invalid. '.
				'It must be on or after %s.'),
				$this->getFormattedDate($this->valid_range_start));

			$this->addMessage(new SwatMessage($message, SwatMessage::ERROR));

		} elseif (!$this->isEndDateValid()) {
			$message = sprintf(Swat::_('The date you have entered is invalid. '.
				'It must be before %s.'),
				$this->getFormattedDate($this->valid_range_end));

			$this->addMessage(new SwatMessage($message, SwatMessage::ERROR));
		}
	}

	// }}}
	// {{{ private function createEmbeddedWidgets()

	/**
	 * Creates all internal widgets required for this date entry
	 */
	private function createEmbeddedWidgets()
	{
		if (!$this->widgets_created) {
			if ($this->display_parts & self::YEAR)
				$this->createYearFlydown();

			if ($this->display_parts & self::MONTH)
				$this->createMonthFlydown();

			if ($this->display_parts & self::DAY)
				$this->createDayFlydown();

			if ($this->display_parts & self::TIME)
				$this->createTimeEntry();

			if ($this->display_parts & self::CALENDAR)
				$this->createCalendar();

			$this->widgets_created = true;
		}
	}

	// }}}
	// {{{ private function createYearFlydown()

	/**
	 * Creates the year flydown for this date entry
	 */
	private function createYearFlydown()
	{
		$this->year_flydown = new SwatFlydown($this->id.'_year');
		$this->year_flydown->parent = $this;

		$start_year = $this->valid_range_start->getYear();

		$tmp = clone $this->valid_range_end;
		$tmp->subtractSeconds(1);
		$end_year = $tmp->getYear();

		for ($i = $start_year; $i <= $end_year; $i++)
			$this->year_flydown->addOption($i, $i);
	}

	// }}}
	// {{{ private function createMonthFlydown()

	/**
	 * Creates the month flydown for this date entry
	 */
	private function createMonthFlydown()
	{
		$this->month_flydown = new SwatFlydown($this->id.'_month');
		$this->month_flydown->parent = $this;

		$start_year = $this->valid_range_start->getYear();
		$tmp = clone $this->valid_range_end;
		$tmp->subtractSeconds(1);
		$end_year = $tmp->getYear();

		if ($end_year == $start_year) {

			$start_month = $this->valid_range_start->getMonth();
			$end_month = $this->valid_range_end->getMonth();

			for ($i = $start_month; $i <= $end_month; $i++)
				$this->month_flydown->addOption($i,
					$this->getMonthOptionText($i));

		} elseif (($end_year - $start_year) == 1) {

			$start_month = $this->valid_range_start->getMonth();
			$end_month = $this->valid_range_end->getMonth();

			for ($i = $start_month; $i <= 12; $i++)
				$this->month_flydown->addOption($i,
					$this->getMonthOptionText($i));

			for ($i = 1; $i <= $end_month; $i++)
				$this->month_flydown->addOption($i,
					$this->getMonthOptionText($i));

		} else {

			for ($i = 1; $i <= 12; $i++)
				$this->month_flydown->addOption($i,
					$this->getMonthOptionText($i));
		}
	}

	// }}}
	// {{{ private function getMonthOptionText()

	/**
	 * Gets the title of a month flydown option
	 *
	 * @param integer $month the numeric identifier of the month.
	 *
	 * @return string the option text of the month.
	 */
	private function getMonthOptionText($month)
	{
		$text = '';

		if ($this->show_month_number)
			$text.= str_pad($month, 2, '0', STR_PAD_LEFT).' - ';

		$text.= Date_Calc::getMonthFullName($month);

		return $text;
	}

	// }}}
	// {{{ private function createDayFlydown()

	/**
	 * Creates the day flydown for this date entry
	 */
	private function createDayFlydown()
	{
		$this->day_flydown = new SwatFlydown($this->id.'_day');
		$this->day_flydown->parent = $this;

		$start_year  = $this->valid_range_start->getYear();

		$tmp = clone $this->valid_range_end;
		$tmp->subtractSeconds(1);
		$end_year = $tmp->getYear();

		$start_month = $this->valid_range_start->getMonth();
		$end_month   = $this->valid_range_end->getMonth();

		$end_check = clone $this->valid_range_start;
		$end_check->addSeconds(2678400); // add 31 days

		if ($start_year == $end_year && $start_month == $end_month) {

			$start_day = $this->valid_range_start->getDay();
			$end_day   = $this->valid_range_end->getDay();

			for ($i = $start_day; $i <= $end_day; $i++)
				$this->day_flydown->addOption($i, $i);

		} elseif (Date::compare($end_check, $this->valid_range_end, true) != -1) {

			$start_day = $this->valid_range_start->getDay();
			$end_day   = $this->valid_range_end->getDay();
			$days_in_month = $this->valid_range_start->getDaysInMonth();

			for ($i = $start_day; $i <= $days_in_month; $i++)
				$this->day_flydown->addOption($i, $i);

			for ($i = 1; $i <= $end_day; $i++)
				$this->day_flydown->addOption($i, $i);

		} else {
			for ($i = 1; $i <= 31; $i++)
				$this->day_flydown->addOption($i, $i);
		}
	}

	// }}}
	// {{{ private function createTimeEntry()

	/**
	 * Creates the time entry widget for this date entry
	 */
	private function createTimeEntry()
	{
		require_once 'Swat/SwatTimeEntry.php';
		$this->time_entry = new SwatTimeEntry($this->id.'_time_entry');
		$this->time_entry->parent = $this;
	}

	// }}}
	// {{{ private function createCalendar()

	/**
	 * Creates the calendar widget for this date entry
	 */
	private function createCalendar()
	{
		require_once 'Swat/SwatCalendar.php';
		$this->calendar = new SwatCalendar($this->id.'_calendar');
		$this->calendar->parent = $this;
		$this->calendar->valid_range_start = $this->valid_range_start;
		$this->calendar->valid_range_end   = $this->valid_range_end;
	}

	// }}}
	// {{{ private function getFormattedDate()

	/**
	 * Formats a date for this date entry
	 *
	 * Returns a date string formatted according to the properties of this
	 * date entry widget. This is used primarily for returning formatted
	 * valid start and valid end dates for user error messages.
	 *
	 * @param Date $date the date object to format.
	 *
	 * @return string a date formatted according to the properties of this date
	 *                 entry.
	 */
	private function getFormattedDate($date)
	{
		$time  = '';
		$day   = '';
		$month = '';
		$year  = '';

		if ($this->display_parts & self::TIME)
			$time = ' %I:%M %p';

		if ($this->display_parts & self::DAY)
			$day = ' %d';

		if ($this->display_parts & self::MONTH)
			$month = ' %B';

		if ($this->display_parts & self::YEAR)
			$year = ' %Y';

		return trim($date->format($month.$day.$year.$time));
	}

	// }}}
	// {{{ private function getDatePartOrder()

	/**
	 * Gets the order of date parts for the current locale
	 *
	 * Note: The technique used within this method does not work correcty for
	 * RTL languages that display month names, month abbreviations or weekday
	 * names. Since we're displaying months textually these locales may have
	 * date parts incorrectly ordered.
	 *
	 * @return array an array containg the values 'd', 'm' and 'y' in the
	 *                correct order for the current locale.
	 */
	private function getDatePartOrder()
	{
		$format = nl_langinfo(D_FMT);

		// expand short form format
		$format = str_replace('%D', '%m/%d/%y', $format);

		$day = $month = $year = null;

		$matches = array();
		if (preg_match('/(%d|%e)/', $format, $matches,
			PREG_OFFSET_CAPTURE) == 1)
			$day = $matches[0][1];

		$matches = array();
		if (preg_match('/(%[bB]|%m)/', $format, $matches,
			PREG_OFFSET_CAPTURE) == 1)
			$month = $matches[0][1];

		$matches = array();
		if (preg_match('/(%[Yy])/', $format, $matches,
			PREG_OFFSET_CAPTURE) == 1)
			$year = $matches[0][1];

		if ($day === null  || $month === null || $year === null) {
			// fallback to d-m-y if the locale format is unknown
			$order = array('d', 'm', 'y');
		} else {
			$order = array();
			$order[$day] = 'd';
			$order[$month] = 'm';
			$order[$year] = 'y';
			ksort($order);
		}

		return $order;
	}

	// }}}
}

?>
