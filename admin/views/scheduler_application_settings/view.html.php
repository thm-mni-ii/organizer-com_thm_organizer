<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the  Giessen Scheduler Monitors screen
 *
 * @package Joomla
 * @subpackage  Giessen Scheduler
 */
class  thm_organizersViewScheduler_Application_Settings extends JView {

	function display($tpl = null)
	{
            JToolBarHelper::title( JText::_( 'Giessen Scheduler - Scheduler Application Settings' ), 'generic.png' );
    		JToolBarHelper::save();
            JToolBarHelper::back();


            //Create Submenu
            JSubMenuHelper::addEntry( JText::_( 'Main Menu' ), 'index.php?option=com_thm_organizer&view=thm_organizers');
            JSubMenuHelper::addEntry( JText::_( 'Category Manager' ), 'index.php?option=com_thm_organizer&view=category_list');
            JSubMenuHelper::addEntry( JText::_( 'Monitor Manager' ), 'index.php?option=com_thm_organizer&view=monitor_list');
            JSubMenuHelper::addEntry( JText::_( 'Semester Manager' ), 'index.php?option=com_thm_organizer&view=semester_list');
            JSubMenuHelper::addEntry( JText::_( 'Virtual Schedule' ), 'index.php?option=com_thm_organizer&view=virtualschedule');

	 		$model = $this->getModel();
	 		$settings = $model->getSettings();
	 		$this->assignRef('settings', $settings);
            $categories = $model->getCategories();
			$this->assignRef('categories', JHTML::_('select.genericlist', $categories, 'scheduler_vacationcat','size="1" class="inputbox"', 'id', 'name', $settings[0]->vacationcat));

            parent::display($tpl);
	}
}
