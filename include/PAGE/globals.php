<?
foreach(array('page','DateTime','db','login','news','comments','sh','form','csf') as 
$global)
	if($this->desc() != $global)
		global $$global;
?>
