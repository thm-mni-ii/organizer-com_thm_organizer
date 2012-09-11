<?php
// Make sure we're being called from the command line, not a web interface
if (array_key_exists('REQUEST_METHOD', $_SERVER)) die();

// Set flag that this is a parent file.
if(!defined('_JEXEC')) define('_JEXEC', 1);
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(dirname(__FILE__)));
	require_once JPATH_BASE . DS . 'includes' . DS . 'defines.php';
	require_once JPATH_BASE . DS . 'includes' . DS . 'framework.php';
}

require_once JPATH_LIBRARIES . '/import.php';
require_once JPATH_LIBRARIES . '/cms.php';

// Force library to be in JError legacy mode
JError::$legacy = true;

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

$mainframe = JFactory::getApplication('site');
$mainframe->initialise();
$user = JFactory::getUser();

?>