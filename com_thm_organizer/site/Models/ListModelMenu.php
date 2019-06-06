<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Joomla\CMS\Form\Form;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class provides a standardized framework for the display of listed resources.
 */
abstract class ListModelMenu extends ListModel
{
    /**
     * Filters out form inputs which should not be displayed due to menu settings.
     *
     * @param Form $form the form to be filtered
     *
     * @return void modifies $form
     */
    abstract protected function filterFilterForm(&$form);

    /**
     * Method to get a form object.
     *
     * @param string         $name    The name of the form.
     * @param string         $source  The form source. Can be XML string if file flag is set to false.
     * @param array          $options Optional array of options for the form creation.
     * @param boolean        $clear   Optional argument to force load a new form.
     * @param string|boolean $xpath   An optional xpath to search for the fields.
     *
     * @return  Form|boolean  Form object on success, False on error.
     */
    protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
    {
        $form = parent::loadForm($name, $source, $options, $clear, $xpath);
        $this->filterFilterForm($form);

        return $form;
    }

    /**
     * Overrides state properties with menu settings values.
     *
     * @return void sets state properties
     */
    abstract protected function populateStateFromMenu();

    /**
     * Method to auto-populate the model state.
     *
     * @param string $ordering  An optional ordering field.
     * @param string $direction An optional direction (asc|desc).
     *
     * @return void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        if (!empty(OrganizerHelper::getApplication()->getMenu()->getActive()->id)) {
            $this->populateStateFromMenu();
        }
    }
}
