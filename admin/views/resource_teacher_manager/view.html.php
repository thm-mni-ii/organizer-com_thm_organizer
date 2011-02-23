<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

/**
 * View class for the  Giessen Scheduler Monitors screen
 *
 * @package Joomla
 * @subpackage  Giessen Scheduler
 */
class  THM_OrganizersViewResource_Teacher_Manager extends JView {

	function display($tpl = null)
	{
		//Create Toolbar
        JToolBarHelper::title( JText::_( 'THM - Organizer: Teacher Manager' ), 'generic.png' );
		JToolBarHelper::addNewX('virtual_schedule_manager.add');
        JToolBarHelper::editListX('virtual_schedule_manager.edit');
        /**
         * ToDo: Virtuelle Stundenpl�ne sollen kopiert werden k�nnen.
         */
        //JToolBarHelper::customX( 'copy', 'copy.png', 'copy_f2.png', JText::_('Copy') );
		JToolBarHelper::deleteListX('Really?');

        thm_organizerHelper::addSubmenu('');

		$mainframe = JFactory::getApplication("administrator");
		$option = $mainframe->scope;
		$view = JRequest::getString('view');
		$db  		= & JFactory::getDBO();

		$filter_order		= $mainframe->getUserStateFromRequest( "$option.$view.filter_order",		'filter_order',		'#__thm_organizer_teachers.id, #__thm_organizer_teachers.name', '' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.$view.filter_order_Dir",	'filter_order_Dir',	'', '' );
		$filter_type		= $mainframe->getUserStateFromRequest( "$option.$view.filter_type",		'filter_type', 		0,			'string' );
		$filter_logged		= $mainframe->getUserStateFromRequest( "$option.$view.filter_logged",		'filter_logged', 	0,			'int' );
		$filter 			= $mainframe->getUserStateFromRequest( $option.$view.'.filter', 'filter', '', 'int' );
		$search 			= $mainframe->getUserStateFromRequest( $option.$view.'.search', 'search', '', 'string' );
		$search 			= $db->getEscaped( trim(JString::strtolower( $search ) ) );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		$model =& $this->getModel();

		// Get data from the model
		$items =& $this->get('Data');

		$pagination = & $this->get('Pagination');

		// search filter
		$lists['search']= $search;

		//assign data to template
		$this->assignRef('lists'  , $lists);

		$this->assignRef( 'items', $items );
		$this->assignRef('pagination', $pagination);
		$this->assignRef('lists', $lists);

        parent::display($tpl);
	}
}
