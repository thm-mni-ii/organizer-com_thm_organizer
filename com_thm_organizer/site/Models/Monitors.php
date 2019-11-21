<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use JDatabaseQuery;
use Organizer\Helpers\Input;

/**
 * Class retrieves information for a filtered set of monitors.
 */
class Monitors extends ListModel
{
	protected $defaultOrdering = 'r.name';

	protected $filter_fields = ['content', 'display', 'useDefaults'];

	/**
	 * Method to get a list of resources from the database.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$query = $this->_db->getQuery(true);

		$select = 'm.id, r.name, m.ip, m.useDefaults, m.display, m.content, ';
		$parts  = ["'index.php?option=com_thm_organizer&view=monitor_edit&id='", 'm.id'];
		$select .= $query->concatenate($parts, '') . ' AS link ';
		$query->select($this->state->get('list.select', $select));
		$query->from('#__thm_organizer_monitors AS m');
		$query->leftJoin('#__thm_organizer_rooms AS r ON r.id = m.roomID');

		$this->setSearchFilter($query, ['r.name', 'm.ip']);
		$this->setValueFilters($query, ['useDefaults']);
		$this->addDisplayFilter($query);
		$this->addContentFilter($query);

		$this->setOrdering($query);

		return $query;
	}

	/**
	 * Adds the filter settings for display behaviour
	 *
	 * @param   object &$query  the query object
	 *
	 * @return void
	 */
	private function addDisplayFilter(&$query)
	{
		$requestDisplay = $this->state->get('filter.display', '');

		if ($requestDisplay === '')
		{
			return;
		}

		$where = "m.display ='$requestDisplay'";

		$params              = Input::getParams();
		$defaultDisplay      = $params->get('display', '');
		$useComponentDisplay = (!empty($defaultDisplay) and $requestDisplay == $defaultDisplay);
		if ($useComponentDisplay)
		{
			$query->where("( $where OR useDefaults = '1')");

			return;
		}

		$query->where($where);
	}

	/**
	 * Adds the filter settings for displayed content
	 *
	 * @param   object &$query  the query object
	 *
	 * @return void
	 */
	private function addContentFilter(&$query)
	{
		$params         = Input::getParams();
		$requestContent = $this->state->get('filter.content', '');

		if ($requestContent === '')
		{
			return;
		}

		$requestContent = $requestContent == '-1' ? '' : $requestContent;
		$where          = "m.content ='$requestContent'";

		$defaultContent      = $params->get('content', '');
		$useComponentContent = ($requestContent == $defaultContent);
		if ($useComponentContent)
		{
			$query->where("( $where OR useDefaults = '1')");

			return;
		}

		$query->where($where);
	}
}
