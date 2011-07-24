<?
foreach(array('page','DateTime','db','login','news','comments') as $global)
	if($this->desc() != $global)
		global $$global;
?>