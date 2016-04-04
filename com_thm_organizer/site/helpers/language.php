<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerHelperLanguage
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
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
    public static function getLanguage()
    {
        $app = JFactory::getApplication();
        $default = THM_CoreHelper::getLanguageShortTag();
        $requested = $app->input->get('languageTag', $default);
        $supportedLanguages = array('en', 'de');
        if (in_array($requested, $supportedLanguages))
        {
            switch ($requested)
            {
                case 'de':
                    $lang = new JLanguage('de-DE');
                    break;
                case 'en':
                default:
                    $lang = new JLanguage('en-GB');
                    break;
            }
        }
        else
        {
            $lang = new JLanguage('en-GB');
        }

        $lang->load('com_thm_organizer');
        return $lang;
    }

    /**
     * Sets the language to the one requested. This can only be called after getLanguage().
     *
     * @param   array  $params  the configuration parameters
     *
     * @return  array  html links for language redirection
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

        $requested = $input->getString('languageTag', '');

        $languageSwitches = array();
        $current = empty($requested)? THM_CoreHelper::getLanguageShortTag() : $requested;
        $supportedLanguages = array('en', 'de');
        $submit = !empty($params['form']);
        foreach ($supportedLanguages AS $supported)
        {
            if ($current != $supported)
            {
                $params['languageTag'] = $supported;
                $url = 'index.php?' . JUri::buildQuery($params);
                $languageSwitches[] = $submit? self::languageSwitch($supported) : self::languageLink($url, $supported);
            }
        }

        return $languageSwitches;
    }

    /**
     * Method to link another language
     *
     * @param   string  $newLanguage  the target language for the switch
     *
     * @return  string  a HTML anchor tag with the appropriate information
     */
    private static function languageSwitch($newLanguage)
    {//$( "#languageTag" ).val("' . $newLanguage . '");submit();
        $constants = array('en' => 'COM_THM_ORGANIZER_FILTER_ENGLISH', 'de' => 'COM_THM_ORGANIZER_FILTER_GERMAN');
        $imgPath = JURI::root() . "/templates/thm/images/assets/icon_earth.jpg";
        $switch = '<a onclick="$(\'#languageTag\').val(\'' . $newLanguage . '\');$(\'#adminForm\').submit();">';
        $switch .= '<img class="flag ' . $newLanguage . '" alt="' . $newLanguage . '" src="' . $imgPath . '" />';
        $switch .= JText::_($constants[$newLanguage]);
        $switch .= '</a>';
        return $switch;
    }

    /**
     * Method to link another language
     *
     * @param   string  $url          the base url
     * @param   string  $newLanguage  the target language for the switch
     *
     * @return  string  a HTML anchor tag with the appropriate information
     */
    private static function languageLink($url, $newLanguage)
    {
        $constants = array('en' => 'COM_THM_ORGANIZER_FILTER_ENGLISH', 'de' => 'COM_THM_ORGANIZER_FILTER_GERMAN');
        $imgPath = JURI::root() . "/templates/thm/images/assets/icon_earth.jpg";
        $switch = '<a href="' . JRoute::_($url) . '">';
        $switch .= '<img class="flag ' . $newLanguage . '" alt="' . $newLanguage . '" src="' . $imgPath . '" />';
        $switch .= JText::_($constants[$newLanguage]);
        $switch .= '</a>';
        return $switch;
    }
}