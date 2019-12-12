<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
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
	 * @param   string   $string                The string to translate
	 * @param   boolean  $jsSafe                Make the result javascript safe
	 * @param   boolean  $interpretBackSlashes  Interpret \t and \n
	 * @param   boolean  $script                To indicate that the string will be push in the javascript language store
	 *
	 * @return  string  The translated string or the key if $script is true
	 */
	public static function _($string, $jsSafe = false, $interpretBackSlashes = true, $script = false)
	{
		if (is_array($jsSafe))
		{
			if (array_key_exists('interpretBackSlashes', $jsSafe))
			{
				$interpretBackSlashes = (boolean) $jsSafe['interpretBackSlashes'];
			}

			if (array_key_exists('script', $jsSafe))
			{
				$script = (boolean) $jsSafe['script'];
			}

			$jsSafe = array_key_exists('jsSafe', $jsSafe) ? (boolean) $jsSafe['jsSafe'] : false;
		}

		$language = self::getLanguage();

		if ($script)
		{
			static::$strings[$string] = $language->_($string, $jsSafe, $interpretBackSlashes);

			return $string;
		}

		return $language->_($string, $jsSafe, $interpretBackSlashes);
	}

	/**
	 * Returns a language constant corresponding to the given class name.
	 *
	 * @param   string  $className  the name of the class
	 *
	 * @return string the constant containing the resolved text for the calling class
	 */
	public static function getConstant($className)
	{
		$parts          = preg_split('/([A-Z][a-z]+)/', $className, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$delimitedParts = implode('_', $parts);

		return 'THM_ORGANIZER_' . strtoupper($delimitedParts);
	}

	/**
	 * Returns a language instance based on user input.
	 *
	 * @return Language
	 */
	private static function getLanguage()
	{
		$tag = self::getTag();
		switch ($tag)
		{
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
	public static function getTag()
	{
		$requestedTag = Input::getCMD('languageTag');

		if (!empty($requestedTag))
		{
			return $requestedTag;
		}

		$default = explode('-', Factory::getLanguage()->getTag())[0];

		return Input::getParams()->get('initialLanguage', $default);
	}

	/**
	 * Translate a string into the current language and stores it in the JavaScript language store.
	 *
	 * @param   string   $string                The Text key.
	 * @param   boolean  $jsSafe                Ensure the output is JavaScript safe.
	 * @param   boolean  $interpretBackSlashes  Interpret \t and \n.
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
	 * Converts a double colon separated string or 2 separate strings to a string ready for bootstrap tooltips
	 *
	 * @param   string   $title    The title of the tooltip (or combined '::' separated string).
	 * @param   string   $content  The content to tooltip.
	 * @param   boolean  $escape   If true will pass texts through htmlspecialchars.
	 *
	 * @return  string  The tooltip string
	 *
	 * @since   3.1.2
	 */
	public static function tooltip($title = '', $content = '', $escape = true)
	{
		// Initialise return value.
		$result = '';

		// Don't process empty strings
		if ($content !== '' or $title !== '')
		{
			$title   = self::_($title);
			$content = self::_($content);

			if ($title === '')
			{
				$result = $content;
			}
			elseif ($title === $content)
			{
				$result = '<strong>' . $title . '</strong>';
			}
			elseif ($content !== '')
			{
				$result = '<strong>' . $title . '</strong><br />' . $content;
			}
			else
			{
				$result = $title;
			}

			// Escape everything, if required.
			if ($escape)
			{
				$result = htmlspecialchars($result);
			}
		}

		return $result;
	}
}
