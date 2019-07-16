<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Organizer\Helpers\Access;
use Organizer\Helpers\Input;

/**
 * Class loads a form for editing data.
 */
class SubjectEdit extends EditModel
{
    protected $deptResource;

    public $item = null;

    /**
     * Checks for user authorization to access the view
     *
     * @param int $subjectID the id of the subject for which authorization is to be checked
     *
     * @return bool  true if the user can access the view, otherwise false
     */
    protected function allowEdit($subjectID = null)
    {
        return Access::allowSubjectAccess($subjectID);
    }

    /**
     * Method to get the form
     *
     * @param array $data     Data         (default: array)
     * @param bool  $loadData Load data  (default: true)
     *
     * @return mixed  \JForm object on success, False on error.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = [], $loadData = true)
    {
        $name = $this->get('name');
        $form = $this->loadForm("com_thm_organizer.$name", $name, ['control' => 'jform', 'load_data' => $loadData]);

        return empty($form) ? false : $form;
    }

    /**
     * Method to get a single record.
     *
     * @param integer $pk The id of the primary key.
     *
     * @return mixed    Object on success, false on failure.
     * @throws Exception => unauthorized access
     */
    public function getItem($pk = null)
    {
        // Prevents duplicate execution from getForm and getItem
        if (isset($this->item->id) and ($this->item->id === $pk or $pk === null)) {
            return $this->item;
        }

        $this->item = parent::getItem($pk);
        $allowEdit  = $this->allowEdit();

        if (!$allowEdit) {
            throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
        }

        return $this->item;
    }

    /**
     * Method to load the form data
     *
     * @return object
     * @throws Exception => unauthorized access
     */
    protected function loadFormData()
    {
        $resourceIDs = Input::getSelectedIDs();
        $resourceID  = empty($resourceIDs) ? 0 : $resourceIDs[0];

        return $this->getItem($resourceID);
    }
}