<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

/**
 * Class loads a room's daily schedule into the display context.
 */
class THM_OrganizerViewRoom_Display extends \Joomla\CMS\MVC\View\HtmlView
{
    /**
     * Loads persistent data into the view context
     *
     * @param string $tpl the name of the template to load
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();
        $model       = $this->getModel();
        $this->model = $model;
        $layout      = $model->params['layout'];
        $this->setLayout($layout);
        parent::display($tpl);
    }

    /**
     * Adds css and javascript files to the document
     *
     * @return void  modifies the document
     */
    private function modifyDocument()
    {
        $document = \JFactory::getDocument();
        $document->addStyleSheet(\JUri::root() . '/media/com_thm_organizer/css/room_display.css');
    }
}
