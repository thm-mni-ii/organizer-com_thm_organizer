<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerModelForm
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class loads non-item-specific form data.
 *
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerModelForm extends JModelForm
{
    /**
     * Method to get the form
     *
     * @param   Array    $data      Data         (default: Array)
     * @param   Boolean  $loadData  Load data  (default: true)
     *
     * @return  mixed  JForm object on success, False on error.
     * 
     * @throws  Exception  if the user is not authorized to access the view
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = array(), $loadData = false)
    {
        $path = JPATH_ROOT . "/media/com_thm_organizer/helpers/componentHelper.php";        
        require_once $path;

        THM_OrganizerHelperComponent::addActions($this);
        $allowEdit = THM_OrganizerHelperComponent::allowEdit($this);
        if (!$allowEdit)
        {
            throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
        }

        $name = $this->get('name');
        $form = $this->loadForm("com_thm_organizer.$name", $name, array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form))
        {
            return false;
        }

        return $form;
    }
}
