<?php

header("Content-type: image/jpeg");
$string = "TEST";
$im    = imagecreatefromgif("large-bubble.gif");
$black = imagecolorallocate($im, 0, 0, 0);
imagestring($im, 3, 10, 10, $string, $black);
imagejpeg($im);
imagedestroy($im);

?> 