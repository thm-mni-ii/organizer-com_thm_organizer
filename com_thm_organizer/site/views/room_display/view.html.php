<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewRoom_Display
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
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
        $this->modifyDocument();
        $model = $this->getModel();
        $this->model = $model;
        $layout = $model->params['layout'];
        $this->setLayout($layout);
        parent::display($tpl);
    }

    /**
     * Adds css and javascript files to the document
     *
     * @return  void  modifies the document
     */
    private function modifyDocument()
    {
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/media/com_thm_organizer/css/room_display.css");
    }
}
