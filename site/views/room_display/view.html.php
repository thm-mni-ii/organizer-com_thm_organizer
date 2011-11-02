<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        room display view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');
class thm_organizerViewroom_display extends JView
{
    function display($tpl = null)
    {
        $model = $this->getModel();
        $this->setLayout($model->layout);
        if($model->layout == 'default' or $model->layout == 'registered')
        {
            $this->roomName = $model->roomName;
            $this->date = $model->date;
            if(count($model->blocks))
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
        else if($model->layout == 'content') $this->content = $model->content;
        parent::display($tpl);
    }
}