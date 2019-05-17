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

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads the plan (subject) pool form into display context.
 */
class Plan_Pool_Edit extends EditView
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_GROUP_EDIT'), 'list-2');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'save', Languages::_('THM_ORGANIZER_SAVE'), 'plan_pool.save', false);
        $toolbar->appendButton('Standard', 'cancel', Languages::_('THM_ORGANIZER_CANCEL'), 'plan_pool.cancel', false);
    }
}
