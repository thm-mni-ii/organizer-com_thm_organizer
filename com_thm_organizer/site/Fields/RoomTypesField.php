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
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a form field for room type selection
 */
class RoomTypesField extends ListField
{
    /**
     * @var  string
     */
    protected $type = 'RoomTypes';

    /**
     * Returns a select box where stored degree programs can be chosen
     *
     * @return array  the available degree programs
     */
    protected function getOptions()
    {
        $defaultOptions = HTML::getTranslatedOptions($this, $this->element);
        $input          = OrganizerHelper::getInput();
        $formData       = OrganizerHelper::getFormInput();
        $buildingID     = (empty($formData) or empty($formData['buildingID'])) ?
            $input->getInt('buildingID') : (int)$formData['buildingID'];
        $campusID       = (empty($formData) or empty($formData['campusID'])) ?
            $input->getInt('campusID') : (int)$formData['campusID'];

        $dbo      = Factory::getDbo();
        $query    = $dbo->getQuery(true);
        $shortTag = Languages::getShortTag();
        $query->select("DISTINCT rt.id, rt.name_$shortTag AS name")
            ->from('#__thm_organizer_room_types AS rt')
            ->innerJoin('#__thm_organizer_rooms AS r ON r.typeID = rt.id');

        if (!empty($buildingID) or !empty($campusID)) {
            $query->innerJoin('#__thm_organizer_buildings AS b ON b.id = r.buildingID');

            if (!empty($buildingID)) {
                $query->where("b.id = '$buildingID'");
            }

            if (!empty($campusID)) {
                $query->innerJoin('#__thm_organizer_campuses AS c ON c.id = b.campusID')
                    ->where("(c.id = '$campusID' OR c.parentID = '$campusID')");
            }
        }

        $query->order('name');
        $dbo->setQuery($query);

        $types = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($types)) {
            return $defaultOptions;
        }

        $options = [];
        if (empty($types)) {
            $options[] = HTML::_('select.option', '', Languages::_('JNONE'));

            return $options;
        } else {
            foreach ($types as $type) {
                $options[] = HTML::_('select.option', $type['id'], $type['name']);
            }
        }

        return array_merge($defaultOptions, $options);
    }
}
