<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('subform');

/**
 * Class loads multiple/repeatable Instance Resources from database and make it possible to advance them.
 */
class InstanceResourcesField extends \JFormFieldSubform
{
    /**
     * Type
     *
     * @var    String
     */
    protected $type = 'InstanceResources';

    /**
     * Method to get the multiple field input of the loaded Resources in Instances Section
     *
     * @return string  The field input markup.
     */
    protected function getInput()
    {
        $this->value = isset($this->value) ? $this->value : [];

        return parent::getInput();
    }
}
