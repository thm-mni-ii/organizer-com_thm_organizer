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
class THM_OrganizerModelForm extends \Joomla\CMS\MVC\Model\FormModel
{
    protected $deptResource;

    /**
     * Checks for user authorization to access the view
     *
     * @return bool  true if the user can access the view, otherwise false
     */
    protected function allowEdit()
    {
        return THM_OrganizerHelperAccess::isAdmin();
    }

    /**
     * Method to get the form
     *
     * @param array $data     Data         (default: array)
     * @param bool  $loadData Load data  (default: true)
     *
     * @return mixed  \JForm object on success, False on error.
     * @throws Exception => unauthorized access
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = [], $loadData = false)
    {
        $allowEdit = $this->allowEdit();
        if (!$allowEdit) {
            throw new \Exception(\JText::_('COM_THM_ORGANIZER_401'), 401);
        }

        $name = $this->get('name');
        $form = $this->loadForm("com_thm_organizer.$name", $name, ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }
}
