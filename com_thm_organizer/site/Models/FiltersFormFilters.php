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

/**
 * Class provides a standardized framework for the display of listed resources.
 */
interface FiltersFormFilters
{
    /**
     * Filters out form inputs which should not be displayed due to menu settings or limited access.
     *
     * @param Form $form the form to be filtered
     *
     * @return void modifies $form
     */
    public function filterFilterForm(&$form);
}