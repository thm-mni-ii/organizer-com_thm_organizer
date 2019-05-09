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

namespace Organizer\Models;

defined('_JEXEC') or die;

use Organizer\Helpers\Languages;

/**
 * Class retrieves information for a filtered set of (schedule) grids.
 */
class Grid_Manager extends ListModel
{
    /**
     * Method to get all grids from the database and set filters for name and default state
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $shortTag = Languages::getShortTag();
        $query    = $this->getDbo()->getQuery(true);

        $select = "id, name_$shortTag AS name, grid, defaultGrid, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=grid_edit&id='", 'id'];
        $select .= $query->concatenate($parts, '') . ' AS link';
        $query->select($select);
        $query->from('#__thm_organizer_grids');
        $this->setOrdering($query);

        return $query;
    }
}
