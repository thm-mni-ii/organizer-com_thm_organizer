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

defined('_JEXEC') or die;

use Organizer\Helpers\OrganizerHelper;

\JLoader::register('ModulesHelper', JPATH_ADMINISTRATOR . '/components/com_modules/Helpers/modules.php');

/**
 * Class creates a select box for module positions.
 */
class ModulePositionsField extends ListField
{
    protected $type = 'ModulePositions';

    /**
     * Method to get the field options.
     *
     * @return array  The field option objects.
     */
    protected function getOptions()
    {
        $clientId = OrganizerHelper::getInput()->get('client_id', 0, 'int');
        $options  = ModulesHelper::getPositions($clientId);

        return array_merge(parent::getOptions(), $options);
    }
}