<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerHelperLanguage
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
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
	 * Sets the Joomla Language based on input from the language switch
	 *
	 * @return  JLanguage
	 */
	public static function getLanguage()
	{
		$requested          = self::getShortTag();
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
	 * Retrieves the two letter language identifier
	 *
	 * @return  string
	 */
	public static function getLongTag()
	{
		return self::resolveShortTag(self::getShortTag());
	}

	/**
	 * Retrieves the two letter language identifier
	 *
	 * @return  string
	 */
	public static function getShortTag()
	{
		$menu = JFactory::getApplication()->getMenu()->getActive();

		if (!empty($menu))
		{
			$initialTag = $menu->params->get('initialLanguage', 'de');
			$shortTag = JFactory::getApplication()->input->get('languageTag', $initialTag);

			return $shortTag;
		}

		$fullTag  = JFactory::getLanguage()->getTag();
		$tagParts = explode('-', $fullTag);
		$shortTag = JFactory::getApplication()->input->get('languageTag', $tagParts[0]);

		return $shortTag;
	}

	/**
	 * Sets the language to the one requested. This can only be called after getLanguage().
	 *
	 * @param array $params the configuration parameters
	 *
	 * @return  array  html links for language redirection
	 */
	public static function getLanguageSwitches($params)
	{
		$params['option'] = 'com_thm_organizer';

		$input  = JFactory::getApplication()->input;
		$menuID = $input->getInt('Itemid', 0);
		if (!empty($menuID))
		{
			$params['Itemid'] = $menuID;
		}

		$requested = $input->getString('languageTag', '');

		$languageSwitches   = array();
		$current            = empty($requested) ? self::getShortTag() : $requested;
		$supportedLanguages = array('en' => 'COM_THM_ORGANIZER_ENGLISH', 'de' => 'COM_THM_ORGANIZER_GERMAN');

		foreach ($supportedLanguages AS $tag => $constant)
		{
			if ($current != $tag)
			{
				$params['languageTag'] = $tag;

				$js = 'onclick="$(\'#languageTag\').val(\'' . $tag . '\');$(\'#adminForm\').submit();"';
				$url = 'href="index.php?' . JUri::buildQuery($params) . '"';
				$mechanism = !empty($params['form']) ? $js : $url;
				$switch    = '<a ' . $mechanism . '"><span class="icon-world"></span> ' . self::getLanguage()->_($constant) . '</a>';
				$languageSwitches[]    = $switch;
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
		switch ($shortTag)
		{
			case 'en':
				$tag = 'en-GB';
				break;
			case 'de':
			default:
				$tag = 'de-DE';
		}

		return $tag;
	}

	/**
	 * Implementation of JText sprintf for language helper
	 *
	 * @param string $string The format string.
	 *
	 * @return  string  The translated strings or the key if 'script' is true in the array of options.
	 */
	public static function sprintf($string)
	{
		$lang = self::getLanguage();
		$args = func_get_args();
		$count = count($args);

		if ($count < 1)
		{
			return '';
		}

		if (is_array($args[$count - 1]))
		{
			$args[0] = $lang->_(
				$string, array_key_exists('jsSafe', $args[$count - 1]) ? $args[$count - 1]['jsSafe'] : false,
				array_key_exists('interpretBackSlashes', $args[$count - 1]) ? $args[$count - 1]['interpretBackSlashes'] : true
			);
		}
		else
		{
			$args[0] = $lang->_($string);
		}

		// Replace custom named placeholders with sprintf style placeholders
		$args[0] = preg_replace('/\[\[%([0-9]+):[^\]]*\]\]/', '%\1$s', $args[0]);

		return call_user_func_array('sprintf', $args);
	}
}