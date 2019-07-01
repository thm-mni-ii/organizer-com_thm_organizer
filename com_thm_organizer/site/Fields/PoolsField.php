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

use Organizer\Helpers\Pools;

/**
 * Class creates a select box for (subject) pools.
 */
class PoolsField extends OptionsField
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
        $options = parent::getOptions();
        $pools   = Pools::getOptions($this->getAttribute('access', ''));

        return array_merge($options, $pools);
    }
}
