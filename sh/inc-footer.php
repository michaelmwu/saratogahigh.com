<script type="text/javascript">
<!--
function showCommentBox()
{
	document.getElementById('showCommentBox').style.display = 'none';
	document.getElementById('commentBox').style.display = 'block';
}

function hideCommentBox()
{
	document.getElementById('showCommentBox').style.display = 'block';
	document.getElementById('commentBox').style.display = 'none';
}

function comment()
{
	url = "/comment-confirm.php";

	xmlhttp.open('POST', url,true);
	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4)
		{
			document.getElementById("commentStatus").innerHTML = xmlhttp.responseText;
		}
	}
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xmlhttp.send('XMLRequest=1&page=<?= htmlentities($REQUEST_URI) ?>&go=comment&entrytext=' + document.commentForm.entrytext.value);
	hideCommentBox();
	document.commentForm.entrytext.value = "";
	return false;
}
//-->
</script>

<div id="commentStatus"></div>

<div id="showCommentBox"><span class="toolbar" style="cursor: hand" onclick="showCommentBox();"><img style="vertical-align: middle" src="/imgs/arrow-down.gif" alt="(more)">&nbsp;Questions, or Comments?</span></div>

<div id="commentBox">
<div onclick="hideCommentBox();" style="cursor: hand; background-color: #888; color: #fff; font-weight: bold; padding: 3px">Questions or Comments?</div>
<form name="commentForm" style="padding: 3px" method="POST" action="/comment-confirm.php" onSubmit="return comment();">
<p style="margin: 0px">
You can send comments or questions about this feature, page, or in general to SaratogaHigh.com staff. <?
if(eregi('/calendar/(layer|calendar|event)\.php', $_SERVER['SCRIPT_URL']))
	print '<span style="color: #900">This message does not go to the administrators of this calendar.</span> ';
if($loggedin && $isvalidated)
	print 'We\'ll follow up using the sh.com Mail system.';
else
	print 'If you leave your contact information (optional), we\'ll follow up by email.';
?><br><input type="hidden" name="go" value="comment">
<input type="hidden" name="page" value="<?= htmlentities($REQUEST_URI) ?>">
<textarea name="entrytext" rows="5" cols="50"><? if(!($loggedin && $isvalidated)) { ?>Name: 
Email: 
Question/Comment: <? } ?></textarea><br><input type="submit" name="btn" value="Send"> Or <a href="mailto:staff@saratogahigh.com">email</a> us.</p>
</form>
</div>