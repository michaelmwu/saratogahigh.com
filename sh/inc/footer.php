</td>

<? if(strlen($this->rightbar) > 0) { ?>

<!-- Right Bar -->

<td class="sidecell" colspan="1">
<? saratogahigh::do_boxes($this->rightbar); ?>
</td>
<? } ?>

</tr>

<tr>
<td colspan="3" class="bottomspacer">
<img class="spacer" src="/images/spacer.gif" alt="spacer">
<div class="right">
<?
	if($login->loggedin)
		print '<a class="small" href="' . MAIN_URI . 'logout.php">Logout</a>';
	else
		print '<a class="small" href="' . MAIN_URI . 'login.php">Login</a>';
	if($login->isadmin)
		print '<br><a class="small" href="' . MAIN_URI . 'spawn.php">Spawn New Page</a>';
?></div>
</td>
</tr>

</table>

<!-- End Body -->
<?=$this->javascript?>
</body>
</html>