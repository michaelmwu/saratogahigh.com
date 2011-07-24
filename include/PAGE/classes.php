<?
define("_BBCLONE_DIR", "/var/www/www/htdocs/bbclone/");
define("COUNTER", _BBCLONE_DIR."mark_page.php");
if (is_readable(COUNTER)) include_once(COUNTER);

require_once INCLUDE_DIR . 'login.php';
require_once INCLUDE_DIR . 'datetime.php';
require_once INCLUDE_DIR . 'form.php';
require_once INCLUDE_DIR . 'news.php';
require_once INCLUDE_DIR . 'comments.php';
require_once INCLUDE_DIR . 'csf.php';

function __autoload($class_name) {
   require_once $class_name . '.php';
}
?>