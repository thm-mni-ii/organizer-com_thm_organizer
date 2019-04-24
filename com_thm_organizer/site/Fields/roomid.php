<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

\JFormHelper::loadFieldClass('list');
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/component.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/rooms.php';

use THM_OrganizerHelperHTML as HTML;
use THM_OrganizerHelperLanguages as Languages;

/**
 * Class creates a form field for room selection.
 */
class JFormFieldRoomID extends \JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'roomID';

    /**
     * Returns a select box where stored degree programs can be chosen
     *
     * @return array  the available degree programs
     */
    protected function getOptions()
    {
        $defaultOptions = HTML::getTranslatedOptions($this, $this->element);
        $rooms          = THM_OrganizerHelperRooms::getRooms();

        $options = [];
        if (empty($rooms)) {
            $lang      = Languages::getLanguage();
            $options[] = HTML::_('select.option', '', $lang->_('JNONE'));

            return $options;
        } else {
            foreach ($rooms as $room) {
                $options[] = HTML::_('select.option', $room['id'], $room['longname']);
            }
        }

        return array_merge($defaultOptions, $options);
    }
}
