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

defined('_JEXEC') or die;

use Organizer\Helpers\Campuses;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class creates a form field for campus selection.
 */
class CampusesField extends ListField
{
    /**
     * @var  string
     */
    protected $type = 'Campuses';

    /**
     * Returns an array of pool options
     *
     * @return array  the pool options
     */
    protected function getOptions()
    {
        $options = parent::getOptions();
        $campuses       = Campuses::getOptions();

        if (empty($campuses)) {
            return $options;
        }

        foreach ($campuses as $campusID => $name) {
            $options[$campusID] = HTML::_('select.option', $campusID, $name);
        }

        return $options;
    }
}
