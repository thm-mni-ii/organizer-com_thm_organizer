<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('subform');

/**
 * Class loads multiple/repeatable grid blocks from database and make it possible to advance them.
 * This needs an own form field to load the values, maybe because the periods are saved as json string.
 */
class PeriodsField extends \JFormFieldSubform
{
    /**
     * Type
     *
     * @var    String
     */
    protected $type = 'Periods';

    /**
     * Method to get the multiple field input of the loaded grids periods
     *
     * @return string  The field input markup.
     */
    protected function getInput()
    {
        $this->value = isset($this->value['periods']) ? $this->value['periods'] : [];

        return parent::getInput();
    }
}
