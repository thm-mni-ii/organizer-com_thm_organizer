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

define('K_PATH_IMAGES', JPATH_ROOT . '/components/com_thm_organizer/images/');

jimport('tcpdf.tcpdf');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads department statistics into the display context.
 */
class DepartmentOccupancy extends SelectionView
{
    /**
     * Modifies document variables and adds links to external files
     *
     * @return void
     */
    protected function modifyDocument()
    {
        parent::modifyDocument();

        Factory::getDocument()->addScript(Uri::root() . 'components/com_thm_organizer/js/department_occupancy.js');
    }

    private function setBaseFields()
    {
        $attribs                    = [];
        $this->sets['baseSettings'] = [];

        $options    = $this->model->getYearOptions();
        $default    = date('Y');
        $termSelect = HTML::selectBox($options, 'year', $attribs, $default);

        $this->sets['baseSettings']['termIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_YEAR'),
            'description' => Languages::_('THM_ORGANIZER_YEAR_DESC'),
            'input'       => $termSelect
        ];
    }

    /**
     * Function to define field sets and fill sets with fields
     *
     * @return void sets the fields property
     */
    protected function setSets()
    {
        $this->sets['baseSettings'] = [];
        $this->setBaseFields();
        $this->sets['filterFields'] = ['label' => 'THM_ORGANIZER_FILTERS'];
        $this->setFilterFields();
    }

    /**
     * Creates resource selection fields for the form
     *
     * @return void sets indexes in $this->fields['resouceSettings'] with html content
     */
    private function setFilterFields()
    {
        $this->sets['filterFields'] = [];
        $attribs                    = ['multiple' => 'multiple'];

        $roomAttribs                     = $attribs;
        $roomOptions                     = $this->model->getRoomOptions();
        $roomSelect                      = HTML::selectBox($roomOptions, 'roomIDs', $roomAttribs);

        $this->sets['filterFields']['roomIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_ROOMS'),
            'description' => Languages::_('THM_ORGANIZER_ROOMS_DESC'),
            'input'       => $roomSelect
        ];

        $roomtypeAttribs                     = $attribs;
        $roomtypeAttribs['onChange']         = 'repopulateRooms();';
        $typeOptions                         = $this->model->getRoomtypeOptions();
        $roomtypeSelect                      = HTML::selectBox($typeOptions, 'typeIDs', $roomtypeAttribs);

        $this->sets['filterFields']['typeIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_ROOMTYPES'),
            'description' => Languages::_('THM_ORGANIZER_ROOMS_TYPES_DESC'),
            'input'       => $roomtypeSelect
        ];
    }
}
