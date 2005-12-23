/**
 * An orderable list control widget
 *
 * Some of the drag and drop code is adapted from Nat Friedman's drag.js
 * script.
 *
 * @package   Swat
 * @copyright 2004-2005 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */

// {{{ function SwatChangeOrder_mousemoveEventHandler()

/**
 * Handles moving a dragged item
 *
 * Updates the position of the shadow item as well as updating the position
 * of the drop target.
 *
 * TODO: Fix when user uses mouse scrollwheel when dragging.
 *
 * @param DOMEvent event the event that triggered this function.
 *
 * @return boolean false.
 */
function SwatChangeOrder_mousemoveEventHandler(event)
{
	var shadow_item = SwatChangeOrder.dragging_item;
	var drop_marker = SwatChangeOrder.dragging_drop_marker;
	var list_div = shadow_item.original_item.parentNode;

	if (shadow_item.style.display == 'none') {
		SwatChangeOrder.is_dragging = true;
		shadow_item.style.display = 'block';
		shadow_item.scroll_timer =
			window.setInterval("SwatChangeOrder_scrollTimerHandler()", 100);

		shadow_item.update_timer =
			window.setInterval("SwatChangeOrder_updateTimerHandler()", 300);
	}

	if (typeof window.event == 'undefined') {
		var left = event.clientX - shadow_item.mouse_offset_x; 
		var top = event.clientY - shadow_item.mouse_offset_y +
			(shadow_item.original_item.offsetTop - list_div.scrollTop);
	} else {
		var left = window.event.clientX - shadow_item.mouse_offset_x; 
		var top = window.event.clientY - shadow_item.mouse_offset_y +
			(shadow_item.original_item.offsetTop - list_div.scrollTop);
	}

	var middle = top + Math.floor(shadow_item.offsetHeight / 2) -
		list_div.offsetTop + list_div.scrollTop;

	shadow_item.top = top;
	shadow_item.left = left;
	shadow_item.style.top = top + 'px';
	shadow_item.style.left = left + 'px';

	SwatChangeOrder_updateDropPosition();

	return false;
}

// }}}
// {{{ function SwatChangeOrder_keydownEventHandler()

/**
 * Handles keydown events for dragged items
 *
 * @param DOMEvent event
 *
 * @return boolean false;
 */
function SwatChangeOrder_keydownEventHandler(event)
{
	// user pressed escape
	if (event.keyCode == 27) {
		document.onmousemove = null;
		document.onmouseup = null;
		if (SwatChangeOrder.ie_event_model)
			document.detachEvent('onkeydown',
				SwatChangeOrder_keydownEventHandler);
		else
			document.removeEventListener('keydown',
				SwatChangeOrder_keydownEventHandler, false);

		var shadow_item = SwatChangeOrder.dragging_item;
		var drop_marker = SwatChangeOrder.dragging_drop_marker;
		var list_div = shadow_item.original_item.parentNode;

		window.clearInterval(shadow_item.timer);

		list_div.parentNode.removeChild(shadow_item);
		if (drop_marker.parentNode !== null)
			drop_marker.parentNode.removeChild(drop_marker);

		SwatChangeOrder.dragging_item = null;
		SwatChangeOrder.dragging_drop_marker = null;
		SwatChangeOrder.is_dragging = false;
	}

	return false;
}

// }}}
// {{{ function SwatChangeOrder_scrollTimerHandler()

/**
 * Handles auto-scrolling timeout events on a dragged item
 *
 * This does the auto scrolling of the main list
 */
function SwatChangeOrder_scrollTimerHandler()
{
	var shadow_item = SwatChangeOrder.dragging_item;
	var list_div = shadow_item.original_item.parentNode;

	var top = shadow_item.top;
	var middle = top + Math.floor(shadow_item.offsetHeight / 2);
	var old_scroll_top;

	// top hot spot scrolls list up
	if (middle > list_div.offsetTop &&
		middle < list_div.offsetTop + SwatChangeOrder.hotspot_height &&
		list_div.scrollTop > 0) {

		// hot spot is exponential
		var delta = Math.floor(
			Math.pow(SwatChangeOrder.hotspot_exponent,
			SwatChangeOrder.hotspot_height - middle + list_div.offsetTop));

		old_scroll_top = list_div.scrollTop;
		list_div.scrollTop -= delta;
		// update correctly if we scroll to the very top
		shadow_item.mouse_offset_y += (old_scroll_top - list_div.scrollTop);
	}

	var list_bottom = list_div.offsetHeight + list_div.offsetTop;

	// TODO: don't do this if the list is already at the bottom
	// bottom hot spot scrolls list down
	if (middle > list_bottom - SwatChangeOrder.hotspot_height &&
		middle < list_bottom) {

		// hot spot is exponential
		var delta = Math.floor(
			Math.pow(SwatChangeOrder.hotspot_exponent,
			SwatChangeOrder.hotspot_height - list_bottom + middle));

		old_scroll_top = list_div.scrollTop;
		list_div.scrollTop += delta;
		// update correctly if we scroll to the very bottom
		shadow_item.mouse_offset_y += (old_scroll_top - list_div.scrollTop);
	}
}

// }}}
// {{{ function SwatChangeOrder_updateTimerHandler()

/**
 * Handles position update timer events on a dragged item
 */
function SwatChangeOrder_updateTimerHandler()
{
	SwatChangeOrder_updateDropPosition();
}

// }}}
// {{{ function SwatChangeOrder_updateDropPosition()

/**
 * Updates the drop position of the current dragging item
 */
function SwatChangeOrder_updateDropPosition()
{
	var shadow_item = SwatChangeOrder.dragging_item;
	var drop_marker = SwatChangeOrder.dragging_drop_marker;
	var list_div = shadow_item.original_item.parentNode;
	var top = shadow_item.top;

	var middle = top + Math.floor(shadow_item.offsetHeight / 2) -
		list_div.offsetTop + list_div.scrollTop;

	for (var i = 0; i < list_div.childNodes.length; i++) {
		var node = list_div.childNodes[i];
		if (node !== drop_marker &&
			middle < node.offsetTop + Math.floor(node.offsetHeight / 2)) {
			// hide the drop marker if no move is taking place
			if (node === shadow_item.original_item ||
				node === shadow_item.original_item.nextSibling) {
				drop_marker.style.display = 'none';
			} else {
				node.parentNode.insertBefore(drop_marker, node);
				drop_marker.style.display = 'block';
			}

			break;
		}
	}
}

// }}}
// {{{ function SwatChangeOrder_mouseupEventHandler()

/**
 * Handles drop action on dragged items
 *
 * Updates the list order and destroys the shadow item and the target marker.
 * Also resets timers and event handlers.
 *
 * @param DOMEvent event the drop event.
 *
 * @return boolean false.
 */
function SwatChangeOrder_mouseupEventHandler(event)
{
	document.onmousemove = null;
	document.onmouseup = null;
	if (SwatChangeOrder.ie_event_model)
		document.detachEvent('onkeydown', SwatChangeOrder_keydownEventHandler);
	else
		document.removeEventListener('keydown',
			SwatChangeOrder_keydownEventHandler, false);

	var shadow_item = SwatChangeOrder.dragging_item;
	var drop_marker = SwatChangeOrder.dragging_drop_marker;
	var list_div = shadow_item.original_item.parentNode;

	window.clearInterval(shadow_item.scroll_timer);
	window.clearInterval(shadow_item.update_timer);

	// reposition the item
	// TODO: don't update this if the position is the same as originally
	if (drop_marker.parentNode !== null) {
		list_div.insertBefore(shadow_item.original_item, drop_marker);
		shadow_item.original_item.controller.updateValue();
	}

	list_div.parentNode.removeChild(shadow_item);

	if (drop_marker.parentNode !== null)
		drop_marker.parentNode.removeChild(drop_marker);

	SwatChangeOrder.dragging_item = null;
	SwatChangeOrder.dragging_drop_marker = null;
	SwatChangeOrder.is_dragging = false;
	
	return false;
}

// }}}
// {{{ function SwatChangeOrder_mousedownEventHandler()

/**
 * Handles an click event for an item in the list
 *
 * @param DOMEvent event the event to handle.
 *
 * @return boolean false.
 */
function SwatChangeOrder_mousedownEventHandler(event)
{
	// only allow left click to do things
	if ((SwatChangeOrder.ie_event_model && (window.event.button & 1) != 1) ||
		(!SwatChangeOrder.ie_event_model && event.button != 0))
		return false;

	if (!this.controller.sensitive)
		return false;

	// select the node
	this.controller.choose(this);

	// prime for dragging
	var shadow_item = this.cloneNode(true);
	shadow_item.original_item = this;
	shadow_item.original_item.parentNode.parentNode.appendChild(shadow_item, this);
	shadow_item.top = 0;

	// TODO: use zindex manager
	shadow_item.style.zIndex = 1000;
	shadow_item.style.display = 'none';
	shadow_item.className += ' swat-change-order-item-shadow';
	shadow_item.style.width = (this.offsetWidth - 4) + 'px';

	if (typeof window.event == 'undefined') {
		shadow_item.mouse_offset_x = event.clientX - this.offsetLeft -
			this.parentNode.offsetLeft;

		shadow_item.mouse_offset_y = event.clientY - this.parentNode.offsetTop;
	} else {
		shadow_item.mouse_offset_x = window.event.clientX - this.offsetLeft -
			this.parentNode.offsetLeft;

		shadow_item.mouse_offset_y = window.event.clientY -
			this.parentNode.offsetTop;
	}

	var drop_marker = document.createElement('div');
	drop_marker.style.borderBottomStyle = 'solid';
	drop_marker.style.borderBottomColor = '#000';
	drop_marker.style.borderBottomWidth = '1px';
	drop_marker.style.display = 'none';
	drop_marker.setAttribute('id', 'drop');
	
	SwatChangeOrder.dragging_item = shadow_item;
	SwatChangeOrder.dragging_drop_marker = drop_marker;

	document.onmousemove = SwatChangeOrder_mousemoveEventHandler;
	document.onmouseup = SwatChangeOrder_mouseupEventHandler;
	document.onmousedown = null;

	if (SwatChangeOrder.ie_event_model)
		document.attachEvent('onkeydown', SwatChangeOrder_keydownEventHandler);
	else
		document.addEventListener('keydown',
			SwatChangeOrder_keydownEventHandler, false);

	return false;
}

// }}}
// {{{ function SwatChangeOrder()

/**
 * An orderable list control widget
 *
 * @param string id the unique identifier of this object.
 * @param boolean sensitive the initial sensitive of this object.
 */
function SwatChangeOrder(id, sensitive)
{
	this.id = id;

	this.list_div = document.getElementById(this.id + '_list');
	this.buttons = document.getElementsByName(this.id + '_buttons');

	// the following two lines must be split on two lines to
	// handle a Firefox bug.
	var hidden_value = document.getElementById(this.id);
	var value_array = hidden_value.value.split(',');
	var count = 0;
	var node = null;
	
	// remove text nodes and set value on nodes
	for (var i = 0; i < this.list_div.childNodes.length; i++) {
		node = this.list_div.childNodes[i];
		if (node.nodeType == 3) {
			this.list_div.removeChild(node);
			i--;
		} else if (node.nodeType == 1) {
			node.order_value = value_array[count];
			node.order_index = count;
			// assign a back reference for event handlers
			node.controller = this;
			// add click handlers to the list items
			node.onmousedown = SwatChangeOrder_mousedownEventHandler;
			count++;
		}
	}

	// since the DOM only has an insertBefore() method we use a sentinel node
	// to make moving nodes down easier.
	var sentinel_node = document.createElement('div');
	sentinel_node.setAttribute('id', 'debug');
	sentinel_node.style.display = 'block';
	this.list_div.appendChild(sentinel_node);

	// while not a real semaphore, this does prevent the user from breaking
	// things by clicking buttons or items while an animation is occuring.
	this.semaphore = true;

	this.active_div = null;

	// this is hard coded to true so we can chose the first element
	this.sensitive = true;

	this.choose(this.list_div.firstChild);
	this.scrollList(this.getScrollPosition(this.list_div.firstChild));

	this.sensitive = sensitive;
}

// }}}
// {{{ static properties

/**
 * Height in pixels of auto-scroll hotspots
 *
 * @var number
 */
SwatChangeOrder.hotspot_height = 40;

/**
 * Exponential value to use to auto-scroll hotspots
 *
 * @var number
 */
SwatChangeOrder.hotspot_exponent = 1.15;

/**
 * Delay in milliseconds to use for animations
 *
 * @var number
 */
SwatChangeOrder.animation_delay = 10;

/**
 * The number of frames of animation to use
 *
 * @var number
 */
SwatChangeOrder.animation_frames = 5;

SwatChangeOrder.shadow_item_padding = 0;
SwatChangeOrder.dragging_item = null;
SwatChangeOrder.is_dragging = false;
SwatChangeOrder.ie_event_model = (document.addEventListener) ? false : true;

// }}}
// {{{ function SwatChangeOrder_staticMoveToTop()

/**
 * A static callback function for the move-to-top window timeout.
 *
 * @param SwatChangeOrder change_order the change-order widget to work with.
 * @param number steps the number of steps to skip when moving the active
 *                      element.
 */
function SwatChangeOrder_staticMoveToTop(change_order, steps)
{
	change_order.moveToTopHelper(steps);
}

// }}}
// {{{ function SwatChangeOrder_staticMoveToBottom()

/**
 * A static callback function for the move-to-bottom window timeout.
 *
 * @param SwatChangeOrder change_order the change-order widget to work with.
 * @param number steps the number of steps to skip when moving the active
 *                      element.
 */
function SwatChangeOrder_staticMoveToBottom(change_order, steps)
{
	change_order.moveToBottomHelper(steps);
}

// }}}
// {{{ choose()

/**
 * Choses an element in this change order as the active div
 *
 * Only allows chosing if the semaphore is not set.
 *
 * @param DOMNode div the element to chose.
 */
SwatChangeOrder.prototype.choose = function(div)
{
	if (this.semaphore && this.sensitive && div !== this.active_div &&
		!SwatChangeOrder.is_dragging) {

		if (this.active_div !== null)
			this.active_div.className = 'swat-change-order-item';

		div.className = 'swat-change-order-item swat-change-order-item-active';
		this.active_div = div;

		// update the index value of this element
		for (var i = 0; i < this.list_div.childNodes.length; i++) {
			if (this.list_div.childNodes[i] === this.active_div) {
				this.active_div.order_index = i;
				break;
			}
		}
	}
}

// }}}
// {{{ moveToTop()

/**
 * Moves the active element to the top of the list
 *
 * Only functions if the semaphore is not set. Sets the semaphore.
 */
SwatChangeOrder.prototype.moveToTop = function()
{
	if (this.semaphore && this.sensitive) {
		this.semaphore = false;
		this.setButtonsSensitive(false);

		var steps = Math.ceil(this.active_div.order_index /
			SwatChangeOrder.animation_frames);

		this.moveToTopHelper(steps);
	}
}

// }}}
// {{{ moveToTopHelper()

/**
 * A helper method that moves the active element up and sets a timeout callback
 * to move it up again until it reaches the top
 *
 * Unsets the semaphore after the active element is at the top.
 *
 * @param number steps the number of steps to skip when moving the active
 *                      element.
 */
SwatChangeOrder.prototype.moveToTopHelper = function(steps)
{
	if (this.moveUpHelper(steps)) {
		window.setTimeout('SwatChangeOrder_staticMoveToTop(' +
			this.id + '_obj, ' + steps + ');',
			SwatChangeOrder.animation_delay);
	} else {
		this.semaphore = true;
		this.setButtonsSensitive(true);
	}
}

// }}}
// {{{ moveToBottom()

/**
 * Moves the active element to the bottom of the list
 *
 * Only functions if the semaphore is not set. Sets the semaphore.
 */
SwatChangeOrder.prototype.moveToBottom = function()
{
	if (this.semaphore && this.sensitive) {
		this.semaphore = false;
		this.setButtonsSensitive(false);

		var steps = Math.ceil((this.list_div.childNodes.length - this.active_div.order_index - 1) /
			SwatChangeOrder.animation_frames);

		this.moveToBottomHelper(steps);
	}
}

// }}}
// {{{ moveToBottomHelper()

/**
 * A helper method that moves the active element down and sets a timeout
 * callback to move it down again until it reaches the bottom
 *
 * Unsets the semaphore after the active element is at the bottom.
 *
 * @param number steps the number of steps to skip when moving the active
 *                      element.
 */
SwatChangeOrder.prototype.moveToBottomHelper = function(steps)
{
	if (this.moveDownHelper(steps)) {
		window.setTimeout('SwatChangeOrder_staticMoveToBottom(' +
			this.id + '_obj, ' + steps + ');',
			SwatChangeOrder.animation_delay);
	} else {
		this.semaphore = true;
		this.setButtonsSensitive(true);
	}
}

// }}}
// {{{ moveUp()
	
/**
 * Moves the active element up one space
 *
 * Only functions if the semaphore is not set.
 */
SwatChangeOrder.prototype.moveUp = function()
{
	if (this.semaphore && this.sensitive)
		this.moveUpHelper(1);
}

// }}}
// {{{ moveDown()

/**
 * Moves the active element down one space
 *
 * Only functions if the semaphore is not set.
 */
SwatChangeOrder.prototype.moveDown = function()
{
	if (this.semaphore && this.sensitive)
		this.moveDownHelper(1);
}

// }}}
// {{{ moveUpHelper()

/**
 * Moves the active element up a number of steps
 *
 * @param number steps the number of steps to move the active element up by.
 *
 * @return boolean true if the element is not hitting the top of the list,
 *                  false otherwise.
 */
SwatChangeOrder.prototype.moveUpHelper = function(steps)
{
	// can't move the top of the list up
	if (this.list_div.firstChild === this.active_div)
		return false;

	var return_val = true;

	var prev_div = this.active_div;
	for (var i = 0; i < steps; i++) {
		prev_div = prev_div.previousSibling;
		if (prev_div === this.list_div.firstChild) {
			return_val = false;
			break;
		}
	}

	this.list_div.insertBefore(this.active_div, prev_div);

	this.active_div.order_index =
		Math.max(this.active_div.order_index - steps, 0);

	this.updateValue();
	this.scrollList(this.getScrollPosition(this.active_div));

	return return_val;
}

// }}}
// {{{ moveDownHelper()

/**
 * Moves the active element down a number of steps
 *
 * @param number steps the number of steps to move the active element down by.
 *
 * @return boolean true if the element is not hitting the bottom of the list,
 *                  false otherwise.
 */
SwatChangeOrder.prototype.moveDownHelper = function(steps)
{
	// can't move the bottom of the list down
	if (this.list_div.lastChild.previousSibling === this.active_div)
		return false;

	var return_val = true;

	var prev_div = this.active_div;
	for (var i = 0; i < steps + 1; i++) {
		prev_div = prev_div.nextSibling;
		if (prev_div === this.list_div.lastChild) {
			return_val = false;
			break;
		}
	}

	this.list_div.insertBefore(this.active_div, prev_div);

	// we take the minimum of the list length - 1 to get the highest index
	// and then - 1 again for the sentinel.
	this.active_div.order_index =
		Math.min(this.active_div.order_index + steps,
			this.list_div.childNodes.length - 2);

	this.updateValue();
	this.scrollList(this.getScrollPosition(this.active_div));

	return return_val;
}

// }}}
// {{{ setButtonsSensitive()

/**
 * Sets the sensitivity on buttons for this control
 *
 * @param boolean sensitive whether the buttons are sensitive.
 */
SwatChangeOrder.prototype.setButtonsSensitive = function(sensitive)
{
	for (var i = 0; i < this.buttons.length; i++)
		this.buttons[i].disabled = !sensitive;
}

// }}}
// {{{ setSensitive()

/**
 * Sets whether this control is sensitive
 *
 * @param boolean sensitive whether this control is sensitive.
 */
SwatChangeOrder.prototype.setSensitive = function(sensitive)
{
	this.setButtonsSensitive(sensitive);
	this.sensitive = sensitive;

	if (sensitive) {
		document.getElementById(this.id + '_control').className =
			'swat-change-order';
	} else {
		document.getElementById(this.id + '_control').className =
			'swat-change-order swat-change-order-insensitive';
	}
}

// }}}
// {{{ updateValue()

/**
 * Updates the value of the hidden field containing the ordering of elements
 */
SwatChangeOrder.prototype.updateValue = function()
{
	var temp = '';

	for (i = 0; i < this.list_div.childNodes.length; i++) {
		if (i > 0)
			temp = temp + ',';

		temp = temp + this.list_div.childNodes[i].order_value;
	}

	// update a hidden field with current order of keys
	document.getElementById(this.id).value = temp;
}

// }}}
// {{{ getScrollPosition()

/**
 * Gets the y-position of the active element in the scrolling section
 */
SwatChangeOrder.prototype.getScrollPosition = function(element)
{
	// this conditional is to fix behaviour in IE
	if (this.list_div.firstChild.offsetTop > this.list_div.offsetTop)
		var y_position = (element.offsetTop - this.list_div.offsetTop) +
			(element.offsetHeight / 2);
	else
		var y_position = element.offsetTop +
			(element.offsetHeight / 2);
	
	return y_position;
}

// }}}
// {{{ scrollList()

/**
 * Scrolls the list to a y-position
 *
 * This method acts the same as window.scrollTo() but it acts on a div instead
 * of the window.
 *
 * @param number y_coord the y value to scroll the list to in pixels.
 */
SwatChangeOrder.prototype.scrollList = function(y_coord)
{
	// clientHeight is the height of the visible scroll area
	var half_list_height = parseInt(this.list_div.clientHeight / 2);

	if (y_coord < half_list_height) {
		this.list_div.scrollTop = 0;
		return;
	}

	// scrollHeight is the height of the contents inside the scroll area
	if (this.list_div.scrollHeight - y_coord < half_list_height) {
		this.list_div.scrollTop = this.list_div.scrollHeight -
			this.list_div.clientHeight;

		return;
	}

	// offsetHeight is clientHeight + padding
	factor = (y_coord - half_list_height) /
		(this.list_div.scrollHeight - this.list_div.offsetHeight);
		
	this.list_div.scrollTop = Math.floor(
		(this.list_div.scrollHeight - this.list_div.clientHeight) * factor);
}

// }}}
