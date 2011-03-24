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
class  thm_organizersViewScheduler_Application_Settings extends JView {

	function display($tpl = null)
	{
            JToolBarHelper::title( JText::_( 'Giessen Scheduler - Scheduler Application Settings' ), 'generic.png' );

    		JToolBarHelper::save('scheduler_application_settings.save');
            JToolBarHelper::back();


            //Create Submenu
           	thm_organizerHelper::addSubmenu('scheduler_application_settings');

	 		$model = $this->getModel();
	 		$settings = $model->getSettings();
	 		$this->assignRef('settings', $settings);
            $categories = $model->getCategories();
			if($categories === false)
            	$this->assignRef('categories', JHTML::_('select.genericlist', array("no category found!"), 'scheduler_vacationcat','size="1" class="inputbox"', 'id', 'name'));
            else
            if($settings === false)
				$this->assignRef('categories', JHTML::_('select.genericlist', $categories, 'scheduler_vacationcat','size="1" class="inputbox"', 'id', 'name'));
			else
				$this->assignRef('categories', JHTML::_('select.genericlist', $categories, 'scheduler_vacationcat','size="1" class="inputbox"', 'id', 'name', $settings[0]->vacationcat));

            parent::display($tpl);
	}
}
