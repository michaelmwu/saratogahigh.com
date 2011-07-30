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
var boxheaderclass = "boxheader";
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
	EventUtils.addEventListener(document,"mouseout",mouseOut);
	evt = new Evt(evt);
	Cursor.setCursor(Cursor.MOVE);
	
/*	var xbox = DomUtils.getFirstChildByClassName(evt.getSource(),toolbarclass); // BUGGY
	try
	{
		xbox.style.display = "block";
	}
	catch( error )
	{
		return false;
	} */
}

function mouseOut(evt)
{
	evt = new Evt(evt);
	Cursor.setCursor(Cursor.DEFAULT);
	
	EventUtils.removeEventListener(document,"mouseout",mouseOut);
	
/*	var to = evt.getSource(); // BUGGY
	if(to.tagName != "H2")
		to = DomUtils.getFirstAncestorByClassName(to,boxheaderclass);
	var relto = EventUtils.getMouseTo( evt.getEvent() );
	while (relto != to && relto.nodeName != 'BODY')
		relto = relto.parentNode;
	if (relto == to) return;
	
	var xbox = DomUtils.getFirstChildByClassName(evt.getSource(),toolbarclass);
	try
	{
		xbox.style.display = "none";
	}
	catch( error )
	{
		return false;
	} */
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
	var url = "index-blocktest.php";
	var i,j,temp_boxes;
	var name;
	
	var args = new Array();

	for(i=0;i<bigboxes.length;i++)
	{
		if(bigbox = document.getElementById(bigboxes[i]))
		{
			args.push(new Array());
			for(j=0;j<bigbox.childNodes.length;j++)
			{
				box = bigbox.childNodes[j];

				if(DomUtils.hasClassName(box,boxclass) && !DomUtils.hasClassName(box,fakeboxclass))
				{
					if(!(name = parseInt(box.getAttribute("name"))))
						name = box.getAttribute("name");
					args[i].push(name);
				}
			}
		}
	}
	
	xmlhttp.open('POST',url,true);
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xmlhttp.send('XMLRequest=1&XMLFunction=save_boxes&XMLArgs=' + Array(args).toPHP());
}

function save_box_prefs(box, key, value)
{
    var url = "index-blocktest.php"; 
    args = new Array(box,key,value);
    
    xmlhttp.open('POST', url,true);
	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4)
		{
			document.getElementById("infobox").innerHTML = xmlhttp.responseText;
		}
	}
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xmlhttp.send('XMLRequest=1&XMLFunction=save_box_prefs&XMLArgs=' + args.toPHP());
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
	source = evt.getSource();
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
    toolbarelement = DomUtils.getFirstAncestorByClassName(source,toolbarclass);
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