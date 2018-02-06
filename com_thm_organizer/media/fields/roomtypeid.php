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
JFormHelper::loadFieldClass('list');
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class creates a form field for room type selection
 */
class JFormFieldRoomTypeID extends JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'roomTypeID';

    /**
     * Returns a select box where stored degree programs can be chosen
     *
     * @return  array  the available degree programs
     * @throws Exception
     */
    public function getOptions()
    {
        $defaultOptions = THM_OrganizerHelperComponent::getTranslatedOptions($this, $this->element);
        $input      = JFactory::getApplication()->input;
        $formData   = $input->get('jform', [], 'array');
        $buildingID = (empty($formData) OR empty($formData['buildingID'])) ? $input->getInt('buildingID') : (int)$formData['buildingID'];
        $campusID   = (empty($formData) OR empty($formData['campusID'])) ? $input->getInt('campusID') : (int)$formData['campusID'];

        $dbo      = JFactory::getDbo();
        $query    = $dbo->getQuery(true);
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();
        $query->select("DISTINCT rt.id, rt.name_$shortTag AS name")
            ->from('#__thm_organizer_room_types AS rt')
            ->innerJoin('#__thm_organizer_rooms AS r ON r.typeID = rt.id');

        if (!empty($buildingID) OR !empty($campusID)) {
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

        try {
            $types = $dbo->loadAssocList();
        } catch (Exception $exc) {
            return $defaultOptions;
        }

        $options = [];
        if (empty($types)) {
            $lang = THM_OrganizerHelperLanguage::getLanguage();
            $options[] = JHtml::_('select.option', '', $lang->_('JNONE'));
            return $options;
        } else {
            foreach ($types as $type) {
                $options[] = JHtml::_('select.option', $type['id'], $type['name']);
            }
        }


        return array_merge($defaultOptions, $options);
    }
}
