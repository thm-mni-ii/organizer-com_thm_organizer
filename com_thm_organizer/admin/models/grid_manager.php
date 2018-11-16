<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class retrieves information for a filtered set of (schedule) grids.
 */
class THM_OrganizerModelGrid_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    /**
     * Method to get all grids from the database and set filters for name and default state
     *
     * @return JDatabaseQuery
     */
    protected function getListQuery()
    {
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();
        $query    = $this->getDbo()->getQuery(true);

        $select = "id, name_$shortTag AS name, grid, defaultGrid, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=grid_edit&id='", "id"];
        $select .= $query->concatenate($parts, "") . " AS link";
        $query->select($select);
        $query->from('#__thm_organizer_grids');
        $this->setOrdering($query);

        return $query;
    }

    /**
     * Function to feed the data in the table body correctly to the list view
     *
     * @return array consisting of items in the body
     */
    public function getItems()
    {
        $items  = parent::getItems();
        $return = [];

        if (empty($items)) {
            return $return;
        }

        $index = 0;
        foreach ($items as $item) {
            $return[$index]             = [];
            $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['name']     = JHtml::_('link', $item->link, $item->name);
            $grid                       = json_decode($item->grid);

            if (isset($grid) and isset($grid->periods)) {
                // 'l' (lowercase L) in date function for full textual day of the week.
                $startDayConstant = strtoupper(date('l', strtotime("Sunday + {$grid->startDay} days")));
                $endDayConstant   = strtoupper(date('l', strtotime("Sunday + {$grid->endDay} days")));

                $return[$index]['startDay'] = JText::_($startDayConstant);
                $return[$index]['endDay']   = JText::_($endDayConstant);

                $periods                     = get_object_vars($grid->periods);
                $return[$index]['startTime'] = THM_OrganizerHelperDate::formatTime(reset($periods)->startTime);
                $return[$index]['endTime']   = THM_OrganizerHelperDate::formatTime(end($periods)->endTime);
            } else {
                $return[$index]['startDay']  = '';
                $return[$index]['endDay']    = '';
                $return[$index]['startTime'] = '';
                $return[$index]['endTime']   = '';
            }

            $tip                           = JText::_('COM_THM_ORGANIZER_GRID_DEFAULT_DESC');
            $return[$index]['defaultGrid'] = $this->getToggle($item->id, $item->defaultGrid, 'grid', $tip);
            $index++;
        }

        return $return;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $headers                = [];
        $headers['checkbox']    = '';
        $headers['name']        = JText::_('COM_THM_ORGANIZER_NAME');
        $headers['startDay']    = JText::_('COM_THM_ORGANIZER_START_DAY');
        $headers['endDay']      = JText::_('COM_THM_ORGANIZER_END_DAY');
        $headers['startTime']   = JText::_('COM_THM_ORGANIZER_START_TIME');
        $headers['endTime']     = JText::_('COM_THM_ORGANIZER_END_TIME');
        $headers['defaultGrid'] = JText::_('COM_THM_ORGANIZER_DEFAULT');

        return $headers;
    }
}
