<?php
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class THM_OrganizersModelResource_Teacher_Manager extends JModel
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

		$filter_order		= $mainframe->getUserStateFromRequest( "$option.$view.filter_order",		'filter_order',		"#__thm_organizer_teachers.id, #__thm_organizer_teachers.name", 'string' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.$view.filter_order_Dir",	'filter_order_Dir',	"", 'string' );
		$filter_type		= $mainframe->getUserStateFromRequest( "$option.$view.filter_type",		'filter_type', 		0,			'string' );
		$filter_logged		= $mainframe->getUserStateFromRequest( "$option.$view.filter_logged",		'filter_logged', 	0,			'int' );
		$filter 			= $mainframe->getUserStateFromRequest( $option.$view.'.filter', 'filter', '', 'int' );
		$search 			= $mainframe->getUserStateFromRequest( $option.$view.'.search', 'search', '', 'string' );
		$search 			= $this->_db->getEscaped( trim(JString::strtolower( $search ) ) );

		if (!$filter_order) { $filter_order = '#__thm_organizer_teachers.id, #__thm_organizer_teachers.name'; }
		if (!$filter_order_Dir) {$filter_order_Dir = ''; }

		$orderby     = "\n ORDER BY $filter_order $filter_order_Dir";

	    $query="SELECT #__thm_organizer_teachers.id, #__thm_organizer_teachers.gpuntisID, #__thm_organizer_teachers.name, #__users.name AS userName, #__thm_organizer_departments.name AS dptName " .
               "FROM #__thm_organizer_teachers " .
               "INNER JOIN #__thm_organizer_departments " .
               "ON #__thm_organizer_teachers.id = #__thm_organizer_departments.id " .
               "INNER JOIN #__users " .
               "ON #__thm_organizer_teachers.manager = #__users.id " .
               "WHERE ";

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

		$query.= ' LOWER(#__thm_organizer_teachers.name) LIKE \'%'.$search.'%\' ';
		$query.= ' OR LOWER(#__users.name) LIKE \'%'.$search.'%\' ';
		$query.= ' OR LOWER(#__thm_organizer_departments.name) LIKE \'%'.$search.'%\' ';

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

 		    $query = 'SELECT count(*) as anzahl FROM #__thm_organizer_teachers';
			$db->setQuery($query);
			$rows = $db->loadObjectList();
 		}
 		return $rows[0]->anzahl;
  	}

  	function getAnz() {
 		$query = 'SELECT count(*) as anzahl FROM #__thm_organizer_teachers';
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
}
?>
