<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Factory;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Mappings;
use Organizer\Helpers\OrganizerHelper;


/**
 * Class creates a select box for (subject) pools.
 */
class PoolsField extends ListField
{
    /**
     * @var  string
     */
    protected $type = 'Pools';

    /**
     * Returns an array of pool options
     *
     * @return array  the pool options
     */
    protected function getOptions()
    {
        $programID = Factory::getSession()->get('programID');
        if (empty($programID)) {
            return parent::getOptions();
        }

        $programRanges = Mappings::getResourceRanges('program', $programID);
        if (empty($programRanges) or count($programRanges) > 1) {
            return parent::getOptions();
        }

        $shortTag = Languages::getShortTag();
        $dbo      = Factory::getDbo();
        $query    = $dbo->getQuery(true);
        $query->select("DISTINCT p.id AS value, p.name_$shortTag AS text");
        $query->from('#__thm_organizer_pools AS p');
        $query->innerJoin('#__thm_organizer_mappings AS m ON p.id = m.poolID');
        $query->where("lft > '{$programRanges[0]['lft']}'");
        $query->where("rgt < '{$programRanges[0]['rgt']}'");
        $query->order('text ASC');
        $dbo->setQuery($query);

        $defaultOptions = parent::getOptions();
        $pools          = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($pools)) {
            return $defaultOptions;
        }

        // Whether or not the program display should be prefiltered according to user resource access
        $access  = $this->getAttribute('access', false);
        $options = [];

        foreach ($pools as $pool) {
            if (!$access or Access::allowDocumentAccess('pool', $pool['value'])) {
                $options[] = HTML::_('select.option', $pool['value'], $pool['text']);
            }
        }

        return array_merge($defaultOptions, $options);
    }
}
