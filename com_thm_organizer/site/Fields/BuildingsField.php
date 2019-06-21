<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Factory;
use Organizer\Helpers\Campuses;
use Organizer\Helpers\HTML;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a form field for building selection.
 */
class BuildingsField extends OptionsField
{
    /**
     * @var  string
     */
    protected $type = 'Buildings';

    /**
     * Returns a select box where stored buildings can be chosen
     *
     * @return array  the available buildings
     */
    protected function getOptions()
    {
        $defaultOptions = HTML::getTranslatedOptions($this, $this->element);
        $options        = Buildings::getOptions();

        return array_merge($defaultOptions, $options);
    }
}
