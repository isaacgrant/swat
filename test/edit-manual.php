<?
require('header.php');

// TODO: Write an autoloader for this.
require_once('Swat/SwatForm.php');
require_once('Swat/SwatFrame.php');
require_once('Swat/SwatEntry.php');
require_once('Swat/SwatTextarea.php');
require_once('Swat/SwatCheckbox.php');
require_once('Swat/SwatButton.php');
require_once('Swat/SwatFlydown.php');
require_once('Swat/SwatFormField.php');

// create the top-level widget
$frame = new SwatFrame('frame1');
$frame->title = 'New Weblog Post Example Form';

// create a form widget and add it to the frame
$form = new SwatForm('form1');
$frame->add($form);

// create an entry widget and add it
$title = new SwatEntry('title');
$title->required = true;
$form->addWithField($title, 'Title');

// create an entry widget and add it
$field = new SwatFormField();
$entry = new SwatEntry('entry1');
$entry->required = true;
$field->add($entry);
$field->title = "First Name";
$form->add($field);

// create a checkbox widget and add it
$hidden = new SwatCheckbox('hidden');
$form->addWithField($hidden, 'Hidden?');

// create a textarea widget and add it
$bodytext = new SwatTextarea('bodytext');
$bodytext->required = true;
$form->addWithField($bodytext, 'Body Text');

// create a textarea widget and add it
$moretext = new SwatTextarea('moretext');
$form->addWithField($moretext, 'More Text');

// create a flydown widget and add it
$fly = new SwatFlydown('replystatus');
$fly->options = array(0 => 'Normal', 1 => 'Hidden');
$fly->selected_value = 0;
$form->addWithField($fly, 'Reply Status');

// create a checkbox widget and add it
$ping = new SwatCheckbox('ping');
$form->addWithField($ping, 'Ping Weblogs.com?');

// create a button widget and add it
$field = new SwatFormField();
$field->class = 'swat-form-footer';
$btn = new SwatButton('btn_create');
$btn->title = 'Create';
$field->add($btn);
$form->add($field);

if ($form->process()) {
	echo '<pre>';
	print_r($_POST);
	echo '</pre>';
}

$frame->displayTidy();

require('footer.php');
?>
