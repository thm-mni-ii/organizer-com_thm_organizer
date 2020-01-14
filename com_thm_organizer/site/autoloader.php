<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer;

defined('_JEXEC') or die;

use Exception;

spl_autoload_register(function ($originalClassName) {

	if ($originalClassName === 'TCPDF')
	{
		require_once JPATH_LIBRARIES . '/tcpdf/tcpdf.php';

		return;
	}

	$classNameParts = explode('\\', $originalClassName);

	$component = array_shift($classNameParts);
	if ($component !== 'Organizer')
	{
		return;
	}

	$className = array_pop($classNameParts);

	if (reset($classNameParts) === 'Admin')
	{
		array_shift($classNameParts);
	}

	$classNameParts[] = empty($className) ? 'Organizer' : $className;

	$filepath            = JPATH_ROOT . '/components/com_thm_organizer/' . implode('/', $classNameParts) . '.php';
	$namespacedClassName = "Organizer\\" . implode('\\', $classNameParts);

	if (is_file($filepath))
	{
		require_once $filepath;
		$valid = (
			class_exists($namespacedClassName)
			or interface_exists($namespacedClassName)
			or trait_exists($namespacedClassName)
		);
		if (!$valid AND JDEBUG)
		{
			echo "<pre>" . print_r('no class!', true) . "</pre>";
			echo "<pre>class name:              " . print_r($className, true) . "</pre>";
			echo "<pre>original fq namespace:   " . print_r($originalClassName, true) . "</pre>";
			echo "<pre>calculated fq namespace: " . print_r($namespacedClassName, true) . "</pre>";
			echo "<pre>file path:               " . print_r($filepath, true) . "</pre>";
			$exc = new Exception;
			echo "<pre>" . print_r($exc->getTraceAsString(), true) . "</pre>";
			die;
		}
	}
});
