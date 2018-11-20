<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;


/**
 * Provides general functions for language data retrieval and display.
 */
class THM_OrganizerHelperLanguage extends JLanguage
{
    /**
     * Sets the Joomla Language based on input from the language switch
     *
     * @return JLanguage
     */
    public static function getLanguage()
    {
        $requested          = self::getShortTag();
        $supportedLanguages = ['en', 'de'];

        if (in_array($requested, $supportedLanguages)) {
            switch ($requested) {
                case 'de':
                    $lang = new JLanguage('de-DE');
                    break;
                case 'en':
                default:
                    $lang = new JLanguage('en-GB');
                    break;
            }
        } else {
            $lang = new JLanguage('en-GB');
        }

        $lang->load('com_thm_organizer');

        return $lang;
    }

    /**
     * Retrieves the two letter language identifier
     *
     * @return string
     */
    public static function getLongTag()
    {
        return self::resolveShortTag(self::getShortTag());
    }

    /**
     * Retrieves the two letter language identifier
     *
     * @return string
     */
    public static function getShortTag()
    {
        $app          = THM_OrganizerHelperComponent::getApplication();
        $requestedTag = $app->input->get('languageTag');

        if (!empty($requestedTag)) {
            return $requestedTag;
        }

        $fullTag    = JFactory::getLanguage()->getTag();
        $defaultTag = explode('-', $fullTag)[0];
        $menu       = $app->getMenu();

        if (empty($menu) or empty($menu->getActive()) or empty($menu->getActive()->params->get('initialLanguage'))) {
            return $defaultTag;
        }

        return $menu->getActive()->params->get('initialLanguage');
    }

    /**
     * Sets the language to the one requested. This can only be called after getLanguage().
     *
     * @param array $params the configuration parameters
     *
     * @return array  html links for language redirection
     */
    public static function getLanguageSwitches($params = [])
    {
        $input = THM_OrganizerHelperComponent::getInput();

        $link = empty($params['view']) ? false : true;

        if ($link) {
            $params['option'] = 'com_thm_organizer';

            $menuID = $input->getInt('Itemid');
            if (!empty($menuID)) {
                $params['Itemid'] = $menuID;
            }

            $js = '';
        } else {
            $js = 'onclick="document.getElementById(\'languageTag\').value=\'XX\';';
            $js .= 'document.getElementById(\'adminForm\').submit();';
        }

        $current            = self::getShortTag();
        $supportedLanguages = ['en' => 'COM_THM_ORGANIZER_ENGLISH', 'de' => 'COM_THM_ORGANIZER_GERMAN'];
        $languageSwitches   = [];

        foreach ($supportedLanguages as $tag => $constant) {
            if ($current != $tag) {
                $params['languageTag'] = $tag;

                $mechanism = $link ?
                    'href="index.php?' . JUri::buildQuery($params) . '"' : str_replace('XX', $tag, $js);

                $switch = '<a ' . $mechanism . '">';
                $switch .= '<span class="icon-world"></span> ' . self::getLanguage()->_($constant);
                $switch .= '</a>';

                $languageSwitches[] = $switch;
            }
        }

        return $languageSwitches;
    }

    /**
     * Extends the tag to the regular language constant.
     *
     * @param string $shortTag the short tag for the language
     *
     * @return string the longTag
     */
    private static function resolveShortTag($shortTag = 'de')
    {
        switch ($shortTag) {
            case 'en':
                $tag = 'en-GB';
                break;
            case 'de':
            default:
                $tag = 'de-DE';
        }

        return $tag;
    }
}
