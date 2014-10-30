<?php
/*
 * Ensure that required path constants are defined.  These can be overridden within the phpunit.xml file
 * if you chose to create a custom version of that file.
 */
if (!defined('JPATH_TESTS'))
{
	define('JPATH_TESTS', realpath(dirname(__DIR__)));
}
if (!defined('JPATH_BASE'))
{
    define('JPATH_BASE', realpath(dirname(dirname(dirname(dirname(__DIR__))))));
}
if (!defined('JPATH_PLATFORM'))
{
    define('JPATH_PLATFORM', JPATH_BASE . '/libraries');
}
if (!defined('JPATH_LIBRARIES'))
{
    define('JPATH_LIBRARIES', JPATH_BASE . '/libraries');
}
if (!defined('JPATH_COMPONENT'))
{
    define('JPATH_COMPONENT', JPATH_BASE . '/administrator/components/com_thm_organizer');
}
if (!defined('JPATH_COMPONENT_SITE'))
{
    define('JPATH_COMPONENT_SITE', JPATH_BASE . '/components/com_thm_organizer');
}

// Import the Joomla bootstrap.
require_once JPATH_BASE . '/tests/bootstrapJ3.php';
