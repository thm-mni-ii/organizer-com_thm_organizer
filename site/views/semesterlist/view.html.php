<?php
/**
 * Room View Class for the Giessen Scheduler Component
 *
 * @package    Giessen Scheduler
 */
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
class thm_organizerViewSemesterList extends JView
{
    function display($tpl = null)
    {
        $model =& $this->getModel();
        $semesters =  $model->getSemesters();
        $this->assignRef( 'semesters', $semesters);
        parent::display($tpl);
    }
}