<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Organizer\Controller;
use ReflectionMethod;
use RuntimeException;

/**
 * Class provides generalized functions useful for several component files.
 */
class Routing
{
    /**
     * Builds a the base url for redirection
     *
     * @return string the root url to redirect to
     */
    public static function getRedirectBase()
    {
        $url    = Uri::base();
        $input  = self::getInput();
        $menuID = $input->getInt('Itemid');

        if (!empty($menuID)) {
            $url .= self::getApplication()->getMenu()->getItem($menuID)->route . '?';
        } else {
            $url .= '?option=com_thm_organizer&';
        }

        if (!empty($input->getString('languageTag'))) {
            $url .= '&languageTag=' . Languages::getTag();
        }

        return $url;
    }
}
