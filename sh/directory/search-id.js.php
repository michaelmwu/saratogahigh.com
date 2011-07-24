function loadID(search_id,element_id,id)
{
	sbox = document.getElementById(search_id);
	document.getElementById(element_id).value = id;
	sbox.style.display='none';
	return false;
}

function search_box(search_id,element_id)
{
	args = Array(search_id,element_id);

	xmlhttp.open("POST", "/directory/search-id.php",true);
	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4)
		{
			document.getElementById(search_id).innerHTML = xmlhttp.responseText;
			}
		}
		xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xmlhttp.send('XMLRequest=1&XMLFunction=search_box&XMLArgs=' + args.toPHP() + '&XMLPrint=');
	
	return false;
}

function search_inner(search_id,element_id,xfn,xln)
{
	args = Array(search_id,element_id,xfn,xln);

	xmlhttp.open("POST", "/directory/search-id.php",true);
	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4)
		{
			document.getElementById(search_inner).innerHTML = xmlhttp.responseText;
			}
		}
		xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xmlhttp.send('XMLRequest=1&XMLFunction=search_inner&XMLArgs=' + args.toPHP() + '&XMLPrint=');
	
	return false;
}

<? if($hideall) print 'loadID(' . $line['USER_ID'] . ');'; ?>