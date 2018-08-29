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

/**
 * Class which sets permissions for the view.
 */
class THM_OrganizerModelTHM_Organizer extends JModelLegacy
{
    /**
     * constructor
     *
     * @param array $config configurations parameter
     *
     * @throws Exception
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        THM_OrganizerHelperComponent::addActions($this);
    }
}
