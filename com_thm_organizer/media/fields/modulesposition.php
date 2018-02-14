<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

JLoader::register('ModulesHelper', JPATH_ADMINISTRATOR . '/components/com_modules/helpers/modules.php');

JFormHelper::loadFieldClass('list');

/**
 * Class creates a select box for module positions.
 */
class JFormFieldModulesPosition extends JFormFieldList
{
    protected $type = 'ModulesPosition';

    /**
     * Method to get the field options.
     *
     * @return array  The field option objects.
     * @throws Exception
     */
    public function getOptions()
    {
        $clientId = JFactory::getApplication()->input->get('client_id', 0, 'int');
        $options  = ModulesHelper::getPositions($clientId);

        return array_merge(parent::getOptions(), $options);
    }
}
