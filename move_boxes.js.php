<? include 'db.php'; ?>
var deltaX;
var deltaY;
var div;

var boxX;
var boxY;

var boxWidth;
var boxHeight;

var bigboxes = Array("bigbox1","bigbox2","bigbox3","bigbox4");

var boxes = Array();

var boxclass = "movebox";
var bigboxclass = "bigbox";
var titleclass = "title";
var fakeboxclass = "hidden";
var contentclass = "content";
var expandclass = "expand";
var minimizeclass= "minimize";
var toolbarclass = "xbox";

var div = document.createElement("DIV");

var boxelement;

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
	if (typeof( target.filters ) != "undefined") {
		target.filters["alpha"] = percentage*100 + "";
	} else if (typeof( target.style.opacity ) != "undefined") {
		target.style.opacity = "" + percentage;
	} else if (typeof( target.style.mozOpacity ) != "undefined") {
		target.style.mozOpacity = "" + percentage;
	}
};

/**
 *	@param string HTMLElement
 *	@return number
 */
DomUtils.getX = function (target) {
	offset = 0;

	do
	{
		offset += parseInt(target.offsetLeft);
	}
	while(target = target.offsetParent);

	return offset;
};

/**
 *	@param string HTMLElement
 *	@return number
 */
DomUtils.getY = function (target) {
	offset = 0;

	do
	{
		offset += parseInt(target.offsetTop);
	}
	while(target = target.offsetParent);

	return offset;};

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
 *	@param string className
 *	@return Element
 */
DomUtils.getFirstChildByClassName = function (parent,className) {
	var child_list = parent.childNodes;
    for(i=0; i<child_list.length; i++) {
		if (DomUtils.hasClassName(child_list[i],className)) {
			return child_list[i];
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
	if (cn.indexOf(" " + className) > -1) {
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
	
	Cursor.setCursor(Cursor.MOVE);
	
	div.style.display = "block";

	boxelement = DomUtils.getFirstAncestorByClassName(evt.getSource(),boxclass);
	
	boxWidth = DomUtils.getWidth(boxelement);
	boxHeight = DomUtils.getHeight(boxelement);

	div.style.width = boxWidth + "px";
	div.style.height = boxHeight + "px";
	
	boxX = DomUtils.getX(boxelement) - 2;
	boxY = DomUtils.getY(boxelement) - 2;

	DomUtils.setOpacity(boxelement,.5);

	deltaX = evt.getX() - boxX;
	deltaY = evt.getY() - boxY;
	
	DomUtils.setLocation(div,boxX,boxY);

	boxelement.appendChild(div);

	EventUtils.addEventListener(document,"mousemove",mouseDrag);
	EventUtils.addEventListener(document,"mouseup",mouseReleased);
}

function mouseDrag(evt)
{
	var elem;

	evt = new Evt(evt);

	var eventX = evt.getX();
	var eventY = evt.getY();

	elem = find_nearest(eventX,eventY);
	
	if(boxelement.id != elem.id && elem != null && DomUtils.hasClassName(boxelement,boxclass) && not_previous_element(elem,boxelement))
	{
		var parent = elem.parentNode;

		if(boxelement.parentNode != elem.parentNode)
			elem.parentNode.appendChild(boxelement);
		parent.insertBefore(boxelement,elem);

		boxX = DomUtils.getX(boxelement) - 2;
		boxY = DomUtils.getY(boxelement) - 2;
		
		boxWidth = DomUtils.getWidth(boxelement);
		boxHeight = DomUtils.getHeight(boxelement);

		calculate_boxes();
		
		save_boxes();
	}
	
	DomUtils.setLocation(div,eventX - deltaX,eventY - deltaY);
}

function mouseReleased(evt)
{
	evt = new Evt(evt);

	DomUtils.setOpacity(boxelement,1);
	
	Cursor.setCursor(Cursor.DEFAULT);

	EventUtils.removeEventListener(document,"mousemove",mouseDrag);
	EventUtils.removeEventListener(document,"mouseup",mouseReleased);

	returnDiv();
}

function startMoveCursor(evt)
{
	var evt = new Evt(evt);

	EventUtils.addEventListener(document,"mouseout",mouseOut);
	
	Cursor.setCursor(Cursor.MOVE);
}

function mouseOut(evt)
{
	var evt = new Evt(evt);
	
	EventUtils.removeEventListener(document,"mouseout",mouseOut);
	
	Cursor.setCursor(Cursor.DEFAULT);
}

function calculate_boxes()
{
	boxes = Array();
	var i,j,bigbox;
	var box;

	for(i=0;i<bigboxes.length;i++)
	{
		if(bigbox = document.getElementById(bigboxes[i]))
		{
			for(j=0;j<bigbox.childNodes.length;j++)
			{
				box = bigbox.childNodes[j];

				if(DomUtils.hasClassName(box,boxclass))
				{
					boxes.push(box);
				}
			}
		}
	}
}

function find_nearest(x,y)
{
	var i,currentDistance,currentBox,minDistance;
	var elem = null;

	for(i=0;i<boxes.length;i++)
	{
		currentBox = boxes[i];
		currentDistance = Math.pow(DomUtils.getX(currentBox) - x,2) + Math.pow(DomUtils.getY(currentBox) - y,2);

		if(minDistance == null)
		{
			minDistance = currentDistance;
			elem = currentBox;
		}

		if(minDistance > currentDistance)
		{
			minDistance = currentDistance;
			elem = currentBox;
		}
	}
	return elem;
}

function find_nearest_from_elem(elem)
{
	return find_nearest(DomUtils.getX(elem),DomUtils.getY(elem));
}

function not_previous_element(tobox, switchbox)
{
	var i;
	for(i=0;i<boxes.length;i++)
	{
		if( boxes[i].id == tobox.id )
		{
			if( i - 1 > -1 && boxes[i-1].id == switchbox.id)
				return false;
			return true;
		}
	}
}

function returnDiv()
{
	var newX = parseInt( (boxX + DomUtils.getX(div)) / 2 );
	var newY = parseInt( (boxY + DomUtils.getY(div)) / 2 );
	
	var newWidth = parseInt( (boxWidth + DomUtils.getWidth(div)) / 2 );
	var newHeight = parseInt( (boxHeight + DomUtils.getHeight(div)) / 2 );

	DomUtils.setLocation(div,newX,newY);
	
	div.style.width = newWidth + "px";
	div.style.height = newHeight + "px";

	if(Math.abs(newX - boxX) < 2 && Math.abs(newY - boxY) < 2)
	{
		div.style.display = "none";
		return;
	}

	window.setTimeout("returnDiv()",25);
}

function save_boxes()
{
	var url = "/index.php";
	var i,j,k,temp_boxes;

	var args = "a:2:{i:0;a:" + bigboxes.length + ":{";
	for(i=0;i<bigboxes.length;i++)
	{
		if(bigbox = document.getElementById(bigboxes[i]))
		{
			args += "i:" + i +";a:";
			k = 0;
			temp_boxes = "";
			for(j=0;j<bigbox.childNodes.length;j++)
			{
				box = bigbox.childNodes[j];

				if(DomUtils.hasClassName(box,boxclass) && !DomUtils.hasClassName(box,fakeboxclass))
				{
					temp_boxes += "i:" + k + ";i:" + box.getAttribute("name") + ";";
					k++;
				}
			}
			args += k + ":{" + temp_boxes + "}";
		}
	}
	args += "}i:1;i:<?=$userid?>;}";

	xmlhttp.open('POST', url,true);
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xmlhttp.send('XMLRequest=1&XMLFunction=save_boxes&XMLArgs=' + args);
}

function save_box_prefs(box, key, value)
{
    var url = "/index.php"; 
    args = 'a:4:{i:0;i:' + box + ';i:1;s:' + key.length + ':"' + key + '";i:2;s:' + value.length + ':"' + value + '";i:3;i:<?=$userid?>;}';
    
    xmlhttp.open('POST', url,true);
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xmlhttp.send('XMLRequest=1&XMLFunction=save_box_prefs&XMLArgs=' + args);  
}

function removeMe(evt)
{
	evt = new Evt(evt);

	boxelement = DomUtils.getFirstAncestorByClassName(evt.getSource(),boxclass);

	bigboxelement = DomUtils.getFirstAncestorByClassName(evt.getSource(),bigboxclass);

	try
	{
		bigboxelement.removeChild(boxelement);
	}
	catch( error )
	{
		return false;
	}

	calculate_boxes();

	save_boxes();

	return false;
}

function minimizeMe(evt)
{
    evt = new Evt(evt);
    source=evt.getSource();
	boxelement = DomUtils.getFirstAncestorByClassName(source,boxclass);
    contentelement = DomUtils.getFirstChildByClassName(boxelement,contentclass);
    toolbarelement = DomUtils.getFirstAncestorByClassName(source,toolbarclass);
    minimizebtn = DomUtils.getFirstChildByClassName(toolbarelement,minimizeclass);
    expandbtn = DomUtils.getFirstChildByClassName(toolbarelement,expandclass);
    
	try
	{
		contentelement.style.display="none";
        expandbtn.style.display="inline";
        minimizebtn.style.display="none";
	}
	catch( error )
	{
		return false;
	}

	save_box_prefs(boxelement.getAttribute("name"), "content_state", "minimized");
    
	return false;
}

function expandMe(evt)
{
    evt = new Evt(evt);
    source=evt.getSource();  
	boxelement = DomUtils.getFirstAncestorByClassName(source,boxclass);
    contentelement = DomUtils.getFirstChildByClassName(boxelement,contentclass);
    toolbarelement = DomUtils.getFirstAncestorByClassName(source,toolbarclass)
    minimizebtn = DomUtils.getFirstChildByClassName(toolbarelement,minimizeclass);
    expandbtn = DomUtils.getFirstChildByClassName(toolbarelement,expandclass);

	try
	{
		contentelement.style.display="block";
        minimizebtn.style.display="inline";
        expandbtn.style.display="none";
	}
	catch( error )
	{
		return false;
	}

	save_box_prefs(boxelement.getAttribute("name"), "content_state", "expanded");
    
	return false;
}