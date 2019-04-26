<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

require_once JPATH_ROOT . '/components/com_thm_organizer/Layouts/list.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Layouts/list_modal.php';

use THM_OrganizerHelperHTML as HTML;
use Joomla\CMS\Uri\Uri;

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
        $this->headers = $this->get('Headers');
        $this->items   = $this->get('Items');

        $this->sidebar = OrganizerHelper::adminSideBar($this->get('name'));

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
        $document = \JFactory::getDocument();
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/backend.css');

        HTML::_('bootstrap.tooltip');
        HTML::_('behavior.multiselect');
        HTML::_('formbehavior.chosen', 'select');
        HTML::_('searchtools.form', '#adminForm', []);
    }
}
