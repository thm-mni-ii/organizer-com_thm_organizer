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

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class loads persistent information a filtered set of monitors into the display context.
 */
class Monitors extends ListView
{
    const DAILY = 1;
    const MIXED = 2;
    const CONTENT = 3;
    const LESSON_LIST = 4;

    public $displayBehaviour = [];

    /**
     * Constructor
     *
     * @param array $config A named configuration array for object construction.
     */
    public function __construct($config = array())
    {
        $this->displayBehaviour[self::DAILY]       = Languages::_('THM_ORGANIZER_DAILY_PLAN');
        $this->displayBehaviour[self::MIXED]       = Languages::_('THM_ORGANIZER_MIXED_PLAN');
        $this->displayBehaviour[self::CONTENT]     = Languages::_('THM_ORGANIZER_CONTENT_DISPLAY');
        $this->displayBehaviour[self::LESSON_LIST] = Languages::_('THM_ORGANIZER_LESSON_LIST');

        parent::__construct($config);
    }

    /**
     * Creates joomla toolbar elements
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_MONITORS_TITLE'), 'screen');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'monitor.add', false);
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'monitor.edit', true);
        $toolbar->appendButton(
            'Confirm',
            Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
            'delete',
            Languages::_('THM_ORGANIZER_DELETE'),
            'monitor.delete',
            true
        );

        if (Access::isAdmin()) {
            HTML::setPreferencesButton();
        }
    }

    /**
     * Function determines whether the user may access the view.
     *
     * @return bool true if the use may access the view, otherwise false
     */
    protected function allowAccess()
    {
        return Access::allowFMAccess();
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering  = $this->state->get('list.ordering');
        $direction = $this->state->get('list.direction');
        $headers   = [];

        $headers['checkbox']    = '';
        $headers['name']        = HTML::sort('ROOM', 'r.name', $direction, $ordering);
        $headers['ip']          = HTML::sort('IP', 'm.ip', $direction, $ordering);
        $headers['useDefaults'] = HTML::sort('DEFAULT_SETTINGS', 'm.useDefaults', $direction, $ordering);
        $headers['display']     = Languages::_('THM_ORGANIZER_DISPLAY_BEHAVIOUR');
        $headers['content']     = HTML::sort('DISPLAY_CONTENT', 'm.content', $direction, $ordering);

        return $headers;
    }

    /**
     * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
     *
     * @return void processes the class items property
     */
    protected function preProcessItems()
    {
        if (empty($this->items)) {
            return;
        }

        $index          = 0;
        $processedItems = [];

        $params       = OrganizerHelper::getParams();
        $displayParam = $params->get('display');
        $contentParam = $params->get('content');

        foreach ($this->items as $item) {
            // Set default attributes
            if (!empty($item->useDefaults)) {
                $item->display = $displayParam;
                $item->content = $contentParam;
            }

            $processedItems[$index]                = [];
            $processedItems[$index]['checkbox']    = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['name']        = HTML::_('link', $item->link, $item->name);
            $processedItems[$index]['ip']          = HTML::_('link', $item->link, $item->ip);
            $tip                                   = Languages::_('THM_ORGANIZER_TOGGLE_COMPONENT_SETTINGS');
            $processedItems[$index]['useDefaults'] = $this->getToggle($item->id, $item->useDefaults, 'monitor', $tip);
            $display                               = $this->displayBehaviour[$item->display];
            $processedItems[$index]['display']     = HTML::_('link', $item->link, $display);
            $processedItems[$index]['content']     = HTML::_('link', $item->link, $item->content);
            $index++;
        }

        $this->items = $processedItems;
    }
}
