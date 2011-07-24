<? $config = array(
	'title' => 'Spawn a Page',
	'bodytitle' => 'SPAWN PAGE',
	);

require_once 'inc/config.php';

$form = new form($page);

$file = new input;
$file->name = "file";
$file->maxlength = 100;
$file->add_class("spawnbox");
$file->req = IS_REQUIRED;
$form->add_element($file);

$title = new input;
$title->name = "title";
$title->maxlength = 100;
$title->add_class("spawnbox");
$title->req = IS_REQUIRED;
$form->add_element($title);

$bodytitle = new input;
$bodytitle->name = "bodytitle";
$bodytitle->maxlength = 100;
$bodytitle->add_class("spawnbox");
$bodytitle->req = IS_REQUIRED;
$form->add_element($bodytitle);

$rightbar = new input;
$rightbar->name = "rightbar";
$rightbar->maxlength = 100;
$rightbar->add_class("spawnbox");
$form->add_element($rightbar);

$leftbar = new input;
$leftbar->name = "leftbar";
$leftbar->maxlength = 100;
$leftbar->add_class("spawnbox");
$form->add_element($leftbar);

$boxes = new input;
$boxes->name = "boxes";
$boxes->maxlength = 100;
$boxes->add_class("spawnbox");
$boxes->req = IS_REQUIRED;
$form->add_element($boxes);

$takeme = new check;
$takeme->name = "takeme";
$takeme->selected = true;
$form->add_element($takeme);

if($_POST['action'] == 'spawn_page' && $login->isadmin)
{
	if($form->check())
		if(create_page($_POST['file'],$_POST['title'],$_POST['bodytitle'],$_POST['rightbar'],$_POST['leftbar'],$_POST['boxes']))
			header("Location: http://" . DNAME . MAIN_URI . $_POST['file']);
}

function create_page($file, $title, $body, $right, $left, $boxes)
{
	$contents = "<? \$config = array(\n";
	if($title)
		$contents .= "\t'title' => '$title',\n";
	if($body)
		$contents .= "\t'bodytitle' => '$body',\n";
	if($right)
		$contents .= "\t'rightbar' => '$right',\n";
	if($left)
		$contents .= "\t'leftbar' => '$left',\n";
	if($boxes)
		$contents .= "\t'boxes' => '$boxes',\n";
	$contents .=
"\t);

require_once 'inc/config.php';

\$page->header();

saratogahigh::do_boxes();

\$page->footer(); ?>";

	$file = fopen($file,'w') or die('Unable to open file');
	
	fputs($file,$contents) or die('Unable to write file');
	
	fclose($file);
	
	return true;
}

$page->header();

if($login->isadmin)
{
?>
	<form name="spawn_form" action="spawn.php" method="POST">
	<p class="bold">Fill in the labels for each piece. Rightbar and / or leftbar may be omitted. Starred items are required.</p>
	<table style="width: 80%; margin: 0px auto;">
	<tr>
	<td style="width: 120px;" class="required">File name, ie: academics/academics.php)</td>
	<td class="right"><? $form->element('file'); ?></td>
	</tr>

	<tr>
	<td class="required">Title</td>
	<td class="right"><? $form->element('title'); ?></td>
	</tr>
	
	<tr>
	<td class="required">Body Title</td>
	<td class="right"><? $form->element('bodytitle'); ?></td>
	</tr>
	
	<tr>
	<td>Right Bar</td>
	<td class="right"><? $form->element('rightbar'); ?></td>
	</tr>
	
	<tr>
	<td>Left Bar</td>
	<td class="right"><? $form->element('leftbar'); ?></td>
	</tr>
	
	<tr>
	<td class="required">Boxes</td>
	<td class="right"><? $form->element('boxes'); ?></td>
	</tr>

	<tr>
	<td>Go to page</td>
	<td class="right"><? $form->element('takeme'); ?></td>
	</tr>

	<tr>
	<td>&nbsp;</td>
	<td><input name="action" type="hidden" value="spawn_page"><input name="submit" type="submit" value="Submit"></td>
	</tr>
	</table>
	</form>
<?
}
else
{
	print 'Sorry, you need to be an administrator to access this page.';
}

$page->footer(); ?>