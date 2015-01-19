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
jimport('thm_core.helpers.corehelper');

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
     * Sets the Joomla Language based on input from the language switch
     *
     * @return  void
     */
    public static function setLanguage()
    {
        $app = JFactory::getApplication();
        $requested = $app->input->get('languageTag', '');
        $supportedLanguages = array('en', 'de');
        if (in_array($requested, $supportedLanguages))
        {
            $lang = JFactory::getApplication()->getLanguage();
            if ($requested == 'en')
            {
                $lang->setLanguage('en-GB');
                return;
            }
            if ($requested == 'de')
            {
                $lang->setLanguage('de-DE');
                return;
            }
            $lang->setLanguage('en-GB');
        }
    }

    /**
     * Sets the language to the one requested
     *
     * @return  void  sets the default language for joomla
     */
    public static function getLanguageSwitches($params)
    {
        $params['option'] = 'com_thm_organizer';

        $input = JFactory::getApplication()->input;
        $menuID = $input->getInt('Itemid', 0);
        if (!empty($menuID))
        {
            $params['Itemid'] = $menuID;
        }

        $languageSwitches = array();
        $current = THM_CoreHelper::getLanguageShortTag();
        $supportedLanguages = array('en', 'de');
        foreach ($supportedLanguages AS $supported)
        {
            if ($current != $supported)
            {
                $params['languageTag'] = $supported;
                $url = 'index.php?' . JUri::buildQuery($params);
                $languageSwitches[] = self::languageSwitch($url, $supported);
            }
        }
        return $languageSwitches;
    }

    /**
     * Method to switch the language
     *
     * @param   string  $url          the base url
     * @param   string  $newLanguage  the target language for the switch
     *
     * @return  string  a HTML anchor tag with the appropriate information
     */
    private static function languageSwitch($url, $newLanguage)
    {
        $imgPath = JURI::root() . "/media/com_thm_organizer/images/$newLanguage.png";
        $switch = '<a href="' . JRoute::_($url) . '">';
        $switch .= '<img class="btn flag ' . $newLanguage . '" alt="' . $newLanguage . '" src="' . $imgPath . '" />';
        $switch .= '</a>';
        return $switch;
    }
}