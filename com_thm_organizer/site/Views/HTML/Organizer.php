<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

defined('_JEXEC') or die;

use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Languages;

/**
 * Class modifies the document for the output of a menu like list of resource management views.
 */
class Organizer extends BaseHTMLView
{
    public $menuItems;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->addMenu();
        $this->modifyDocument();
        $this->addToolBar();

        parent::display($tpl);
    }

    /**
     * Creates a toolbar
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_MAIN'), 'organizer');

        if (Access::isAdmin()) {
            HTML::setPreferencesButton();
        }
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

        $document = Factory::getDocument();
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/organizer.css');
    }
}
