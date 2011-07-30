<?
$config = array(
			'title' => 'test'
);

require_once 'inc/config.php';

include 'blocks.php';

$page->header();

classes_block(30,$userR);

$page->footer();
?>