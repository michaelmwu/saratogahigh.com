<?
require_once 'testconfig.php';

$page->header();

$bitmask = new bitmask();

$bitmask->set(8979879); // Whatever

$bitmask->set(888);

$bitmask->toggle(39393); // Yadda yadda

$bitmask->remove(888);

$bitmask->debug();

$bitmask->stringin("10010100010100100010101001010101000000001000001");

print $bitmask->stringout();

$bitmask->debug();

$bitmask->clear();

$bitmask->debug();

$page->footer();
?>