<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\ListModel as ParentModel;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class provides a standardized framework for the display of listed resources.
 */
abstract class ListModelMenu extends ListModel
{
    /**
     * Method to auto-populate the model state.
     *
     * @param string $ordering  An optional ordering field.
     * @param string $direction An optional direction (asc|desc).
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $params = OrganizerHelper::getParams();
        $app = OrganizerHelper::getApplication();

        // Receive & set filters
        $filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', [], 'array');
        if (!empty($filters)) {
            foreach ($filters as $name => $value) {
                if(!empty($params->get($name))) {
                    $value = $params->get($name);
                }
                $this->setState('filter.' . $name, $value);
            }
        }

        $list = $app->getUserStateFromRequest($this->context . '.list', 'list', [], 'array');
        $this->setListState($list);

        $validLimit = (isset($list['limit']) && is_numeric($list['limit']));
        $limit      = $validLimit ? $list['limit'] : $this->defaultLimit;
        $this->setState('list.limit', $limit);

        $value = $this->getUserStateFromRequest('limitstart', 'limitstart', 0);
        $start = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
        $this->setState('list.start', $start);
    }
}
