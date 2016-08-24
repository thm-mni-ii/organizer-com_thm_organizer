<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperTeachers
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once 'department_resources.php';

/**
 * Provides validation methods for xml teacher objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperTeachers
{
	/**
	 * Checks for the teacher entry in the database, creating it as necessary. Adds the id to the teacher entry in the
	 * schedule.
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   string $gpuntisID      the teacher's gpuntis ID
	 *
	 * @return  void  sets the id if the teacher could be resolved/added
	 */
	public static function getID($gpuntisID, $data)
	{
		$teacherTable   = JTable::getInstance('teachers', 'thm_organizerTable');
		$loadCriteria   = array();
		$loadCriteria[] = array('gpuntisID' => $gpuntisID);

		if (!empty($data->username))
		{
			$loadCriteria[] = array('username' => $data->username);
		}
		if (!empty($data->forename))
		{
			$loadCriteria[] = array('surname' => $data->surname, 'forename' => $data->forename);
		}

		foreach ($loadCriteria as $criteria)
		{
			try
			{
				$success = $teacherTable->load($criteria);
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

				return;
			}

			if ($success)
			{
				return $teacherTable->id;
			}
		}

		// Entry not found
		$success = $teacherTable->save($data);
		return $success? $teacherTable->id : null;
	}
}
