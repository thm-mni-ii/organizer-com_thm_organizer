<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;

/**
 * Provides general functions for language data retrieval and display.
 */
class Languages extends Text
{
    /**
     * Translate function, mimics the php gettext (alias _) function.
     *
     * The function checks if $jsSafe is true, then if $interpretBackslashes is true.
     *
     * @param string  $string               The string to translate
     * @param boolean $jsSafe               Make the result javascript safe
     * @param boolean $interpretBackSlashes Interpret \t and \n
     * @param boolean $script               To indicate that the string will be push in the javascript language store
     *
     * @return  string  The translated string or the key if $script is true
     */
    public static function _($string, $jsSafe = false, $interpretBackSlashes = true, $script = false)
    {
        if (is_array($jsSafe)) {
            if (array_key_exists('interpretBackSlashes', $jsSafe)) {
                $interpretBackSlashes = (boolean)$jsSafe['interpretBackSlashes'];
            }

            if (array_key_exists('script', $jsSafe)) {
                $script = (boolean)$jsSafe['script'];
            }

            $jsSafe = array_key_exists('jsSafe', $jsSafe) ? (boolean)$jsSafe['jsSafe'] : false;
        }

        if (self::passSprintf($string, $jsSafe, $interpretBackSlashes, $script)) {
            return $string;
        }

        $lang = self::getLanguage();

        if ($script) {
            static::$strings[$string] = $lang->_($string, $jsSafe, $interpretBackSlashes);

            return $string;
        }

        return $lang->_($string, $jsSafe, $interpretBackSlashes);
    }

    /**
     * Returns a language instance based on user input.
     *
     * @return Language
     */
    private static function getLanguage()
    {
        $shortTag = self::getShortTag();
        switch ($shortTag) {
            case 'en':
                $language = Language::getInstance('en-GB');
                break;
            case 'de':
            default:
                $language = Language::getInstance('de-DE');
                break;
        }

        $language->load('com_thm_organizer', JPATH_ADMINISTRATOR . '/components/com_thm_organizer');

        return $language;
    }

    /**
     * Retrieves the two letter language identifier
     *
     * @return string
     */
    public static function getShortTag()
    {
        $input        = OrganizerHelper::getInput();
        $requestedTag = $input->get('languageTag');

        if (!empty($requestedTag)) {
            return $requestedTag;
        }

        $fullTag    = Factory::getLanguage()->getTag();
        $defaultTag = explode('-', $fullTag)[0];
        $params     = OrganizerHelper::getParams();

        return empty($params->get('initialLanguage')) ? $defaultTag : $params->get('initialLanguage');
    }

    /**
     * Checks the string if it should be interpreted as sprintf and runs sprintf over it.
     *
     * @param string   &$string               The string to translate.
     * @param mixed     $jsSafe               Boolean: Make the result javascript safe.
     * @param boolean   $interpretBackSlashes To interpret backslashes (\\=\, \n=carriage return, \t=tabulation)
     * @param boolean   $script               To indicate that the string will be push in the javascript language store
     *
     * @return  boolean  Whether the string be interpreted as sprintf
     */
    private static function passSprintf(&$string, $jsSafe = false, $interpretBackSlashes = true, $script = false)
    {
        // Check if string contains a comma
        if (strpos($string, ',') === false) {
            return false;
        }

        $lang         = self::getLanguage();
        $string_parts = explode(',', $string);

        // Pass all parts through the Text translator
        foreach ($string_parts as $i => $str) {
            $string_parts[$i] = $lang->_($str, $jsSafe, $interpretBackSlashes);
        }

        $first_part = array_shift($string_parts);

        // Replace custom named placeholders with sprinftf style placeholders
        $first_part = preg_replace('/\[\[%([0-9]+):[^\]]*\]\]/', '%\1$s', $first_part);

        // Check if string contains sprintf placeholders
        if (!preg_match('/%([0-9]+\$)?s/', $first_part)) {
            return false;
        }

        $final_string = vsprintf($first_part, $string_parts);

        // Return false if string hasn't changed
        if ($first_part === $final_string) {
            return false;
        }

        $string = $final_string;

        if ($script) {
            foreach ($string_parts as $i => $str) {
                static::$strings[$str] = $string_parts[$i];
            }
        }

        return true;
    }

    /**
     * Passes a string thru an printf.
     *
     * Note that this method can take a mixed number of arguments as for the sprintf function.
     *
     * @param string $string The format string.
     *
     * @return  mixed
     */
    public static function printf($string)
    {
        $lang  = self::getLanguage();
        $args  = func_get_args();
        $count = count($args);

        if ($count < 1) {
            return '';
        }

        if (is_array($args[$count - 1])) {
            $args[0] = $lang->_(
                $string, array_key_exists('jsSafe', $args[$count - 1]) ? $args[$count - 1]['jsSafe'] : false,
                array_key_exists('interpretBackSlashes',
                    $args[$count - 1]) ? $args[$count - 1]['interpretBackSlashes'] : true
            );
        } else {
            $args[0] = $lang->_($string);
        }

        return call_user_func_array('printf', $args);
    }

    /**
     * Translate a string into the current language and stores it in the JavaScript language store.
     *
     * @param string  $string               The Text key.
     * @param boolean $jsSafe               Ensure the output is JavaScript safe.
     * @param boolean $interpretBackSlashes Interpret \t and \n.
     *
     * @return  string
     *
     * @since   11.1
     */
    public static function script($string = null, $jsSafe = false, $interpretBackSlashes = true)
    {
        // Normalize the key and translate the string.
        static::$strings[strtoupper($string)] = self::_($string);

        // Load core.js dependency
        HTML::_('behavior.core');

        // Update Joomla.JText script options
        Factory::getDocument()->addScriptOptions('joomla.jtext', static::$strings, false);

        return static::getScriptStrings();
    }

    /**
     * Passes a string thru a sprintf.
     *
     * Note that this method can take a mixed number of arguments as for the sprintf function.
     *
     * The last argument can take an array of options:
     *
     * array('jsSafe'=>boolean, 'interpretBackSlashes'=>boolean, 'script'=>boolean)
     *
     * where:
     *
     * jsSafe is a boolean to generate a javascript safe strings.
     * interpretBackSlashes is a boolean to interpret backslashes \\->\, \n->new line, \t->tabulation.
     * script is a boolean to indicate that the string will be push in the javascript language store.
     *
     * @param string $string The format string.
     *
     * @return  string  The translated strings or the key if 'script' is true in the array of options.
     */
    public static function sprintf($string)
    {
        $lang  = self::getLanguage();
        $args  = func_get_args();
        $count = count($args);

        if ($count < 1) {
            return '';
        }

        if (is_array($args[$count - 1])) {
            $args[0] = $lang->_(
                $string, array_key_exists('jsSafe', $args[$count - 1]) ? $args[$count - 1]['jsSafe'] : false,
                array_key_exists('interpretBackSlashes', $args[$count - 1]) ?
                    $args[$count - 1]['interpretBackSlashes'] : true
            );

            if (array_key_exists('script', $args[$count - 1]) && $args[$count - 1]['script']) {
                static::$strings[$string] = call_user_func_array('sprintf', $args);

                return $string;
            }
        } else {
            $args[0] = $lang->_($string);
        }

        // Replace custom named placeholders with sprintf style placeholders
        $args[0] = preg_replace('/\[\[%([0-9]+):[^\]]*\]\]/', '%\1$s', $args[0]);

        return call_user_func_array('sprintf', $args);
    }
}
