<?php
/*
 * Ensure that required path constants are defined.  These can be overridden within the phpunit.xml file
 * if you chose to create a custom version of that file.
 */

// Import the Joomla bootstrap.
require_once realpath(__DIR__) . "/../../bootstrapSelenium.php";

// add extension pages
$autoloader->addPagePath(realpath(__DIR__) . "/Pages");