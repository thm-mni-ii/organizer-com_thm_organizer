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

use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Rooms;

/**
 * Class creates a form field for room selection.
 */
class RoomsField extends OptionsField
{
    /**
     * @var  string
     */
    protected $type = 'Rooms';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $defaultOptions = HTML::getTranslatedOptions($this, $this->element);
        $rooms          = Rooms::getRooms();

        $options = [];
        if (empty($rooms)) {
            $options[] = HTML::_('select.option', '', Languages::_('JNONE'));

            return $options;
        } else {
            foreach ($rooms as $room) {
                $options[] = HTML::_('select.option', $room['id'], $room['name']);
            }
        }

        return array_merge($defaultOptions, $options);
    }
}
