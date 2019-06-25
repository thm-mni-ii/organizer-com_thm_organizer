<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Organizer\Helpers\HTML;

/**
 * Class creates a form field for template selection.
 * @todo rename this and make it generally accessible should this usage occur again.
 */
class TemplatesField extends OptionsField
{
    /**
     * @var  string
     */
    protected $type = 'Templates';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        return HTML::getTranslatedOptions($this, $this->element);
    }
}
