<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerViewEdit
 * @author      Melih Cakir, <melih.cakir@mni.thm.de>
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Class provides standardized output of an item being edited
 *
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 */
abstract class THM_OrganizerViewEdit extends JViewLegacy
{
    public $item = null;

    public $form = null;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        // Allows for view specific toolbar handling
        $this->addToolBar();
        parent::display($tpl);
    }

    /**
     * Concrete classes are supposed to use this method to add a toolbar.
     *
     * @return  void  adds toolbar items to the view
     */
    protected abstract function addToolBar();

    /**
     * Adds styles and scripts to the document
     *
     * @return  void  modifies the document
     */
    protected function modifyDocument()
    {
        JHtml::_('bootstrap.framework');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.formvalidator');
        JHtml::_('formbehavior.chosen', 'select');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JUri::root() . "/media/com_thm_organizer/fonts/iconfont.css");
        $document->addStyleSheet(JUri::root() . "/media/com_thm_organizer/css/backend.css");
        $document->addScript(JUri::root() . "/media/com_thm_organizer/js/validators.js");
        $document->addScript(JUri::root() . "/media/com_thm_organizer/js/submitButton.js");
    }
}
