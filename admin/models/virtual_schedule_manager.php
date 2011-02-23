<?php
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelvirtual_schedule_manager extends JModel
{
	 /* Items total
     * @var integer
     */
  	var $_total = null;

  	/**
  	 * Pagination object
  	 * @var object
  	 */
  	var $_pagination = null;

	function __construct(){
 		parent::__construct();

		$mainframe = JFactory::getApplication("administrator");
		$option = $mainframe->scope;
		$view = JRequest::getString('view');

		// Get pagination request variables
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option.$view.'.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
  	}

	function _buildQuery()
	{
		$mainframe = JFactory::getApplication("administrator");
		$option = $mainframe->scope;
		$view = JRequest::getString('view');

		$filter_order		= $mainframe->getUserStateFromRequest( "$option.$view.filter_order",		'filter_order',		"#__thm_organizer_virtual_schedules.sid, #__thm_organizer_virtual_schedules.vid", 'string' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.$view.filter_order_Dir",	'filter_order_Dir',	"", 'string' );
		$filter_type		= $mainframe->getUserStateFromRequest( "$option.$view.filter_type",		'filter_type', 		0,			'string' );
		$filter_logged		= $mainframe->getUserStateFromRequest( "$option.$view.filter_logged",		'filter_logged', 	0,			'int' );
		$filter 			= $mainframe->getUserStateFromRequest( $option.$view.'.filter', 'filter', '', 'int' );
		$search 			= $mainframe->getUserStateFromRequest( $option.$view.'.search', 'search', '', 'string' );
		$groupFilter 		= $mainframe->getUserStateFromRequest( $option.$view.'.groupFilters', 'groupFilters', '', 'int' );
		$rolesFilter 		= $mainframe->getUserStateFromRequest( $option.$view.'.rolesFilters', 'rolesFilters', '', 'int' );
		$search 			= $this->_db->getEscaped( trim(JString::strtolower( $search ) ) );

		if (!$filter_order) { $filter_order = '#__thm_organizer_virtual_schedules.sid, #__thm_organizer_virtual_schedules.vid'; }
		if (!$filter_order_Dir) {$filter_order_Dir = ''; }

		$orderby     = "\n ORDER BY $filter_order $filter_order_Dir";

	      $query='SELECT DISTINCT ' .
	      		'#__thm_organizer_virtual_schedules.vid as id, #__thm_organizer_virtual_schedules.vname as name,' .
	      		'vtype as type, #__users.name as responsible,' .
	      		' department as department,' .
	      		'CONCAT(#__thm_organizer_semester.orgunit, "-",#__thm_organizer_semester.semester, " (", #__thm_organizer_semester.author, ")" ) as semesterid, #__thm_organizer_virtual_schedules.sid as sid' .
	      		' FROM #__thm_organizer_virtual_schedules' .
	      		' INNER JOIN #__thm_organizer_virtual_schedules_elements' .
	      		' ON #__thm_organizer_virtual_schedules.vid = #__thm_organizer_virtual_schedules_elements.vid' .
	      		' INNER JOIN #__thm_organizer_semester' .
	      		' ON #__thm_organizer_virtual_schedules.sid = #__thm_organizer_semester.sid' .
	      		' INNER JOIN #__users' .
	      		' ON #__thm_organizer_virtual_schedules.vresponsible = #__users.username' .
	      		' WHERE #__thm_organizer_virtual_schedules.sid = #__thm_organizer_virtual_schedules_elements.sid';

			$searchUm = str_replace("Ö", "&Ouml;", $search);
			$searchUm = str_replace("ö", "&öuml;", $searchUm);
			$searchUm = str_replace("Ä", "&Auml;", $searchUm);
			$searchUm = str_replace("ä", "&auml;", $searchUm);
			$searchUm = str_replace("Ü", "&Uuml;", $searchUm);
			$searchUm = str_replace("ü", "&uuml;", $searchUm);

			$searchUm2 = str_replace("Ã¶", "&Ouml;", $search);
			$searchUm2 = str_replace("Ã¶", "&öuml;", $searchUm2);
			$searchUm2 = str_replace("Ã¤", "&Auml;", $searchUm2);
			$searchUm2 = str_replace("Ã¤", "&auml;", $searchUm2);
			$searchUm2 = str_replace("Ã¼", "&Uuml;", $searchUm2);
			$searchUm2 = str_replace("Ã¼", "&uuml;", $searchUm2);

			$query.= ' AND (LOWER(#__thm_organizer_virtual_schedules.vname) LIKE \'%'.$search.'%\' ';
			$query.= ' OR LOWER(#__thm_organizer_virtual_schedules.vresponsible) LIKE \'%'.$search.'%\' ';
			$query.= ' OR LOWER(#__thm_organizer_virtual_schedules.department) LIKE \'%'.$search.'%\' ';
			$query.= ' OR LOWER(#__thm_organizer_virtual_schedules.vname) LIKE \'%'.$searchUm.'%\' ';
			$query.= ' OR LOWER(#__thm_organizer_virtual_schedules.vresponsible) LIKE \'%'.$searchUm.'%\' ';
			$query.= ' OR LOWER(#__thm_organizer_virtual_schedules.department) LIKE \'%'.$searchUm.'%\' ';
			$query.= ' OR LOWER(#__thm_organizer_virtual_schedules.vname) LIKE \'%'.$searchUm2.'%\' ';
			$query.= ' OR LOWER(#__thm_organizer_virtual_schedules.vresponsible) LIKE \'%'.$searchUm2.'%\' ';
			$query.= ' OR LOWER(#__thm_organizer_virtual_schedules.department) LIKE \'%'.$searchUm2.'%\') ';

		if ($groupFilter>0) {
			$query.= ' AND #__thm_organizer_virtual_schedules.vtype = ' . $groupFilter . ' ';
			//$this->setState('limit', 0);
			//$this->setState('limitstart', 0);
		}

		if ($rolesFilter>0) {
			$query.= ' AND #__thm_organizer_virtual_schedules.sid = ' . $rolesFilter . ' ';
			//$this->setState('limit', 0);
			//$this->setState('limitstart', 0);
		}

		$query.= $orderby;
       //'order by '.$orderby;
       return $query;
	}

	function getData() {
		// Lets load the data if it doesn't already exist
		if (empty( $this->_data ))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		if(!is_array($this->_data))
			$this->_data = array();
		return $this->_data;
	}

	function getTotal() {
 		// Load the content if it doesn't already exist
 		if (empty($this->_total)) {
 		    $db =& JFactory::getDBO();

 		    $query = 'SELECT count(*) as anzahl FROM #__thm_organizer_virtual_schedules';
			$db->setQuery($query);
			$rows = $db->loadObjectList();
 		}
 		return $rows[0]->anzahl;
  	}

  	function getAnz() {
 		$query = 'SELECT count(*) as anzahl FROM #__thm_organizer_virtual_schedules';
	    $db =& JFactory::getDBO();
		$db->setQuery($query);
		$rows = $db->loadObjectList();
 		return $rows[0]->anzahl;
  	}

  	function getPagination() {
 		// Load the content if it doesn't already exist
 		if (empty($this->_pagination)) {
 		    jimport('joomla.html.pagination');
 		    $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
 		}
 		return $this->_pagination;
  	}

  	function getElements()
  	{
  		$query = 'SELECT * FROM #__thm_organizer_virtual_schedules_elements';
	    $db =& JFactory::getDBO();
		$db->setQuery($query);
		$rows = $db->loadObjectList();
 		return $rows;
  	}
}
?>
