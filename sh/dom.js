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
    for(var i=0; i<child_list.length; i++) {
		if (DomUtils.hasClassName(child_list[i],className)) {
			return child_list[i];
		}
		
		if(childelement = DomUtils.getFirstChildByClassName(child_list[i],className))
			return childelement;
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

EventUtils.getMouseTo = function (e)
{
	return (e.relatedTarget) ? e.relatedTarget :
		(e.toElement) ? e.toElement : null;
}

EventUtils.getMouseFrom = function (e)
{
	return (e.relatedTarget) ? e.relatedTarget :
		(e.toElement) ? e.fromElement : null;
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

/** @returns event */
Evt.prototype.getEvent = function () {
	return this._evt;
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

function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}