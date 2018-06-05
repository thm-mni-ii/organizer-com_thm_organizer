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
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/models/edit.php';

/**
 * Class loads a form for editing (degree) program data.
 */
class THM_OrganizerModelProgram_Edit extends THM_OrganizerModelEdit
{
    public $children = null;

    /**
     * Checks access for edit views
     *
     * @param int $programID the id of the resource to be edited (empty for new entries)
     *
     * @return bool  true if the user can access the edit view, otherwise false
     * @throws Exception
     */
    public function allowEdit($programID = null)
    {
        if (empty($programID) OR !THM_OrganizerHelperComponent::checkAssetInitialization('program', $programID)) {
            return THM_OrganizerHelperComponent::allowDeptResourceCreate('program');
        }

        return THM_OrganizerHelperComponent::allowResourceManage('program', $programID, 'manage');

        return false;
    }
}
