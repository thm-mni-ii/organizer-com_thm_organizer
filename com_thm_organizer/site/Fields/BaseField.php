<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Organizer\Helpers\Languages;

/**
 * Class creates select input.
 */
class BaseField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    protected $type = 'Base';

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     */
    protected function getLayoutData()
    {
        if (!empty($this->element['label'])) {
            $labelConstant          = 'THM_ORGANIZER_' . (string)$this->element['label'];
            $descriptionConstant    = $labelConstant . '_DESC';
            $this->element['label'] = Languages::_($labelConstant);
            $this->description      = Languages::_($descriptionConstant);
        }

        return parent::getLayoutData();
    }
}