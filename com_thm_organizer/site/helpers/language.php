<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerHelperLanguage
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class provides methods used by organizer models for retrieving teacher data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerHelperLanguage
{
    /**
     * Method to switch the language
     *
     * @param   string  $view      the view to be called
     * @param   string  $language  the language in which content is to be delivered
     * @param   int     $menuID    the id of the menu entry
     * @param   int     $groupBy   a code for the criteria for grouping
     *
     * @return  string  the language switch URI
     */
    public static function languageSwitch($view, $language, $menuID, $groupBy = 0)
    {
        $URI = JURI::getInstance('index.php');
        $params = array();
        $params['option'] = 'com_thm_organizer';
        $params['view'] = $view;
        $params['Itemid'] = $menuID;
        $params['languageTag'] = $language;
        if ($groupBy)
        {
            $params['groupBy'] = $groupBy;
        }
        $URIParams = array_merge($URI->getQuery(true), $params);
        $query = $URI->buildQuery($URIParams);
        $URI->setQuery($query);
        return $URI->toString();
    }
}