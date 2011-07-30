<? header('Content-type: text/css'); ?>

<? if(eregi("msie.*mac_", $_SERVER['HTTP_USER_AGENT'])) { ?>
/* Tab bar */
#dnavbar { float: left; width: 100%; background: #ffffff url("/imgs/nav/navred-bottom.gif") repeat-x bottom; font-family: sans-serif; font-size: 95%; }
#dnavbar ul { margin: 0; padding: 5px 5px 0; list-style: none; }
#dnavbar li { float: left; background-image: url("/imgs/nav/nav-right.gif"); background-position: right top; background-repeat: no-repeat; padding: 0; margin: 0 2px 0 0; }
#dnavbar li a { background-image: url("/imgs/nav/nav-left.gif"); display: block; padding: 5px 7px 3px 7px; color: #c33; background-position: left top; background-repeat: no-repeat; }
#dnavbar li a:hover { color: #039 }
#dnavbar .current a   { padding-bottom: 5px; font-weight: bold; color: #000099; }
<? } else { ?>
/* Tab bar */
#dnavbar { float: left; width: 100%; background: #ffffff url("/imgs/nav/navred-bottom.gif") repeat-x bottom; font-family: sans-serif; font-size: 95%; }
#dnavbar ul { margin: 0; padding: 5px 5px 0; list-style: none; }
#dnavbar li { float: left; background-image: url("/imgs/nav/nav-right.gif"); background-position: right top; background-repeat: no-repeat; padding: 0; margin: 0 2px 0 0; }
#dnavbar li a { background-image: url("/imgs/nav/nav-left.gif"); display: block; padding: 5px 7px 3px 7px; color: #c33; background-position: left top; background-repeat: no-repeat; }
#dnavbar li a:hover { color: #039 }
#dnavbar li#home a { background-image: url("/imgs/nav/navhome-left.gif"); padding-left: 30px;  font-weight: bold; }
#dnavbar li.inverted { background-image: url("/imgs/nav/navred-right.gif"); }
#dnavbar li.inverted a { background-image: url("/imgs/nav/navred-left.gif"); color: #fff; }
#dnavbar li.inverted a:hover { color: #ddd }
#dnavbar .current a   { padding-bottom: 5px; font-weight: bold; color: #000099; }
<? } ?>