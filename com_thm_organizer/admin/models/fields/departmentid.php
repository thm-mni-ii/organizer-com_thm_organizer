<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldDepartmentID
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
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldDepartmentID extends JFormFieldList
{
	/**
	 * @var  string
	 */
	protected $type = 'departmentID';

	/**
	 * Returns an array of options
	 *
	 * @return  array  the department options
	 */
	public function getOptions()
	{
		$allowedIDs = THM_OrganizerHelperComponent::getAccessibleDepartments();
		if (empty($allowedIDs))
		{
			return parent::getOptions();
		}

		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$dbo      = JFactory::getDbo();
		$query    = $dbo->getQuery(true);
		$query->select("id AS value, short_name_$shortTag AS text");
		$query->from('#__thm_organizer_departments');
		$query->where("id IN ( '" . implode("', '", $allowedIDs) . "' )");
		$query->order('text ASC');
		$dbo->setQuery((string) $query);

		try
		{
			$departments = $dbo->loadAssocList();
		}
		catch (Exception $exc)
		{
			return parent::getOptions();
		}

		$options = array();
		foreach ($departments as $department)
		{
			$options[] = JHtml::_('select.option', $department['value'], $department['text']);
		}

		return array_merge(parent::getOptions(), $options);
	}
}
