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

use \THM_OrganizerHelperHTML as HTML;

/**
 * Class loads filtered events into the display context.
 */
class THM_OrganizerViewEvent_List extends \Joomla\CMS\MVC\View\HtmlView
{
    public $form = null;

    public $lang = null;

    public $model;

    public $state;

    /**
     * Loads persistent data into the view context
     *
     * @param string $tpl the name of the template to load
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->model = $this->getModel();
        $layout      = $this->model->params['layout'];
        $this->lang  = THM_OrganizerHelperLanguage::getLanguage();
        $this->state = $this->get('State');
        $this->form  = $this->get('Form');
        $this->form->setValue('startDate', null, $this->state->get('startDate'));

        $this->form->setValue('dateRestriction', null, $this->state->get('dateRestriction'));

        $this->modifyDocument();
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
        HTML::_('jquery.ui');
        HTML::_('behavior.tooltip');
        $document = \JFactory::getDocument();
        $document->addStyleSheet(\JUri::root() . '/media/com_thm_organizer/css/event_list.css');
    }
}
