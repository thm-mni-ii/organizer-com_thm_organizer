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

defined('_JEXEC') or die;
define('K_PATH_IMAGES', JPATH_ROOT . '/components/com_thm_organizer/images/');

jimport('tcpdf.tcpdf');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads department statistics into the display context.
 */
class Department_Statistics extends BaseHTMLView
{
    public $fields = [];

    /**
     * Sets context variables and renders the view.
     *
     * @param string $tpl template
     *
     * @return void sets context variables and uses the parent's display method
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $this->model = $this->getModel();

        $this->setBaseFields();
        $this->setFilterFields();

        parent::display($tpl);
    }

    /**
     * Modifies document variables and adds links to external files
     *
     * @return void
     */
    private function modifyDocument()
    {
        HTML::_('bootstrap.framework');
        HTML::_('bootstrap.tooltip');
        HTML::_('jquery.ui');
        HTML::_('formbehavior.chosen', 'select');

        $document = Factory::getDocument();
        $document->addScript(Uri::root() . 'components/com_thm_organizer/js/department_statistics.js');
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/department_statistics.css');
    }

    private function setBaseFields()
    {
        $attribs                      = [];
        $this->fields['baseSettings'] = [];

        $options  = $this->model->getYearOptions();
        $default  = date('Y');
        $termSelect = HTML::selectBox($options, 'year', $attribs, $default);

        $this->fields['baseSettings']['termIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_YEAR'),
            'description' => Languages::_('THM_ORGANIZER_YEAR_EXPORT_DESC'),
            'input'       => $termSelect
        ];
    }

    /**
     * Creates resource selection fields for the form
     *
     * @return void sets indexes in $this->fields['resouceSettings'] with html content
     */
    private function setFilterFields()
    {
        $this->fields['filterFields'] = [];
        $attribs                      = ['multiple' => 'multiple'];

        $roomAttribs                     = $attribs;
        $roomAttribs['data-placeholder'] = Languages::_('THM_ORGANIZER_SELECT_ROOMS');
        $roomOptions                     = $this->model->getRoomOptions();
        $roomSelect                      = HTML::selectBox($roomOptions, 'roomIDs', $roomAttribs);

        $this->fields['filterFields']['roomIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_ROOMS'),
            'description' => Languages::_('THM_ORGANIZER_ROOMS_EXPORT_DESC'),
            'input'       => $roomSelect
        ];

        $roomTypeAttribs                     = $attribs;
        $roomTypeAttribs['onChange']         = 'repopulateRooms();';
        $roomTypeAttribs['data-placeholder'] = Languages::_('THM_ORGANIZER_SELECT_ROOM_TYPES');
        $typeOptions                         = $this->model->getRoomTypeOptions();
        $roomTypeSelect                      = HTML::selectBox($typeOptions, 'typeIDs', $roomTypeAttribs);

        $this->fields['filterFields']['typeIDs'] = [
            'label'       => Languages::_('THM_ORGANIZER_ROOM_TYPES'),
            'description' => Languages::_('THM_ORGANIZER_ROOMS_TYPES_EXPORT_DESC'),
            'input'       => $roomTypeSelect
        ];
    }
}
