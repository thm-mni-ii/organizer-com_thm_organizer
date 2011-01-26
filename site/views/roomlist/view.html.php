<?php
/**
 * Room View Class for the Giessen Scheduler Component
 *
 * @package    Giessen Scheduler
 */
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
class thm_organizerViewRoomList extends JView
{
    function display($tpl = null)
    {
        $model =& $this->getModel();
        $rooms = $model->rooms;
        if(isset($rooms) && count($rooms) > 0)
        {
            $roomlist = JHTML::_('select.genericlist', $rooms, 'room','size="1"', 'room', 'room');
            $this->assignRef( 'roomlist', $roomlist);
            $calendar = JHTML::_('calendar', '', 'date', 'thm_organizer_rl_calendar', '%Y-%m-%d',
                                    array(
                                        'class'=>'hasTip inputbox',
                                        'title'=>'Format YYYY-MM-DD. Wenn leer wird das aktuelle Datum verwendet.',
                                        'size'=>'7',
                                        'maxlength'=>'10'
                                        )
                                );
            $this->assignRef( 'calendar', $calendar);
        }
        else
        {
            $nomonitors = 'Keine R&auml;ume/Monitoren sind eingetragen.';
            $this->assignRef( 'nomonitors', $nomonitors);
        }
        parent::display($tpl);
    }
}