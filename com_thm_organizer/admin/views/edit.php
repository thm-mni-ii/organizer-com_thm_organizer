<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use \THM_OrganizerHelperHTML as HTML;

require_once JPATH_COMPONENT . '/layouts/edit_basic.php';
require_once JPATH_COMPONENT . '/layouts/edit_tabbed.php';

/**
 * Class loads the resource form into display context. Specific resource determined by extending class.
 */
abstract class THM_OrganizerViewEdit extends \Joomla\CMS\MVC\View\HtmlView
{
    public $item = null;

    public $form = null;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        // Allows for view specific toolbar handling
        $this->addToolBar();
        $this->modifyDocument();
        parent::display($tpl);
    }

    /**
     * Concrete classes are supposed to use this method to add a toolbar.
     *
     * @return void  adds toolbar items to the view
     */
    abstract protected function addToolBar();

    /**
     * Adds styles and scripts to the document
     *
     * @return void  modifies the document
     */
    protected function modifyDocument()
    {
        HTML::_('bootstrap.framework');
        HTML::_('bootstrap.tooltip');
        HTML::_('behavior.formvalidator');
        HTML::_('formbehavior.chosen', 'select');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/fonts/iconfont.css');
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/backend.css');
        $document->addScript(JUri::root() . '/media/com_thm_organizer/js/validators.js');
        $document->addScript(JUri::root() . '/media/com_thm_organizer/js/submitButton.js');
    }
}
