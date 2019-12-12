<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Organizer\Helpers\Campuses;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class creates a form field for campus selection.
 */
class CampusesField extends OptionsField
{
    /**
     * @var  string
     */
    protected $type = 'Campuses';

    /**
     * Returns an array of options
     *
     * @return array  the options
     */
    protected function getOptions()
    {
        $options  = parent::getOptions();
        $campuses = Campuses::getOptions();

        return array_merge($options, $campuses);
    }
}
