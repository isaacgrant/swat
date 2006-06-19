<?php

require_once 'Swat/SwatCellRenderer.php';
require_once 'Swat/SwatHtmlTag.php';

/**
 * A link cell renderer
 *
 * @package   Swat
 * @copyright 2004-2006 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
class SwatLinkCellRenderer extends SwatCellRenderer
{
	// {{{ public properties

	/**
	 * The href attribute in the XHTML anchor tag
	 *
	 * Optionally uses vsprintf() syntax, for example:
	 * <code>
	 * $renderer->link = 'MySection/MyPage/%s?id=%s';
	 * </code>
	 *
	 * @var string
	 *
	 * @see SwatLinkCellRenderer::$link_value
	 */
	public $link;

	/**
	 * The visible content to place within the XHTML anchor tag
	 *
	 * Optionally uses vsprintf() syntax, for example:
	 * <code>
	 * $renderer->text = 'Page %s of %s';
	 * </code>
	 *
	 * @var string
	 *
	 * @see SwatLinkCellRenderer::$value
	 */
	public $text = '';

	/**
	 * Optional content type
	 *
	 * Default text/plain, use text/xml for XHTML fragments.
	 *
	 * @var string
	 */
	public $content_type = 'text/plain';

	/**
	 * A value or array of values to substitute into the text of this cell
	 *
	 * The value property may be specified either as an array of values or as
	 * a single value. If an array is passed, a call to vsprintf() is done
	 * on the {@link SwatLinkCellRenderer::$text} property. If the value
	 * is a string a single sprintf() call is made.
	 *
	 * @var mixed
	 *
	 * @see SwatLinkCellRenderer::$text
	 */
	public $value = null;

	/**
	 * A value or array of values to substitute into the link of this cell
	 *
	 * @var mixed
	 *
	 * @see SwatLinkCellRenderer::$link, SwatLinkCellRenderer::$value
	 */
	public $link_value = null;

	// }}}
	// {{{ protected properties

	/**
	 * Additional CSS class to use for this link cell renderer
	 *
	 * This class is used on the XHTML anchor tag.
	 *
	 * @var string
	 */
	protected $class = null;

	// }}}
	// {{{ public function render()

	/**
	 * Renders the contents of this cell
	 *
	 * @see SwatCellRenderer::render()
	 */
	public function render()
	{
		if ($this->sensitive && ($this->link !== null)) {
			$anchor = new SwatHtmlTag('a');
			$anchor->setContent($this->getText(), $this->content_type);
			$anchor->href = $this->getLink();
			$anchor->title = $this->getTitle();
			$anchor->class = $this->class;

			$anchor->display();
		} else {
			$span_tag = new SwatHtmlTag('span');
			$span_tag->setContent($this->getText(), $this->content_type);
			$span_tag->title = $this->getTitle();
			$span_tag->class = 'swat-link-cell-renderer-insensitive';
			if ($this->class !== null)
				$span_tag->class.= ' '.$this->class;

			$span_tag->display();
		}
	}

	// }}}
	// {{{ protected function getTitle()

	protected function getTitle()
	{
		return null;
	}

	// }}}
	// {{{ function getText()

	protected function getText()
	{
		if ($this->value === null)
			$text = $this->text;
		elseif (is_array($this->value))
			$text = vsprintf($this->text, $this->value);
		else
			$text = sprintf($this->text, $this->value);

		return $text;
	}

	// }}}
	// {{{ function getLink()

	protected function getLink()
	{
		if ($this->link_value === null)
			$link = $this->link;
		elseif (is_array($this->link_value))
			$link = vsprintf($this->link, $this->link_value);
		else
			$link = sprintf($this->link, $this->link_value);

		return $link;
	}

	// }}}
}

?>
