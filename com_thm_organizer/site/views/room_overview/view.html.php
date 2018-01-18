<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewRoom_Overview
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

define('DAY', 1);
define('WEEK', 2);

/**
 * Loads lesson and event data for a single room and day into view context
 *
 * @category  Joomla.Component.Site
 * @package   thm_organizer
 */
class THM_OrganizerViewRoom_Overview extends JViewLegacy
{
    public $model = null;

    public $state = null;

    public $filters = [];

    /**
     * Loads persistent data into the view context
     *
     * @param string $tpl the name of the template to load
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $this->model = $this->getModel();
        $this->state = $this->model->getState();
        $this->setFilters();

        $this->modifyDocument();
        parent::display($tpl);
    }

    /**
     * Adds css and javascript files to the document
     *
     * @return  void  modifies the document
     */
    private function modifyDocument()
    {
        JHtml::_('jquery.ui');
        JHtml::_('behavior.tooltip');
        JHtml::_('formbehavior.chosen', 'select');
        $document = JFactory::getDocument();
        $document->setCharset("utf-8");
        $document->addScript(JUri::root() . '/media/com_thm_organizer/js/room_overview.js');
        $document->addStyleSheet(JUri::root() . "/media/com_thm_organizer/css/room_overview.css");
    }

    /**
     * Sets various filter elements
     *
     * @return  void  sets the filter object variables
     */
    private function setFilters()
    {
        $helper                    = 'THM_OrganizerHelperComponent';
        $templateOptions           = [
            DAY  => JText::_('COM_THM_ORGANIZER_FILTER_DAY_TEMPLATE'),
            WEEK => JText::_('COM_THM_ORGANIZER_FILTER_WEEK_TEMPLATE')
        ];
        $this->filters['template'] = $helper::selectBox($templateOptions, 'template', null, $this->state->template);

        $format                = JFactory::getApplication()->getParams()->get('dateFormat');
        $jsFormat              = preg_replace('/[a-zA-Z]/', '%$0', $format);
        $this->filters['date'] = JHtml::calendar($this->state->get('date'), 'jform[date]', 'date', $jsFormat);

        $attribs        = [
            'class'    => 'room-select',
            'multiple' => 'multiple',
            'onChange' => 'cleanSelection(this.id, \'selectedRooms\');',
            'size'     => '10'
        ];
        $defaultOptions = ['-1' => JText::_('JALL')];

        $this->filters['rooms'] = $helper::selectBox(
            $this->model->rooms, 'rooms', $attribs, $this->state->rooms, $defaultOptions
        );
        $attribs['class']       = 'type-select';
        $attribs['onChange']    = 'cleanSelection(this.id, \'selectedTypes\');';
        $this->filters['types'] = $helper::selectBox($this->model->types, 'types', $attribs, $this->state->types,
            $defaultOptions);
    }

    /**
     * Creates a tooltip for individual blocks
     *
     * @param string $date    the block's date
     * @param int    $blockNo the block number
     * @param string $roomNo  the room number
     *
     * @return  string  formatted tooltip
     */
    public function getBlockTip($date, $blockNo, $roomNo)
    {
        $dayConstant   = strtoupper(date('l', strtotime($date)));
        $day           = JText::_($dayConstant);
        $formattedDate = THM_OrganizerHelperComponent::formatDate($date);
        $dateText      = "$day $formattedDate<br />";

        $block     = $this->model->grid['periods'][$blockNo];
        $startTime = THM_OrganizerHelperComponent::formatTime($block['startTime']);
        $endTime   = THM_OrganizerHelperComponent::formatTime($block['endTime']);
        $blockText = "$blockNo. Block ($startTime - $endTime)<br />";

        $roomText = JText::_('COM_THM_ORGANIZER_ROOM') . " $roomNo<br />";

        return htmlentities('<div>' . $dateText . $blockText . $roomText . '</div>');
    }

    /**
     * Creates tips for block events
     *
     * @param array $events the events taking place in the block
     *
     * @return  string  the html to be used in the tooltip
     */
    public function getEventTips($events)
    {
        $tips = [];
        foreach ($events as $eventNo => $event) {
            $eventTip   = [];
            $eventTip[] = '<div>';
            $eventTip[] = JText::_('COM_THM_ORGANIZER_DEPT_ORG') . ": {$event['department']}<br/>";
            $eventTip[] = JText::_('COM_THM_ORGANIZER_EVENT') . ": {$event['title']}<br/>";
            $eventTip[] = JText::_('COM_THM_ORGANIZER_TEACHERS') . ": {$event['teachers']}";
            if (!empty($event['comment'])) {
                $eventTip[] = '<br />';
                $eventTip[] = JText::_('COM_THM_ORGANIZER_EXTRA_INFORMATION') . ": {$event['comment']}";
            }
            if (!empty($event['divTime'])) {
                $eventTip[] = '<br />';
                $eventTip[] = JText::_('COM_THM_ORGANIZER_DIVERGENT_TIME') . ": {$event['divTime']}<br/>";
            }
            $eventTip[] = '</div>';
            $tips[]     = implode('', $eventTip);
        }

        return htmlentities(implode('', $tips));
    }
}
