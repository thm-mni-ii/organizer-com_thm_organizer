<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        JFormFieldProgramID
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
JFormHelper::loadFieldClass('list');

/**
 * Class creates a form field for subject-degree program association
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class JFormFieldProgramID extends JFormFieldList
{
	/**
	 * @var  string
	 */
	protected $type = 'programID';

	/**
	 * Returns a select box where stored degree programs can be chosen
	 *
	 * @return  array  the available degree programs
	 */
	public function getOptions()
	{
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$dbo      = JFactory::getDbo();
		$query    = $dbo->getQuery(true);

		$nameParts  = ["dp.name_$shortTag", 'd.abbreviation', 'dp.version'];
		$nameSelect = $query->concatenate($nameParts, ', ') . " AS text";

		$query->select("dp.id AS value, dp.name_$shortTag AS name, d.abbreviation AS degree, dp.version");
		$query->from('#__thm_organizer_programs AS dp');
		$query->innerJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
		$query->innerJoin('#__thm_organizer_mappings AS m ON dp.id = m.programID');
		$query->order('name ASC, degree ASC, version DESC');
		$dbo->setQuery($query);

		try
		{
			$programs = $dbo->loadAssocList();
		}
		catch (Exception $exc)
		{
			return parent::getOptions();
		}

		// Whether or not the program display should be prefiltered according to user resource access
		$access  = $this->getAttribute('access', 'false') == 'true';
		$unique  = $this->getAttribute('unique', 'false') == 'true';
		$uniqueNames = [];
		$options = [];

		foreach ($programs as $program)
		{
			$index = "{$program['name']} {$program['degree']}";

			if ($unique AND in_array($index, $uniqueNames))
			{
				continue;
			}
			else
			{
				$uniqueNames[$index] = $index;
			}

			$text = "{$program['name']}, {$program['degree']} ({$program['version']})";

			if (!$access OR THM_OrganizerHelperComponent::allowResourceManage('program', $program['value'], 'manage'))
			{
				$options[] = JHtml::_('select.option', $program['value'], $text);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
