<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/edit.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class provides methods for editing the subject table in frontend
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelPrep_Course_Edit extends THM_OrganizerModelEdit
{
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param string $name    The table name. Optional.
	 * @param string $prefix  The class prefix. Optional.
	 * @param array  $options Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 */
	public function getTable($name = '', $prefix = 'THM_OrganizerTable', $options = [])
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_thm_organizer/tables');

		return JTable::getInstance("subjects", $prefix, $options);
	}

	/**
	 *    Saves course data to database
	 *
	 * @param array $data     form data
	 * @param int   $lessonID id id for lesson to handle participants
	 *
	 * @return bool true on success, false on error
	 */
	public function save($data)
	{
		$lessonID = JFactory::getApplication()->input->getInt('lessonID', 0);

		if (THM_OrganizerHelperComponent::allowResourceManage('subject', $data["id"]))
		{
			$success = parent::save($data);
		}
		else
		{
			return false;
		}

		if (!empty($success) AND !empty($lessonID))
		{
			$model = JModelLegacy::getInstance('participant', 'THM_OrganizerModel');
			$model->moveUpWaitingUsers($lessonID);
		}

		return $success;
	}
}
