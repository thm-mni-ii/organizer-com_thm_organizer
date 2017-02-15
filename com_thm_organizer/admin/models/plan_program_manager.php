<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPlan_Program_Manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class provides methods to deal with plan_programs
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPlan_Program_Manager extends THM_OrganizerModelList
{
	protected $defaultOrdering = 'ppr.gpuntisID';

	protected $defaultDirection = 'asc';

	/**
	 * Constructor to set the config array and call the parent constructor
	 *
	 * @param array $config Configuration  (default: array)
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Method to get all plan_programs from the database
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$query    = $this->_db->getQuery(true);

		$select    = "ppr.id, ppr.gpuntisID, ppr.name, pr.name_$shortTag AS prName, pr.version, d.abbreviation AS abbreviation, ";
		$linkParts = array("'index.php?option=com_thm_organizer&view=plan_program_edit&id='", "ppr.id");
		$select .= $query->concatenate($linkParts, "") . " AS link";
		$query->select($select);

		$query->from('#__thm_organizer_plan_programs AS ppr');
		$query->leftJoin('#__thm_organizer_programs AS pr ON ppr.programID = pr.id');
		$query->leftJoin('#__thm_organizer_degrees AS d ON pr.degreeID = d.id');

		$departmentID = $this->state->get('list.departmentID');

		if ($departmentID)
		{
			$query->innerJoin("#__thm_organizer_department_resources AS dr ON dr.programID = ppr.id");
			$query->where("dr.departmentID = '$departmentID'");
		}

		$searchColumns = array('ppr.name', 'ppr.gpuntisID');
		$this->setSearchFilter($query, $searchColumns);

		$this->setOrdering($query);

		return $query;
	}

	/**
	 * Method to overwrite the getItems method in order to set the program name
	 *
	 * @return  array  an array of objects fulfilling the request criteria
	 */
	public function getItems()
	{
		$items  = parent::getItems();
		$return = array();

		if (empty($items))
		{
			return $return;
		}

		$index = 0;

		foreach ($items as $item)
		{
			$return[$index]              = array();
			$return[$index]['checkbox']  = JHtml::_('grid.id', $index, $item->id);
			$return[$index]['gpuntisID'] = JHtml::_('link', $item->link, $item->gpuntisID);
			$return[$index]['name']      = JHtml::_('link', $item->link, $item->name);
			$index++;
		}

		return $return;
	}

	/**
	 * Function to get table headers
	 *
	 * @return array including headers
	 */
	public function getHeaders()
	{
		$ordering             = $this->state->get('list.ordering', $this->defaultOrdering);
		$direction            = $this->state->get('list.direction', $this->defaultDirection);
		$headers              = array();
		$headers['checkbox']  = '';
		$headers['gpuntisID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_GPUNTISID', 'ppr.gpuntisID', $direction, $ordering);
		$headers['name']      = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DISPLAY_NAME', 'ppr.name', $direction, $ordering);

		return $headers;
	}
}
