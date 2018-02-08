<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerViewList
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Ilja Michajlow, <Ilja.Michajlow@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Class provides standardized output of list items
 *
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 */
abstract class THM_OrganizerViewList extends JViewLegacy
{
    public $state = null;

    public $items = null;

    public $pagination = null;

    public $filterForm = null;

    public $activeFilters = null;

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
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Items common across list views
        $this->headers      = $this->get('Headers');
        $this->hiddenFields = $this->get('HiddenFields');

        $this->items = $this->get('Items');

        // Allows for component specific menu handling
        $path = JPATH_ROOT . "/media/com_thm_organizer/helpers/componentHelper.php";
        /** @noinspection PhpIncludeInspection */
        require_once $path;

        THM_OrganizerHelperComponent::addSubmenu($this);

        // Allows for view specific toolbar handling
        $this->addToolBar();
        parent::display();
    }

    /**
     * Concrete classes are supposed to use this method to add a toolbar.
     *
     * @return void  sets context variables
     */
    protected abstract function addToolBar();

    /**
     * Adds styles and scripts to the document
     *
     * @return void  modifies the document
     */
    protected function modifyDocument()
    {
        $document = JFactory::getDocument();
        $document->addStyleSheet(JUri::root() . "/media/com_thm_organizer/fonts/iconfont.css");
        $document->addStyleSheet(JUri::root() . "/media/com_thm_organizer/css/backend.css");

        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.multiselect');
        JHtml::_('formbehavior.chosen', 'select');
        JHtml::_('searchtools.form', '#adminForm', []);
    }
}
