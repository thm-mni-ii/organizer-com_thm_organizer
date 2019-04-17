<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer;

defined('_JEXEC') or die;

spl_autoload_register(function ($originalClassName) {

    $classNameParts = explode('\\', $originalClassName);

    $component = array_shift($classNameParts);
    if ($component !== 'Organizer') {
        return;
    }

    $className = array_pop($classNameParts);

    switch (reset($classNameParts)) {
        case 'Admin':
            $base = JPATH_ADMINISTRATOR . '/components/com_thm_organizer';
            break;
        default:
            $base = JPATH_ROOT . '/components/com_thm_organizer';
            break;
    }

    $filepath            = implode('/', $classNameParts);
    $namespacedClassName = "Organizer\\" . implode('\\', $classNameParts) . "\\$className";

    $fullPath = "$base/$filepath/$className.php";
    if (is_file($fullPath)) {
        require_once $fullPath;
        if (!class_exists($namespacedClassName)) {
            echo "<pre>" . print_r('no class!', true) . "</pre>";
            echo "<pre>class name:              " . print_r($className, true) . "</pre>";
            echo "<pre>original fq namespace:   " . print_r($originalClassName, true) . "</pre>";
            echo "<pre>calculated fq namespace: " . print_r($namespacedClassName, true) . "</pre>";
            echo "<pre>file path:               " . print_r($fullPath, true) . "</pre>";
            $exc = new \Exception;
            echo "<pre>" . print_r($exc->getTraceAsString(), true) . "</pre>";
            die;
        }
    } // The legitimate reason for this case is Joomla using default namespaces to look for component files.
    else {
        return;
        echo "<pre>" . print_r('no file!', true) . "</pre>";
        echo "<pre>class name:              " . print_r($className, true) . "</pre>";
        echo "<pre>original fq namespace:   " . print_r($originalClassName, true) . "</pre>";
        echo "<pre>calculated fq namespace: " . print_r($namespacedClassName, true) . "</pre>";
        echo "<pre>file path:               " . print_r($fullPath, true) . "</pre>";
        $exc = new \Exception;
        echo "<pre>" . print_r($exc->getTraceAsString(), true) . "</pre>";
    }
});

class_alias('Joomla\CMS\Factory', 'Factory', true);

class_alias('Organizer\Controllers\Controller', 'Controller', true);

class_alias('Organizer\Helpers\Access', 'Access', true);
class_alias('Organizer\Helpers\OrganizerHelper', 'OrganizerHelper', true);
class_alias('Organizer\Helpers\Dates', 'Dates', true);
class_alias('Organizer\Helpers\HTML', 'HTML', true);
class_alias('Organizer\Helpers\Languages', 'Languages', true);
