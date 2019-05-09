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

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Uri\Uri;

/**
 * Loads lesson and event data for a filtered set of rooms into the view context.
 */
class Room_Overview extends BaseView
{
    const DAY = 1;

    const WEEK = 2;

    public $form = null;

    public $languageLinks;

    public $model = null;

    public $state = null;

    /**
     * Loads persistent data into the view context
     *
     * @param string $tpl the name of the template to load
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->model = $this->getModel();
        $this->state = $this->get('State');
        $this->form  = $this->get('Form');

        $this->form->setValue('template', null, $this->state->get('template'));
        $this->form->setValue('date', null, $this->state->get('date'));
        $this->form->setValue('campusID', null, $this->state->get('campusID'));
        $this->form->setValue('buildingID', null, $this->state->get('buildingID'));
        $this->form->setValue('types', null, $this->state->get('types'));
        $this->form->setValue('rooms', null, $this->state->get('rooms'));

        $this->languageLinks = new LayoutFile('language_links', JPATH_ROOT . '/components/com_thm_organizer/Layouts');

        $this->modifyDocument();
        parent::display($tpl);
    }

    /**
     * Adds css and javascript files to the document
     *
     * @return void  modifies the document
     */
    private function modifyDocument()
    {
        HTML::_('jquery.ui');
        HTML::_('behavior.tooltip');
        HTML::_('formbehavior.chosen', 'select');
        $document = Factory::getDocument();
        $document->setCharset('utf-8');
        $document->addScript(Uri::root() . 'components/com_thm_organizer/js/room_overview.js');
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/room_overview.css');
    }

    /**
     * Creates a tooltip for individual blocks
     *
     * @param string $date      the block's date
     * @param int    $blockNo   the block number
     * @param string $blockName the block name to be displayed
     * @param string $roomNo    the room number
     *
     * @return string  formatted tooltip
     */
    public function getBlockTip($date, $blockNo, $blockName, $roomNo)
    {
        $dayConstant   = strtoupper(date('l', strtotime($date)));
        $day           = Languages::_($dayConstant);
        $formattedDate = Dates::formatDate($date);
        $dateText      = "$day $formattedDate<br />";

        $block     = $this->model->grid['periods'][$blockNo];
        $blockText = is_numeric($blockName) ? "$blockName. Block" : $blockName;

        $startTime = Dates::formatTime($block['startTime']);
        $endTime   = Dates::formatTime($block['endTime']);
        $timeText  = " ($startTime - $endTime)<br />";

        $roomText = Languages::_('THM_ORGANIZER_ROOM') . " $roomNo<br />";

        return htmlentities('<div>' . $dateText . $blockText . $timeText . $roomText . '</div>');
    }

    /**
     * Creates tips for block events
     *
     * @param array $events the events taking place in the block
     *
     * @return string  the html to be used in the tooltip
     */
    public function getEventTips($events)
    {
        $tips = [];
        foreach ($events as $event) {
            $eventTip   = [];
            $eventTip[] = '<div>';
            $eventTip[] = Languages::_('THM_ORGANIZER_DEPT_ORG') . ": {$event['department']}<br/>";
            $eventTip[] = Languages::_('THM_ORGANIZER_EVENT') . ": {$event['title']}<br/>";
            $eventTip[] = Languages::_('THM_ORGANIZER_TEACHERS') . ": {$event['teachers']}";
            if (!empty($event['comment'])) {
                $eventTip[] = '<br />';
                $eventTip[] = Languages::_('THM_ORGANIZER_EXTRA_INFORMATION') . ": {$event['comment']}";
            }
            if (!empty($event['divTime'])) {
                $eventTip[] = '<br />';
                $eventTip[] = Languages::_('THM_ORGANIZER_DIVERGENT_TIME') . ": {$event['divTime']}<br/>";
            }
            $eventTip[] = '</div>';
            $tips[]     = implode('', $eventTip);
        }

        return htmlentities(implode('', $tips));
    }

    /**
     * Creates a dynamically translated label.
     *
     * @param string $inputName the name of the form field whose label should be generated
     *
     * @return string the HMTL for the field label
     */
    public function getLabel($inputName)
    {
        $title  = Languages::_($this->form->getField($inputName)->title);
        $tip    = Languages::_($this->form->getField($inputName)->description);
        $return = '<label id="jform_' . $inputName . '-lbl" for="jform_' . $inputName . '" class="hasPopover"';
        $return .= 'data-content="' . $tip . '" data-original-title="' . $title . '">' . $title . '</label>';

        return $return;
    }

    /**
     * Creates a tooltip for individual blocks
     *
     * @param array $room an array with room information
     *
     * @return string  formatted tooltip
     */
    public function getRoomTip($room)
    {
        $typeText = '';
        if (!empty($room['typeName'])) {
            $typeText .= $room['typeName'];
            if (!empty($room['typeDesc'])) {
                $typeText .= ":<br /> {$room['typeDesc']}";
            }
            $typeText .= '<br />';
        }

        $capacityText = '';
        if (!empty($room['capacity'])) {
            $capacityText .= Languages::_('THM_ORGANIZER_CAPACITY');
            $capacityText .= ": {$room['capacity']}";
            $capacityText .= '<br />';
        }

        return htmlentities('<div>' . $typeText . $capacityText . '</div>');
    }
}
