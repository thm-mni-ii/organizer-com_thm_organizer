<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

require_once JPATH_ROOT . '/components/com_thm_organizer/Layouts/merge.php';

use THM_OrganizerHelperHTML as HTML;
use Joomla\CMS\Uri\Uri;

/**
 * Class loads a non-item based resource form (merge) into the display context. Specific resource determined by
 * extending class.
 */
abstract class THM_OrganizerViewMerge extends \Joomla\CMS\MVC\View\HtmlView
{
    public $params = null;

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
        $this->modifyDocument();

        $this->form = $this->get('Form');

        // Allows for view specific toolbar handling
        if (method_exists($this, 'addToolBar')) {
            $this->addToolBar();
        }
        parent::display($tpl);
    }

    /**
     * Adds styles and scripts to the document
     *
     * @return void  modifies the document
     */
    protected function modifyDocument()
    {
        HTML::_('bootstrap.tooltip');
        HTML::_('behavior.framework', true);
        HTML::_('behavior.formvalidation');
        HTML::_('formbehavior.chosen', 'select');

        $document = \JFactory::getDocument();
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/backend.css');
        $document->addScript(Uri::root() . 'components/com_thm_organizer/js/validators.js');
        $document->addScript(Uri::root() . 'components/com_thm_organizer/js/submitButton.js');
    }
}
