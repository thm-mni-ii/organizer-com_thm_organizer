<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        room display model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Loads lesson and event data for a single room and day into view context
 *
 * @category  Joomla.Component.Site
 * @package   thm_organizer
 */
class THM_OrganizerViewRoom_Display extends JViewLegacy
{
    /**
     * Loads persistent data into the view context
     *
     * @param   string  $tpl  the name of the template to load
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();
        $this->model = $model;
        $this->setLayout($model->layout);
        if ($model->layout == 'default' or $model->layout == 'registered' or $model->layout == 'events')
        {
            $document = JFactory::getDocument();
            $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");
            if ($model->layout == 'registered' OR $model->layout == 'events' OR $model->layout == 'content')
            {
                $document->addStyleSheet($this->baseurl . "/media/com_thm_organizer/css/room_display.css");
                $this->schedule_refresh = $model->schedule_refresh;
            }

            $this->roomName = $model->roomName;
            $this->date = $model->date;
            if (count($model->blocks))
            {
                $this->blocks = $model->blocks;
                $this->lessonsExist = $model->lessonsExist;
            }
            $this->eventsExist = $model->eventsExist;
            $this->appointments = $model->appointments;
            $this->notices = $model->notices;
            $this->information = $model->information;
            $this->upcoming = $model->upcoming;
        }
        elseif ($model->layout == 'content')
        {
            $this->content = $model->content;
            $this->content_refresh = $model->content_refresh;
        }
        parent::display($tpl);
    }

}
