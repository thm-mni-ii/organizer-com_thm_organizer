<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Languages;

/**
 * Class loads subject information into the display context.
 */
class Subject_Selection extends ListView
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'pool.addSubject', true);
    }

    /**
     * Adds styles and scripts to the document
     *
     * @return void  modifies the document
     */
    protected function modifyDocument()
    {
        parent::modifyDocument();

        HTML::_('jquery.framework');
        HTML::_('searchtools.form', '#adminForm', []);

        $document = Factory::getDocument();
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/child_selection.css');
    }
}
