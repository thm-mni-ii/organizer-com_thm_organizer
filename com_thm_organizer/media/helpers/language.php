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
		$app                = JFactory::getApplication();
		$requested          = $app->input->get('languageTag', self::getShortTag());
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
		$menu = JFactory::getApplication()->getMenu()->getActive();

		if (!empty($menu))
		{
			$initialLanguage = $menu->params->get('initialLanguage', 'de');
			switch ($initialLanguage)
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

		return JFactory::getLanguage()->getTag();
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
			$initialLanguage = $menu->params->get('initialLanguage', 'de');

			if (!empty($initialLanguage))
			{
				return $initialLanguage;
			}
		}

		$fullTag  = JFactory::getLanguage()->getTag();
		$tagParts = explode('-', $fullTag);

		return $tagParts[0];
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
}