<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Class loads a filtered set of resources into the display context. Specific resource determined by extending class.
 */
abstract class THM_OrganizerViewList extends \Joomla\CMS\MVC\View\HtmlView
{
    public $state = null;

    public $items = null;

    public $pagination = null;

    public $filterForm = null;

    public $headers = null;

    public $hiddenFields = null;

    /**
     * Method to create a list output
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Don't know which of these filters does what if anything active had no effect on the active highlighting
        $this->filterForm = $this->get('FilterForm');

        // Items common across list views
        $this->headers      = $this->get('Headers');
        $this->hiddenFields = $this->get('HiddenFields');
        $this->items        = $this->get('Items');

        THM_OrganizerHelperComponent::addSubmenu($this);

        // Allows for view specific toolbar handling
        $this->addToolBar();
        parent::display($tpl);
    }

    /**
     * Concrete classes are supposed to use this method to add a toolbar.
     *
     * @return void  sets context variables
     */
    abstract protected function addToolBar();

    /**
     * Adds styles and scripts to the document
     *
     * @return void  modifies the document
     */
    protected function modifyDocument()
    {
        $document = JFactory::getDocument();
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/fonts/iconfont.css');
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/backend.css');

        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.multiselect');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('searchtools.form', '#adminForm', []);
    }
}
