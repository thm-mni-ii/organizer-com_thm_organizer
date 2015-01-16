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
     * @param   string  $url          the base url
     * @param   string  $newLanguage  the target language for the switch
     *
     * @return  string  a HTML anchor tag with the appropriate information
     */
    public static function languageSwitch($url, $newLanguage)
    {
        $imgPath = JURI::root() . "/media/com_thm_organizer/images/$newLanguage.png";
        $switch = '<a href="' . JRoute::_("$url&languageTag=$newLanguage") . '">';
        $switch .= '<img class="btn flag ' . $newLanguage . '" alt="' . $newLanguage . '" src="' . $imgPath . '" />';
        $switch .= '</a>';
        return $switch;
    }
}