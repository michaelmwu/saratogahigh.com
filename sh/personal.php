<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Untitled Document</title>
<style type="text/css">
.bigbox { float: left; width: 200px; border: 1px solid #000000; margin: 5px; padding: 5px; background-color: #00FF00; }
.box { border: 1px solid #0000FF; position: static; width: 150px; background-color: #0033FF; opacity: 1; }
.title { background-color: #FF0000; }
</style>

<script type="text/javascript">
<!--
var deltaX;
var deltaY;
var div;

var bigboxes;
var boxes;

var boxclass = "box";
var bigboxclass = "bigbox";
var titleclass = "title";

var div = document.createElement("DIV");

div.style.display="none";
div.style.position="absolute";
div.style.cursor="move";
div.style.border="2px solid #999999";

Cursor = {};
Cursor.DEFAULT 	= "default";
Cursor.POINTER 	= "pointer";
Cursor.MOVE 	= "move";
Cursor.GRABBING = "-moz-grabbing";

Cursor.setCursor = function (type) {
	try {
		document.body.style.cursor = type;
	} catch (gulp) { }
};

 /** @constructor */
function DomUtils() {
	throw 'RuntimeException: DomUtils is a utility class with only static ' +
		' methods and may not be instantiated';
}		

/**
 *	@param string HTMLElement
 *	@param number x
 *	@param number y
 *	@return void
 */
DomUtils.setLocation = function (target,x,y) {
	target.style.left = x + "px";
	target.style.top  = y + "px";
};

/** @type Object */
DomUtils.Position = {};
/** @type String */
DomUtils.Position.STATIC   = "static";
/** @type String */
DomUtils.Position.RELATIVE = "relative";
/** @type String */
DomUtils.Position.ABSOLUTE = "absolute";

/**
 *	@param string HTMLElement
 *	@param String type
 *	@return void
 */
DomUtils.setPosition = function (target,type) {
	target.style.position = type;
};

/**
 *	@param string HTMLElement
 *	@return number
 */
DomUtils.setOpacity = function (target,percentage) {
	if (target.filters) {
		target.filters["alpha"] = percentage*100 + "";
	} else if (target.style.opacity) {
		target.style.opacity = "" + percentage;
	} else if (target.style.mozOpacity) {
		target.style.mozOpacity = "" + percentage;
	}
};

/**
 *	@param string HTMLElement
 *	@return number
 */
DomUtils.getX = function (target) {
	return parseInt(target.offsetLeft);
};

/**
 *	@param string HTMLElement
 *	@return number
 */
DomUtils.getY = function (target) {
	return parseInt(target.offsetTop);
};

/**
 *	@param string HTMLElement
 *	@return number
 */
DomUtils.getWidth = function (Elem) {
	return parseInt(Elem.offsetWidth);
};

/**
 *	@param string HTMLElement
 *	@return number
 */
DomUtils.getHeight = function (Elem) {
	return parseInt(Elem.offsetHeight);
};

/**
 *	@param string className
 *	@return Element
 */
DomUtils.getFirstAncestorByClassName = function (target,className) {
	var parent = target;
	while (parent = parent.parentNode) {
		if (DomUtils.hasClassName(parent,className)) {
			return parent;
		}
	}
	return null;
};

/**
 *	@param Element target
 *	@param string className
 *	@returns boolean
 */
DomUtils.hasClassName = function (target,className) {
	
	function _isLastOfMultpleClassNames(all,className) {
		var spaceBefore = all.lastIndexOf(className)-1;
		return all.endsWith(className) && 
			all.substring(spaceBefore,spaceBefore+1) == " ";
	}

	className = className.trim();
	var cn = target.className;
	if (!cn) {
		return false;
	}
	cn = cn.trim();
	if (cn == className) {
		return true;
	}
	if (cn.indexOf(className + " ") > -1) {
		return true;
	}
	if (_isLastOfMultpleClassNames(cn,className)) {
		return true;
	}
	return false;
};

String.prototype.trim = function () {
	return this.replace(/^\s*(.+)/gi,"$1").replace(/\s*$/gi,"");
};

/** @constructor */
function EventUtils() {
	throw 'RuntimeException: EventUtils is a utility class with only static ' +
		' methods and may not be instantiated';
}		

/**
 *  @access static
 *  @param HTMLElement target
 *  @param string type
 *  @param Function callback
 *  @param boolean captures
 */
EventUtils.addEventListener = function (target,type,callback,captures) {
	if (target.addEventListener) {
		// EOMB
		target.addEventListener(type,callback,captures);
	} else if (target.attachEvent) {
		// IE
		target.attachEvent('on'+type,callback,captures);
	} else {
		// IE 5 Mac and some others

		target['on'+type] = callback;
	}
}			

/**
 *  @access static
 *  @param HTMLElement target
 *  @param string type
 *  @param Function callback
 *  @param boolean captures
 */
EventUtils.removeEventListener = function (target,type,callback,captures) {
	if (target.removeEventListener) {
		// EOMB
		target.removeEventListener(type,callback,captures);
	} else if (target.detachEvent) {
		// IE
		target.detachEvent('on'+type,callback,captures);
	} else {
		// IE 5 Mac and some others
		target['on'+type] = null;
	}
}			

/**
 *	@constructor
 *	@param Event (EOMB) | undefined (IE)
 *
 */
function Evt(evt) {
	var docEl 	 = document.documentElement;
	var body  	 = document.body;
	
	this._evt 	 = (evt) ? evt : 
				   (window.event) ? window.event : null;
	this._source = (evt.target) ? evt.target : 
				   (evt.srcElement) ? evt.srcElement : null;
	this._x		 = (evt.pageX) ? evt.pageX : 
				   (docEl.scrollLeft) ? (docEl.scrollLeft + evt.clientX) : 
				   (body.scrollLeft) ? (body.scrollLeft + evt.clientX) : evt.clientX;
	this._y 	 = (evt.pageY) ? evt.pageY : 
				   (docEl.scrollTop) ? (docEl.scrollTop + evt.clientY) :
				   (body.scrollTop) ? (body.scrollTop + evt.clientY) : evt.clientY;
}

/** @returns number */
Evt.prototype.getX = function () {
	return this._x;
};

/** @returns number */
Evt.prototype.getY = function () {
	return this._y;
};

/** @returns HTMLElement */
Evt.prototype.getSource = function () {
	return this._source;
};

/** 
 *	@returns void
 */
Evt.prototype.consume = function () {
	if (!this._evt) return;
	if (this._evt.stopPropagation) {
		this._evt.stopPropagation();
		this._evt.preventDefault();
	} else if (typeof this._evt.cancelBubble != undefined) {
		this._evt.cancelBubble = true;
		this._evt.returnValue  = false;
	} else {
		this._evt = null;
	}
};

function startMoveCursor(evt)
{
	evt = new Evt(evt);
	EventUtils.addEventListener(document,"mouseout",mouseOut);
	Cursor.setCursor(Cursor.MOVE);
}

function mouseOut(evt)
{
	evt = new Evt(evt);
	EventUtils.removeEventListener(document,"mouseout",mouseOut);
	Cursor.setCursor(Cursor.DEFAULT);
}

function startDrag(evt)
{
	evt = new Evt(evt);
	
	element = DomUtils.getFirstAncestorByClassName(evt.getSource(),boxclass);
	
	Cursor.setCursor(Cursor.MOVE);
	EventUtils.addEventListener(document,"mousemove",mouseDrag);
	EventUtils.addEventListener(document,"mouseup",mouseReleased);
	
	div.style.display = "block";
	
	div.style.width = DomUtils.getWidth(element) + "px";
	div.style.height = DomUtils.getHeight(element) + "px";
	
	x = DomUtils.getX(element) - 2;
	y = DomUtils.getY(element) - 2;
	
	deltaX = evt.getX() - x;
	deltaY = evt.getY() - y;
	
	DomUtils.setLocation(div,x,y);
	
	document.body.appendChild(div);
	
	DomUtils.setOpacity(element,.5);
}

function mouseDrag(evt)
{
	evt = new Evt(evt);
	
	x = evt.getX() - deltaX;
	y = evt.getY() - deltaY;
	
	DomUtils.setLocation(div,x,y);
}

function mouseReleased(evt)
{
	evt = new Evt(evt);
	
	element = DomUtils.getFirstAncestorByClassName(evt.getSource(),boxclass);
	
	div.style.display = "none";

	EventUtils.removeEventListener(document,"mousemove",mouseDrag);
	EventUtils.removeEventListener(document,"mouseup",mouseReleased);

	DomUtils.setOpacity(element,1);
	Cursor.setCursor(Cursor.DEFAULT);
}

function startMoveCursor(evt)
{
	evt = new Evt(evt);

	EventUtils.addEventListener(document,"mouseout",mouseOut);
	
	Cursor.setCursor(Cursor.MOVE);
}

function mouseOut(evt)
{
	evt = new Evt(evt);
	
	EventUtils.removeEventListener(document,"mouseout",mouseOut);
	
	Cursor.setCursor(Cursor.DEFAULT);
}
// -->
</script>
</head>

<body>

<div id="bigbox1" class="bigbox">
<div id="box1" class="box"><div id="title1" class="title" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);">a</div></div>
<div id="box2" class="box"><div id="title2" class="title" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);">b</div></div>
</div>

<div id="bigbox2" class="bigbox">
<div id="box3" class="box"><div id="title3" class="title" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);">c</div></div>
<div id="box4" class="box"><div id="title4" class="title" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);">d</div></div>
</div>

<div id="bigbox3" class="bigbox">
<div id="box1" class="box"><div id="title5" class="title" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);">e</div></div>
<div id="box2" class="box"><div id="title6" class="title" onMouseOver="startMoveCursor(event);" onMouseDown="startDrag(event);">f</div></div>
</div>

<? print( serialize(array( 0 => array( 4, 6 ), 1 => array( 2, 1, 3 ), 2 => array( 5 ) ) ) ); ?>

</body>
</html>