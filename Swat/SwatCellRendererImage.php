<?php
require_once('Swat/SwatCellRenderer.php');

/**
 * An image renderer
 *
 * @package Swat
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @copyright silverorange 2004
 */
class SwatCellRendererImage extends SwatCellRenderer {

	/**
	 * Image src
	 *
	 * The src attribute in the HTML img tag.
	 * @var string
	 */
	public $src;

	/**
	 * Image height
	 *
	 * The height attribute in the HTML img tag.
	 * @var int
	 */
	public $height = 0;

	/**
	 * Image width
	 *
	 * The width attribute in the HTML img tag.
	 * @var int
	 */
	public $width = 0;

	/**
	 * Image title
	 *
	 * The title attribute in the HTML img tag.
	 * @var string
	 */
	public $title = '';

	/**
	 * Image alt text
	 *
	 * The alt attribute in the HTML img tag.
	 * @var string
	 */
	public $alt = '';

	public function render($prefix) {
		$image_tag = new SwatHtmlTag('img');
		$image_tag->src = $this->src;

		if ($this->height > 0)
			$image_tag->height = $this->height;

		if ($this->width > 0)
			$image_tag->width = $this->width;

		if (strlen($this->title) > 0)
			$image_tag->title = _S($this->title);

		if (strlen($this->alt) > 0)
			$image_tag->alt = _S($this->alt);

		$image_tag->display();
	}
}
