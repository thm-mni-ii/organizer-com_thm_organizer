<?php
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for the Giessen Scheduler Component
 *
 * @package    Giessen Scheduler
 */
 
class thm_organizerViewScheduleList extends JView
{
    function display($tpl = null)
    {
		JHTML::_('behavior.tooltip');
		
    	$model =& $this->getModel();
		$schedules = $model->getSchedules();
        $this->assignRef( 'schedules', $schedules );
        $links = $this->buildLinks($schedules);
        $this->assignRef('links', $links);
        $this->assignRef('sid', JRequest::getVar('semesterid'));
        parent::display($tpl);
    }
    
    function buildLinks($schedules)
    {//JRoute::_()
		JHTML::_('behavior.tooltip');
		$links = array();
		if($schedules != "empty")
			foreach($schedules as $schedule)
			{		
		    	$attribs = 'style="height: 16px; width: 16px;"';
				$image = JHTML::_('image.site', 'publish.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, NULL, $attribs);
				$tiptext = JText::_( 'Diesen Stundenplan ver&ouml;ffentlichen' );
				$tiptitle = JText::_( 'Publish' );
				$link 	= 'index.php?controller=schedulelist&view=schedulelist&task=schedule_publish&schedule_id='.$schedule->id.'&semesterid='.$schedule->sid;
				$links['publish'][$schedule->id] = '<a href="'.JRoute::_($link).'" class="editNoteLink hasTip" title="'.$tiptitle.'::'.$tiptext.'">'.$image.'</a>';
				$image = JHTML::_('image.site', 'unpublish.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, NULL, $attribs);
				$tiptext = JText::_( 'Diesen Stundenplan unver&ouml;ffentlichen' );
				$tiptitle = JText::_( 'Unpublish' );
				$link 	= 'index.php?controller=schedulelist&view=schedulelist&task=schedule_unpublish&schedule_id='.$schedule->id.'&semesterid='.$schedule->sid;
				$links['unpublish'][$schedule->id] = '<a href="'.JRoute::_($link).'" class="editNoteLink hasTip" title="'.$tiptitle.'::'.$tiptext.'">'.$image.'</a>';
				$image = JHTML::_('image.site', 'delete.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, NULL, $attribs);
				$tiptext = JText::_( 'Diesen Stundenplan l&ouml;schen' );
				$tiptitle = JText::_( 'L&ouml;schen' );
				$link 	= 'index.php?controller=schedulelist&view=schedulelist&task=schedule_delete&schedule_id='.$schedule->id.'&semesterid='.$schedule->sid;
				$links['delete'][$schedule->id] = '<a href="'.JRoute::_($link).'" class="editNoteLink hasTip" title="'.$tiptitle.'::'.$tiptext.'">'.$image.'</a>';
			}

		return $links;
    }
}
?>