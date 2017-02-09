<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperSubjects
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once 'departments.php';

/**
 * Provides validation methods for xml subject objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperSubjects
{
	/**
	 * Retrieves the table id if existent.
	 *
	 * @param string $subjectIndex the subject index (dept. abbreviation + gpuntis id)
	 *
	 * @return mixed int id on success, otherwise null
	 */
	public static function getID($subjectIndex)
	{
		$table  = JTable::getInstance('plan_subjects', 'thm_organizerTable');
		$data   = array('subjectIndex' => $subjectIndex);
		$exists = $table->load($data);
		if ($exists)
		{
			return $exists ? $table->id : null;
		}

		return null;
	}

	/**
	 * Retrieves the (plan) subject name
	 *
	 * @param int    $subjectID the table id for the subject
	 * @param string $type      the type of the id (real or plan)
	 *
	 * @return array an array of program information
	 */
	public static function getName($subjectID, $type)
	{
		$dbo           = JFactory::getDbo();
		$languageTag   = THM_OrganizerHelperLanguage::getShortTag();

		$query     = $dbo->getQuery(true);
		$query->select("ps.name as psName, s.name_$languageTag as name");
		$query->from('#__thm_organizer_plan_subjects AS ps');

		if ($type == 'real')
		{
			$query->innerJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id');
			$query->innerJoin('#__thm_organizer_subjects AS s ON s.id = sm.subjectID');
			$query->where("s.id = '$subjectID'");
		}
		else
		{
			$query->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id');
			$query->leftJoin('#__thm_organizer_subjects AS s ON s.id = sm.subjectID');
			$query->where("ps.id = '$subjectID'");
		}

		$dbo->setQuery($query);

		try
		{
			$names = $dbo->loadAssoc();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return '';
		}

		return empty($names)? '' : empty($names['name'])? $names['psName'] : $names['name'];
	}

	/**
	 * Attempts to get the plan subject's id, creating it if non-existent.
	 *
	 * @param object $subject the subject object
	 *
	 * @return mixed int on success, otherwise null
	 */
	public static function getPlanResourceID($subjectIndex, $subject)
	{
		$subjectID = self::getID($subjectIndex);

		$table = JTable::getInstance('plan_subjects', 'thm_organizerTable');

		if (!empty($subjectID))
		{
			$table->load($subjectID);
		}

		$data                 = array();
		$data['subjectIndex'] = $subjectIndex;
		$data['gpuntisID']    = $subject->gpuntisID;

		if (!empty($subject->fieldID))
		{
			$data['fieldID'] = $subject->fieldID;
		}

		$data['subjectNo'] = $subject->subjectNo;
		$data['name']      = $subject->longname;

		$success = $table->save($data);

		return $success ? $table->id : null;

	}
}
