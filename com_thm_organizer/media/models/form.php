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
 * Class loads non-item-specific form data.
 */
class THM_OrganizerModelForm extends JModelForm
{
    /**
     * Method to get the form
     *
     * @param array $data     Data         (default: array)
     * @param bool  $loadData Load data  (default: true)
     *
     * @return mixed  JForm object on success, False on error.
     * @throws  Exception  if the user is not authorized to access the view
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = [], $loadData = false)
    {
        require_once JPATH_ROOT . "/media/com_thm_organizer/helpers/component.php";

        THM_OrganizerHelperComponent::addActions($this);
        $allowEdit = THM_OrganizerHelperComponent::allowEdit($this);
        if (!$allowEdit) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $name = $this->get('name');
        $form = $this->loadForm("com_thm_organizer.$name", $name, ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }
}
