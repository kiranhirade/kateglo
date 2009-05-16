<?
/**
 * Entry point of application
 */

// constants
define(LF, "\n"); // line break
define(APP_NAME, 'Kateglo (Beta)'); // application name
define(APP_VERSION, 'v0.0.5'); // application version. See README.txt

// variables
$base_dir = dirname(__FILE__);
$is_post = ($_SERVER['REQUEST_METHOD'] == 'POST');
$title = APP_NAME;
ini_set('include_path', $base_dir . '/pear/');
foreach ($_GET as $key => $val)
	$_GET[$key] = trim($val);

// includes
require_once('config.php');
require_once('messages.php');
require_once('common.php');
require_once('class_db.php');
require_once('class_form.php');
require_once('class_logger.php');
require_once('class_user.php');
require_once('Auth.php');
require_once('class_dictionary.php');
require_once('class_glossary.php');
require_once('class_kbbi.php');

// initialization
$db = new db;
$db->connect($dsn);
$db->msg = $msg;

// authentication & and logging
$auth = new Auth(
	'MDB2', array(
		'dsn' => $db->dsn,
		'table' => "sys_user",
		'usernamecol' => "user_id",
		'passwordcol' => "pass_key"
	), 'login');
$auth->start();
$logger = new logger(&$db, &$auth);
$logger->log();

// process
// TODO: This should be automatically perform, using method_exists, perhaps?
switch ($_GET['mod'])
{
	case 'auth':
		$user = new user(&$db, &$auth, $msg);
		switch ($_GET['action'])
		{
			case 'logout':
				$auth->logout();
				redir('./?');
			case 'password':
				if ($is_post) $user->change_password();
				break;
		}
		break;
	case 'dict':
		$dictionary = new dictionary(&$db, &$auth, $msg);
		if ($is_post && $auth->checkAuth() && $_GET['action'] == 'form')
			$dictionary->save_form();
		break;
	case 'glo':
		$glossary = new glossary(&$db, &$auth, $msg);
		switch ($_GET['action'])
		{
			case 'form':
				if ($is_post && $auth->checkAuth() && $_GET['action'] == 'form')
					$glossary->save_form();
				break;
			default:
				break;
		}
		break;
}

// display
// TODO: This should be automatically perform, using method_exists, perhaps?
$body .= show_header();
switch ($_GET['mod'])
{
	case 'dict':
		switch ($_GET['action'])
		{
			case 'view':
				$body .= $dictionary->show_phrase();
				break;
			case 'form':
				$body .= $dictionary->show_form();
				break;
			default:
				$body .= $dictionary->show_list();
				break;
		}
		$title = $msg['dictionary'] . ' - ' . $title;
		if ($_GET['phrase']) $title = $_GET['phrase'] . ' - ' . $title;
		break;
	case 'glo':
		switch ($_GET['action'])
		{
			case 'form':
				$body .= $glossary->show_form();
				break;
			default:
				$body .= $glossary->show_result();
				break;
		}
		$title = $msg['glossary'] . ' - ' . $title;
		break;
	case 'doc':
		$body .= read_doc($_GET['doc']);
		break;
	case 'auth':
		switch ($_GET['action'])
		{
			case 'login':
				$body .= login();
				break;
			case 'password':
				$body .= $user->change_password_form();
				break;
		}
		break;
	default:
		$searches = $db->get_rows('SELECT phrase FROM searched_phrase
			ORDER BY search_count DESC LIMIT 0, 5;');
		if ($searches)
		{
			$search_result = '';
			$tmp = '<strong>%1$s</strong> [<a href="?mod=dict&action=view&phrase=%1$s">%2$s</a>, '
				. '<a href="?mod=glo&phrase=%1$s">%3$s</a>]';
			for ($i = 0; $i < $db->num_rows; $i++)
			{
				if ($db->num_rows > 2)
					$search_result .= $search_result ? ', ' : '';
				if ($i == $db->num_rows - 1 && $db->num_rows > 1)
					$search_result .= ' dan ';
				$search_result .= sprintf($tmp, $searches[$i]['phrase'],
					$msg['dict_short'], $msg['glo_short']);
			}
		}
		else
			$search_result = $msg['no_most_searched'];

		$dict_count = $db->get_row_value('SELECT COUNT(*) FROM phrase;');
		$glo_count = $db->get_row_value('SELECT COUNT(*) FROM translation;');
		$body .= sprintf($msg['welcome'], $dict_count, $glo_count, $search_result);
		break;
}

// render
$ret .= '<html>' . LF;
$ret .= '<head>' . LF;
$ret .= '<title>' . $title . '</title>' . LF;
$ret .= '<link rel="stylesheet" href="./common.css" type="text/css" />' . LF;
$ret .= '<link rel="icon" href="./images/favicon.ico" type="image/x-icon">' . LF;
$ret .= '<link rel="shortcut icon" href="./images/favicon.ico" type="image/x-icon">' . LF;
$ret .= '</head>' . LF;
$ret .= '<body>' . LF;
$ret .= $body;
$ret .= sprintf('<p><span style="float:right;"><a href="http://creativecommons.org/licenses/by-sa/3.0/"><img alt="Creative Commons License" style="border-width:0" src="./images/cc-by-sa.png" /></a></span><a href="%2$s">%1$s %3$s</a></p>' . LF,
	APP_NAME, './?mod=doc&doc=README.txt', APP_VERSION);
$ret .= '</body>' . LF;
$ret .= '</html>' . LF;
echo($ret);
?>