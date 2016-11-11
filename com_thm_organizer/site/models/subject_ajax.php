<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSubject_Ajax
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
/** @noinspection PhpIncludeInspection */
require_once JPATH_BASE . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Class provides methods for building a model of the curriculum in JSON format
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSubject_Ajax extends JModelLegacy
{
	/**
	 * Constructor to set up the class variables and call the parent constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Retrieves subject entries from the database
	 *
	 * @return  string  the subjects which fit the selected resource
	 */
	public function getSubjects()
	{
		$input     = JFactory::getApplication()->input;
		$programID = $input->getString('programID', '-1');
		$teacherID = $input->getString('teacherID', '-1');
		if ($programID == '-1' AND $teacherID == '-1')
		{
			return '[]';
		}

		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$lang   = JFactory::getApplication()->input->getString('languageTag', 'de');
		$select = "DISTINCT s.id, s.name_{$lang} AS name, s.externalID, s.creditpoints, ";
		$select .= "t.surname, t.forename, t.title, t.username ";
		$query->select($select);

		$query->from('#__thm_organizer_subjects AS s');

		$boundarySet = $this->getBoundaries();
		if (!empty($boundarySet))
		{
			$query->innerJoin('#__thm_organizer_mappings AS m ON m.subjectID = s.id');
			$where   = '';
			$initial = true;
			foreach ($boundarySet as $boundaries)
			{
				$where .= $initial ?
					"((m.lft >= '{$boundaries['lft']}' AND m.rgt <= '{$boundaries['rgt']}')"
					: " OR (m.lft >= '{$boundaries['lft']}' AND m.rgt <= '{$boundaries['rgt']}')";
				$initial = false;
			}

			$query->where($where . ')');
		}

		if ($teacherID != '-1')
		{
			$query->innerJoin('#__thm_organizer_subject_teachers AS st ON st.subjectID = s.id');
			$query->innerJoin('#__thm_organizer_teachers AS t ON st.teacherID = t.id');
			$query->where("st.teacherID = '$teacherID'");
		}
		else
		{
			$query->leftJoin('#__thm_organizer_subject_teachers AS st ON st.subjectID = s.id');
			$query->innerJoin('#__thm_organizer_teachers AS t ON st.teacherID = t.id');
			$query->where("st.teacherResp = '1'");
		}

		$query->order('name');
		$query->group('s.id');

		$dbo->setQuery((string) $query);
		try
		{
			$subjects = $dbo->loadObjectList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return '[]';
		}

		return empty($subjects) ? '[]' : json_encode($subjects);
	}

	/**
	 * Retrieves the left and right boundaries of the nested program or pool
	 *
	 * @return  array
	 */
	private function getBoundaries()
	{
		$input             = JFactory::getApplication()->input;
		$programID         = $input->getString('programID');
		$programBoundaries = THM_OrganizerHelperMapping::getBoundaries('program', $programID);

		if (empty($programBoundaries))
		{
			return array();
		}

		$poolID         = $input->getString('poolID');
		$poolBoundaries = ($poolID != '-1' AND $poolID != 'null') ?
			THM_OrganizerHelperMapping::getBoundaries('pool', $poolID) : array();

		$validPool = (!empty($poolBoundaries) AND $this->poolInProgram($poolBoundaries, $programBoundaries));
		if ($validPool)
		{
			return $poolBoundaries;
		}

		return $programBoundaries;
	}

	/**
	 * Checks whether the pool is subordinate to the selected program
	 *
	 * @param array $poolBoundaries    the pool's left and right values
	 * @param array $programBoundaries the program's left and right values
	 *
	 * @return  boolean  true if the pool is subordinate to the program,
	 *                   otherwise false
	 */
	private function poolInProgram($poolBoundaries, $programBoundaries)
	{
		$first = $poolBoundaries[0];
		$last  = end($poolBoundaries);

		$leftValid  = $first['lft'] > $programBoundaries[0]['lft'];
		$rightValid = $last['rgt'] < $programBoundaries[0]['rgt'];
		if ($leftValid AND $rightValid)
		{
			return true;
		}

		return false;
	}
}
