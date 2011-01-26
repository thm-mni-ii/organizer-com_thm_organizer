<?php
/**
 * 
 * view.html.php
 * view = viewnote
 * 
 */
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for the Giessen Scheduler Component
 *
 * @package    Giessen Scheduler
 */
 
class thm_organizerViewICalTest extends JView
{
    function display($tpl = null)
    {
    	$model =& $this->getModel();
		$ical = $model->getiCal();
		var_dump($ical);
        
        parent::display($tpl);
    }
    
}