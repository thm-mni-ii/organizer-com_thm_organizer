<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Mappings;
use Organizer\Helpers\Pools;
use Organizer\Helpers\Programs;

/**
 * Class loads curriculum information into the view context.
 */
class Curriculum extends ItemModel
{
    /**
     * Provides a strict access check which can be overwritten by extending classes.
     *
     * @return bool  true if the user can access the view, otherwise false
     */
    protected function allowView()
    {
        return true;
    }

    /**
     * Method to get an array of data items.
     *
     * @return mixed  An array of data items on success, false on failure.
     * @throws Exception
     */
    public function getItem()
    {
        $allowView = $this->allowView();
        if (!$allowView) {
            throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
        }

        $resource = [];
        if ($poolID = Input::getFilterID('pool')) {
            $mappings         = Mappings::getMappings('pool', $poolID);
            $resource['name'] = Pools::getName($poolID);
            $resource['type'] = 'pool';
        } elseif ($programID = Input::getFilterID('program')) {
            $mappings         = Mappings::getMappings('program', $programID);
            $resource['name'] = Programs::getName($programID);
            $resource['type'] = 'program';
        } else {
            return $resource;
        }

        $mapping  = array_pop($mappings);
        $resource += $mapping;
        Mappings::getChildren($resource);

        return $resource;
    }
}
