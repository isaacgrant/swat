//TODO: fix rteModeSource and how it's being checked with the new Source/Normal
//mode buttons plus add css for said buttons	

// Cross-Browser Rich Text Editor
// http://www.kevinroth.com/rte/demo.htm
// Written by Kevin Roth (kevin@NOSPAMkevinroth.com - remove NOSPAM)
// Visit the support forums at http://www.kevinroth.com/forums/index.php?c=2
// This code is public domain. Redistribution and use of this code, with or without modification, is permitted.

//init variables
var isRichText = false;
var rng;
var currentRTE;
var allRTEs = "";

var isIE;
var isGecko;
var isSafari;
var isKonqueror;

var imagesPath;
var includesPath;
var cssFile;
var generateXHTML;

var lang = "en";
var encoding = "utf-8";

function initRTE(imgPath, incPath, css, genXHTML) {
	//set browser vars
	var ua = navigator.userAgent.toLowerCase();
	isIE = ((ua.indexOf("msie") != -1) && (ua.indexOf("opera") == -1) && (ua.indexOf("webtv") == -1)); 
	isGecko = (ua.indexOf("gecko") != -1);
	isSafari = (ua.indexOf("safari") != -1);
	isKonqueror = (ua.indexOf("konqueror") != -1);
	
	generateXHTML = genXHTML;
	
	//check to see if designMode mode is available
	//Safari/Konqueror think they are designMode capable even though they are not
	if (document.getElementById && document.designMode && !isSafari && !isKonqueror) {
		isRichText = true;
	}
	
	//set paths vars
	imagesPath = imgPath;
	includesPath = incPath;
	cssFile = css;
	
	if (isRichText) document.writeln('<style type="text/css">@import "swat/swat-textarea-editor.css";</style>');
}

function writeRichText(rte, html, width, height, menu_type) {
	if (isRichText) {
		if (allRTEs.length > 0) allRTEs += ";";
		allRTEs += rte;
		
		
		//adjust minimum table widths
		if (isIE) {
			if (width < 540) width = 540;
			var tablewidth = width;
		} else {
			if (width < 540) width = 540;
			var tablewidth = width + 14;
		}
		
		document.writeln('<div class="rteDiv">');
		
		document.writeln('<div id="Menu_' + rte + '" class="rteMenu">');
		if (menu_type == 'formatting') {
			document.writeln('<div id="MenuFormatting_' + rte + '" class="rteMenuFormatting">');
			document.writeln('	<select id="formatblock_' + rte + '" onchange="selectFont(\'' + rte + '\', this.id);">');
			document.writeln('		<option value="">[Style]</option>');
			document.writeln('		<option value="<p>">Paragraph &lt;p&gt;</option>');
			document.writeln('		<option value="<h1>">Heading 1 &lt;h1&gt;</option>');
			document.writeln('		<option value="<h2>">Heading 2 &lt;h2&gt;</option>');
			document.writeln('		<option value="<h3>">Heading 3 &lt;h3&gt;</option>');
			document.writeln('		<option value="<h4>">Heading 4 &lt;h4&gt;</option>');
			document.writeln('		<option value="<h5>">Heading 5 &lt;h5&gt;</option>');
			document.writeln('		<option value="<h6>">Heading 6 &lt;h6&gt;</option>');
			document.writeln('		<option value="<address>">Address &lt;ADDR&gt;</option>');
			document.writeln('		<option value="<pre>">Formatted &lt;pre&gt;</option>');
			document.writeln('	</select>');
			document.writeln('	<select id="fontname_' + rte + '" onchange="selectFont(\'' + rte + '\', this.id)">');
			document.writeln('		<option value="Font" selected>[Font]</option>');
			document.writeln('		<option value="Arial, Helvetica, sans-serif">Arial</option>');
			document.writeln('		<option value="Courier New, Courier, mono">Courier New</option>');
			document.writeln('		<option value="Times New Roman, Times, serif">Times New Roman</option>');
			document.writeln('		<option value="Verdana, Arial, Helvetica, sans-serif">Verdana</option>');
			document.writeln('	</select>');
			document.writeln('	<select unselectable="on" id="fontsize_' + rte + '" onchange="selectFont(\'' + rte + '\', this.id);">');
			document.writeln('		<option value="Size">[Size]</option>');
			document.writeln('		<option value="1">1</option>');
			document.writeln('		<option value="2">2</option>');
			document.writeln('		<option value="3">3</option>');
			document.writeln('		<option value="4">4</option>');
			document.writeln('		<option value="5">5</option>');
			document.writeln('		<option value="6">6</option>');
			document.writeln('		<option value="7">7</option>');
			document.writeln('	</select>');
			document.writeln('</div>');
		}
		
		document.write('<div id="MenuButtons_' + rte + '" class="rteMenuButtons">');
		
		document.write('<div>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-bold"');
			document.write('onClick="rteCommand(\'' + rte + '\', \'bold\', \'\')"');
			document.write('title="bold">bold</a>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-italic"');
			document.write('onClick="rteCommand(\'' + rte + '\', \'italic\', \'\')"');
			document.write('title="italic">italic</a>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-underline"');
			document.write('onClick="rteCommand(\'' + rte + '\', \'underline\', \'\')"');
			document.write('title="underline">underline</a>');
		
		document.write('</div>');
		document.write('<div>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-align-left"');
			document.write('onClick="rteCommand(\'' + rte + '\', \'justifyleft\', \'\')"');
			document.write('title="underline">align left</a>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-align-center"');
			document.write('onClick="rteCommand(\'' + rte + '\', \'justifycenter\', \'\')"');
			document.write('title="underline">align center</a>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-align-right"');
			document.write('onClick="rteCommand(\'' + rte + '\', \'justifyright\', \'\')"');
			document.write('title="underline">align right</a>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-align-justify"');
			document.write('onClick="rteCommand(\'' + rte + '\', \'jusitfyfull\', \'\')"');
			document.write('title="underline">justify full</a>');
		
		document.write('</div>');
		document.write('<div>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-ul"');
			document.write('onClick="rteCommand(\'' + rte + '\', \'insertunorderedlist\', \'\')"');
			document.write('title="underline">insert unordered list</a>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-ol"');
			document.write('onClick="rteCommand(\'' + rte + '\', \'insertorderedlist\', \'\')"');
			document.write('title="underline">insert ordered list</a>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-indent"');
			document.write('onClick="rteCommand(\'' + rte + '\', \'indent\', \'\')"');
			document.write('title="underline">indent</a>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-outdent"');
			document.write('onClick="rteCommand(\'' + rte + '\', \'outdent\', \'\')"');
			document.write('title="underline">outdent</a>');
		
		document.write('</div>');
		document.write('<div>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-link"');
			document.write('onClick="dlgInsertLink(\'' + rte + '\', \'link\')"');
			document.write('title="underline">insert link</a>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-hr"');
			document.write('onClick="rteCommand(\'' + rte + '\', \'inserthorizontalrule\', \'\')"');
			document.write('title="underline">horizonatal rule</a>');
		
		document.write('</div>');
		document.write('<div>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-hilite"');
			document.write('onClick="hiliteText(\'' + rte + '\')"');
			document.write('title="underline">highlight</a>');
		
		document.write('<a href="javascript: return false;" class="rteMenu-quote"');
			document.write('onClick="insertQuote(\'' + rte + '\')"');
			document.write('title="underline">quote</a>');
		
		document.write('</div>');
	
		document.write('</div>');
		document.write('</div>');

		document.writeln('<iframe class="rteIframe" id="' + rte + '" name="' + rte + '" width="' + width + 'px" height="' + height + 'px" src="' + includesPath + 'swat-textarea-editor-blank.html"></iframe>');

		document.writeln('<div class="rteToggleMode">');	
			//document.writeln('<div id="_rteToggleNormal' + rte + '" />');
				document.writeln('<a class="rteToggleModeDisabled" id="_rteToggleNormal' + rte + '" href="javascript: return false;" onclick="toggleHTMLSrc(\'' + rte + '\', 0);"><span>Normal</span></a>');
			//document.writeln('</div>');
		
			//document.writeln('<div id="_rteToggleSource' + rte + '" />');
				document.writeln('<a href="javascript: return false;"n id="_rteToggleSource' + rte + '" onclick="toggleHTMLSrc(\'' + rte + '\', 1);"><span>Source</span></a>');
			//document.writeln('</div>');	
		document.writeln('</div>');	
		document.writeln('<input type="hidden" id="_rteModeSource' + rte + '" value="0" />');	

		//iframe for the palette - unremark this if you want a palette control
		//document.writeln('<iframe width="154" height="104" id="cp' + rte + '" src="' + includesPath + 'swat-textarea-editor-palette.html" marginwidth="0" marginheight="0" scrolling="no" style="visibility:hidden; position: absolute;"></iframe>');
		
		html = convertTags(html);
		document.writeln('<input type="hidden" id="hdn' + rte + '" name="' + rte + '" value="' + html + '">');

		document.writeln('</div>');
		
		enableDesignMode(rte, html);
	
	} else {
		document.writeln('<textarea name="' + rte + '" id="' + rte + '" style="width: ' + width + 'px; height: ' + height + 'px;">' + html + '</textarea>');
	}
}

function enableDesignMode(rte, html) {
	var frameHtml = "<html id=\"" + rte + "\">\n";
	frameHtml += "<head>\n";
	//to reference your stylesheet, set href property below to your stylesheet path and uncomment
	//if (cssFile.length > 0) {
		//frameHtml += "<link media=\"all\" type=\"text/css\" href=\"" + cssFile + "\" rel=\"Stylesheet\">\n";
	//} else {
		frameHtml += "<style>\n";
		frameHtml += "body {\n";
		frameHtml += "	background: #FFFFFF;\n";
		frameHtml += "	margin: 2px;\n";
		frameHtml += "	padding: 2px;\n";
		frameHtml += "}\n";
		frameHtml += ".highlight { background: #FF0; }";
		frameHtml += ".quote { border: 5px solid #CCC;}";
		frameHtml += "</style>\n";
	//}
	frameHtml += "</head>\n";
	frameHtml += "<body>\n";
	//frameHtml += html + "\n";
	frameHtml += "\n"; // content gets loaded onload
	frameHtml += "</body>\n";
	frameHtml += "</html>";
	
	
	if (document.all) {
		var oRTE = frames[rte].document;
		oRTE.open();
		oRTE.write(frameHtml);
		oRTE.close();
		oRTE.designMode = "On";
		frames[rte].document.attachEvent("onkeypress", function evt_ie_keypress(event) {ieKeyPress(event, rte);});
		appendFormOnSubmit(rte);
	} else {
		try {
			document.getElementById(rte).contentDocument.designMode = "on";
			try {
				var oRTE = document.getElementById(rte).contentWindow.document;
				oRTE.open();
				oRTE.write(frameHtml);
				oRTE.close();
				if (isGecko) {
					//attach a keyboard handler for gecko browsers to make keyboard shortcuts work
					oRTE.addEventListener("keypress", geckoKeyPress, true);
					
					//switch to non-CSS mode (inserts <tag></tag> instead of <span style=""><span>)
					oRTE.execCommand("useCSS", false, true);
					appendFormOnSubmit(rte);
				}
			} catch (e) {
				//TODO: figure out why this started being thrown when I removed
				//image buttons
				window.alert(e);
				alert("Error preloading content.");
			}
		} catch (e) {
			//gecko may take some time to enable design mode.
			//Keep looping until able to set.
			if (isGecko) {
				setTimeout("enableDesignMode('" + rte + "', '" + html + "');", 10);
			} else {
				return false;
			}
		}
	}
}

function appendFormOnSubmit(rte) {
	var hdn_field = document.getElementById('hdn' + rte);
	var myform = hdn_field.form;

	if (typeof myform.onsubmit == "function") {
		var myform_onsubmit = myform.onsubmit;
		if (typeof myform.__msh_prevOnSubmit == "undefined")
			myform.__msh_prevOnSubmit = [];
			myform.__msh_prevOnSubmit.push(myform_onsubmit);
	}

	myform.onsubmit = function() {
		setHiddenVal(rte);
		
		var prev_elements = this.__msh_prevOnSubmit;
		if (typeof prev_elements != "undefined")
			for (var elem in prev_elements)
				prev_elements[elem]();
	};
}


function setHiddenVal(rte) {
	//set hidden form field value for current rte
	var oHdnField = document.getElementById('hdn' + rte);
	
	//convert html output to xhtml (thanks Timothy Bell and Vyacheslav Smolin!)
	if (oHdnField.value == null) oHdnField.value = "";
	if (document.all) {
		if (generateXHTML) {
			oHdnField.value = get_xhtml(frames[rte].document.body, lang, encoding);
		} else {
			oHdnField.value = frames[rte].document.body.innerHTML;
		}
	} else {
		if (generateXHTML) {
			oHdnField.value = get_xhtml(document.getElementById(rte).contentWindow.document.body, lang, encoding);
		} else {
			oHdnField.value = document.getElementById(rte).contentWindow.document.body.innerHTML;
		}
	}
	
	oHdnField.value = convertTags(oHdnField.value);

	//if there is no content (other than formatting) set value to nothing
	if (stripHTML(oHdnField.value.replace("&nbsp;", " ")) == "" &&
		oHdnField.value.toLowerCase().search("<hr") == -1 &&
		oHdnField.value.toLowerCase().search("<img") == -1) oHdnField.value = "";
}

function rteCommand(rte, command, option) {	
	if (document.getElementById("_rteModeSource" + rte).value == 1)
		return;
	
	//function to perform command
	var oRTE;
	if (document.all) {
		oRTE = frames[rte];
	} else {
		oRTE = document.getElementById(rte).contentWindow;
	}
	
	try {
		oRTE.focus();
	  	oRTE.document.execCommand(command, false, option);
		oRTE.focus();
	} catch (e) {
//		alert(e);
//		setTimeout("rteCommand('" + rte + "', '" + command + "', '" + option + "');", 10);
	}
}

function toggleHTMLSrc(rte, edit_src) {

	if (document.getElementById("_rteModeSource" + rte).value == edit_src)
		return false;

	//contributed by Bob Hutzel (thanks Bob!)
	var oHdnField = document.getElementById('hdn' + rte);
	
	document.getElementById("_rteModeSource" + rte).value = edit_src;

	if (edit_src) {
		//we are checking the box
		var element = document.getElementById("MenuButtons_" + rte);
		element.className = 'rteMenuButtons rteMenuButtonsDisabled';

		document.getElementById('_rteToggleSource' + rte).className = 'rteToggleModeDisabled';
		document.getElementById('_rteToggleNormal' + rte).className = null;

		toggleFormatting(rte);
		setHiddenVal(rte);
		
		if (document.all) {
			frames[rte].document.body.innerText = oHdnField.value;
		} else {
			var oRTE = document.getElementById(rte).contentWindow.document;
			var htmlSrc = oRTE.createTextNode(oHdnField.value);
			oRTE.body.innerHTML = "";
			oRTE.body.appendChild(htmlSrc);
		}
	} else {
		//we are unchecking the box
		var element = document.getElementById("MenuButtons_" + rte);
		element.className = 'rteMenuButtons';
		
		document.getElementById('_rteToggleNormal' + rte).className = 'rteToggleModeDisabled';
		document.getElementById('_rteToggleSource' + rte).className = null;
		
		toggleFormatting(rte);
		
		if (document.all) {
			//fix for IE
			var output = escape(frames[rte].document.body.innerText);
			output = output.replace("%3CP%3E%0D%0A%3CHR%3E", "%3CHR%3E");
			output = output.replace("%3CHR%3E%0D%0A%3C/P%3E", "%3CHR%3E");
			frames[rte].document.body.innerHTML = unescape(output);
		} else {
			var oRTE = document.getElementById(rte).contentWindow.document;
			var htmlSrc = oRTE.body.ownerDocument.createRange();
			htmlSrc.selectNodeContents(oRTE.body);
			oRTE.body.innerHTML = htmlSrc.toString();
		}
	}

	return false;
}

function dlgColorPalette(rte, command) {
	if (document.getElementById("_rteModeSource" + rte).value == 1)
		return;

	//function to display or hide color palettes
	setRange(rte);
	
	//get dialog position
	var oDialog = document.getElementById('cp' + rte);
	var buttonElement = document.getElementById(command + '_' + rte);
	var iLeftPos = getOffsetLeft(buttonElement);
	var iTopPos = getOffsetTop(buttonElement) + (buttonElement.offsetHeight + 4);
	oDialog.style.left = (iLeftPos) + "px";
	oDialog.style.top = (iTopPos) + "px";
	
	if ((command == parent.command) && (rte == currentRTE)) {
		//if current command dialog is currently open, close it
		if (oDialog.style.visibility == "hidden") {
			showHideElement(oDialog, 'show');
		} else {
			showHideElement(oDialog, 'hide');
		}
	} else {
		//if opening a new dialog, close all others
		var vRTEs = allRTEs.split(";");
		for (var i = 0; i < vRTEs.length; i++) {
			showHideElement('cp' + vRTEs[i], 'hide');
		}
		showHideElement(oDialog, 'show');
	}
	
	//save current values
	parent.command = command;
	currentRTE = rte;
}

function dlgInsertTable(rte, command) {
	//function to open/close insert table dialog
	//save current values
	
	if (document.getElementById("_rteModeSource" + rte).value == 1)
		return;
	
	parent.command = command;
	currentRTE = rte;
	InsertTable = popUpWin(includesPath + 'swat-textarea-editor-insert-table.html', 'InsertTable', 360, 180, '');
}

function dlgInsertLink(rte, command) {
	//function to open/close insert table dialog
	//save current values
	
	if (document.getElementById("_rteModeSource" + rte).value == 1)
		return;
	
	parent.command = command;
	currentRTE = rte;
	InsertLink = popUpWin(includesPath + 'swat-textarea-editor-insert-link.html', 'InsertLink', 360, 180, '');
	
	//get currently highlighted text and set link text value
	setRange(rte);
	var linkText = '';
	if (isIE) {
		linkText = stripHTML(rng.htmlText);
	} else {
		linkText = stripHTML(rng.toString());
	}
	setLinkText(linkText);
}

function setLinkText(linkText) {
	//set link text value in insert link dialog
	try {
		window.InsertLink.document.linkForm.linkText.value = linkText;
	} catch (e) {
		//may take some time to create dialog window.
		//Keep looping until able to set.
		setTimeout("setLinkText('" + linkText + "');", 10);
	}
}

function popUpWin(url, win, width, height, options) {
	var leftPos = (screen.availWidth - width) / 2;
	var topPos = (screen.availHeight - height) / 2;
	options += 'width=' + width + ',height=' + height + ',left=' + leftPos + ',top=' + topPos;
	return window.open(url, win, options);
}

function setColor(color) {
	//function to set color
	var rte = currentRTE;
	var parentCommand = parent.command;

	if (document.all) {
		if (parentCommand == "hilitecolor") parentCommand = "backcolor";
		
		//retrieve selected range
		rng.select();
	}
	
	if (parentCommand == "hilitecolor")
		setBackgroundColor(color);
	else
		rteCommand(rte, parentCommand, color);
	
	showHideElement('cp' + rte, "hide");
}

function setBackgroundColor(rte, color) {
	rng = setRange(rte);
	
	if (document.all) {
		//retrieve selected range
		rng.select();
		rteCommand(rte, 'backcolor', color);
	} else {
		//with css mode turned off - this manually inserts a colored span
		//instead (otherwise it just doesn't work)
		var node = document.createElement('span');
		node.style.backgroundColor = color;
		rng.surroundContents(node);
	}
}

function addImage(rte) {
	//function to add image
	
	if (document.getElementById("_rteModeSource" + rte).value == 1)
		return;
	
	imagePath = prompt('Enter Image URL:', 'http://');				
	if ((imagePath != null) && (imagePath != "")) {
		rteCommand(rte, 'InsertImage', imagePath);
	}
}

function hiliteText(rte) {
	currentRTE = rte;
	
	var content = getHTMLOfSelection(rte);
	insertHTML('<span class="highlight">' + content + '</span>');
}

function insertQuote(rte) {
	currentRTE = rte;
	
	var content = getHTMLOfSelection(rte);
	insertHTML('<div class="quote">' + content + '</div><br />');
}

// Ernst de Moor: Fix the amount of digging parents up, in case the RTE editor itself is displayed in a div.
// KJR 11/12/2004 Changed to position palette based on parent div, so palette will always appear in proper location regardless of nested divs
function getOffsetTop(elm) {
	var mOffsetTop = elm.offsetTop;
	var mOffsetParent = elm.offsetParent;
	var parents_up = 1; //the positioning div is 2 elements up the tree
	
	while(parents_up > 0) {
		mOffsetTop += mOffsetParent.offsetTop;
		mOffsetParent = mOffsetParent.offsetParent;
		parents_up--;
	}
	
	return mOffsetTop;
}

// Ernst de Moor: Fix the amount of digging parents up, in case the RTE editor itself is displayed in a div.
// KJR 11/12/2004 Changed to position palette based on parent div, so palette will always appear in proper location regardless of nested divs
function getOffsetLeft(elm) {
	var mOffsetLeft = elm.offsetLeft;
	var mOffsetParent = elm.offsetParent;
	var parents_up = 1;
	
	while(parents_up > 0) {
		mOffsetLeft += mOffsetParent.offsetLeft;
		mOffsetParent = mOffsetParent.offsetParent;
		parents_up--;
	}
	
	return mOffsetLeft;
}

function selectFont(rte, selectname) {
	//function to handle font changes
	var idx = document.getElementById(selectname).selectedIndex;
	// First one is always a label
	if (idx != 0) {
		var selected = document.getElementById(selectname).options[idx].value;
		var cmd = selectname.replace('_' + rte, '');
		rteCommand(rte, cmd, selected);
		document.getElementById(selectname).selectedIndex = 0;
	}
}

function insertHTML(html) {
	//function to add HTML -- thanks dannyuk1982
	var rte = currentRTE;
	
	var oRTE;
	if (document.all) {
		oRTE = frames[rte];
	} else {
		oRTE = document.getElementById(rte).contentWindow;
	}
	
	oRTE.focus();
	if (document.all) {
		var oRng = oRTE.document.selection.createRange();
		oRng.pasteHTML(html);
		oRng.collapse(false);
		oRng.select();
	} else {
		oRTE.document.execCommand('insertHTML', false, html);
	}
}

function showHideElement(element, showHide) {
	//function to show or hide elements
	//element variable can be string or object
	if (document.getElementById(element)) {
		element = document.getElementById(element);
	}
	
	if (showHide == "show") {
		element.style.visibility = "visible";
	} else if (showHide == "hide") {
		element.style.visibility = "hidden";
	}
}

function setRange(rte) {
	//function to store range of current selection
	var oRTE;
	if (document.all) {
		oRTE = frames[rte];
		var selection = oRTE.document.selection; 
		if (selection != null) rng = selection.createRange();
	} else {
		oRTE = document.getElementById(rte).contentWindow;
		var selection = oRTE.getSelection();
		rng = selection.getRangeAt(selection.rangeCount - 1).cloneRange();
	}
	return rng;
}

function stripHTML(oldString) {
	//function to strip all html
	var newString = oldString.replace(/(<([^>]+)>)/ig,"");
	
	//replace carriage returns and line feeds
   newString = newString.replace(/\r\n/g," ");
   newString = newString.replace(/\n/g," ");
   newString = newString.replace(/\r/g," ");
	
	//trim string
	newString = trim(newString);
	
	return newString;
}

function clearFormat(rte) {
	currentRTE = rte;
	insertHTML(stripHTML(getHTMLOfSelection(rte)));
}

function trim(inputString) {
   // Removes leading and trailing spaces from the passed string. Also removes
   // consecutive spaces and replaces it with one space. If something besides
   // a string is passed in (null, custom object, etc.) then return the input.
   if (typeof inputString != "string") return inputString;
   var retValue = inputString;
   var ch = retValue.substring(0, 1);
	
   while (ch == " ") { // Check for spaces at the beginning of the string
      retValue = retValue.substring(1, retValue.length);
      ch = retValue.substring(0, 1);
   }
   ch = retValue.substring(retValue.length - 1, retValue.length);
	
   while (ch == " ") { // Check for spaces at the end of the string
      retValue = retValue.substring(0, retValue.length - 1);
      ch = retValue.substring(retValue.length - 1, retValue.length);
   }
	
	// Note that there are two spaces in the string - look for multiple spaces within the string
   while (retValue.indexOf("  ") != -1) {
		// Again, there are two spaces in each of the strings
      retValue = retValue.substring(0, retValue.indexOf("  ")) + retValue.substring(retValue.indexOf("  ") + 1, retValue.length);
   }
   return retValue; // Return the trimmed string back to the user
}

function getHTMLOfSelection(rte) {
	var range = setRange(rte);
	
	if (document.all) {
		return range.htmlText;
	} else {
		var clonedSelection = range.cloneContents();
		var div = document.createElement('div');
		div.appendChild(clonedSelection);
		return div.innerHTML;
	}
}



//********************
//Gecko-Only Functions
//********************
function geckoKeyPress(evt) {
	//function to add bold, italic, and underline shortcut commands to gecko RTEs
	//contributed by Anti Veeranna (thanks Anti!)
	var rte = evt.target.id;
	
	if (evt.ctrlKey) {
		var key = String.fromCharCode(evt.charCode).toLowerCase();
		var cmd = '';
		switch (key) {
			case 'b': cmd = "bold"; break;
			case 'i': cmd = "italic"; break;
			case 'u': cmd = "underline"; break;
		};

		if (cmd) {
			rteCommand(rte, cmd, null);
			
			// stop the event bubble
			evt.preventDefault();
			evt.stopPropagation();
		}
 	}
}



//*****************
//IE-Only Functions
//*****************
function ieKeyPress(evt, rte) {
	var key = (evt.which || evt.charCode || evt.keyCode);
	var stringKey = String.fromCharCode(key).toLowerCase();
}





function raiseButton(e) {
	var el = window.event.srcElement;
	
	if (isMenuButton(el))
		el.className = 'rteImageRaised';
}

function normalButton(e) {
	var el = window.event.srcElement;
	
	if (isMenuButton(el))
		el.className = 'rteImage';
}

function lowerButton(e) {
	var el = window.event.srcElement;
	
	if (isMenuButton(el))
		el.className = 'rteImageLowered';
}

function isMenuButton(el) {
	className = el.className;

	if (el.parentNode.className.length)
		parentClassName = el.parentNode.className;
	else if (el.parentNode.parentNode)
		parentClassName = el.parentNode.parentNode.className;
	else
		parentClassName = '';

	return (parentClassName == 'rteMenuButtons' && className != 'rteVertSep');
}


function convertTags(value) {
	value = convertTag('em', 'b', value);
	value = convertTag('strong', 'i', value);
	return value;
}


function convertTag(tag1, tag2, value) {
	myRegExp = new RegExp('<' + tag1 + '>', 'gi');
	value = value.replace(myRegExp, "<" + tag2 + ">");

	myRegExp = new RegExp('</' + tag1 + '>', 'gi');
	value = value.replace(myRegExp, "</" + tag2 + ">");

	return value;
}

function toggleFormatting(rte) {
	var element = document.getElementById("MenuFormatting_" + rte);
	if (element)
		for (var i = 0; i < element.childNodes.length; i++)
			if (element.childNodes[i].nodeName == 'SELECT')
				element.childNodes[i].disabled = (document.getElementById("_rteModeSource" + rte).value == 1);
}

window.onload = function(ev) {
	var vRTEs = allRTEs.split(";");
	for (var i = 0; i < vRTEs.length; i++) {
		currentRTE = vRTEs[i];
		
		var hidden_val = document.getElementById('hdn' + vRTEs[i]);

		if (hidden_val.value.toString())
			insertHTML(hidden_val.value.toString());
	}
};

window.onunload = function(ev) {
	var vRTEs = allRTEs.split(";");
	for (var i = 0; i < vRTEs.length; i++)
		setHiddenVal(vRTEs[i])
};
