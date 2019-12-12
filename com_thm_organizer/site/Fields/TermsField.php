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

use Organizer\Helpers\Terms;

/**
 * Class creates a select box for terms.
 */
class TermsField extends OptionsField
{
    /**
     * @var  string
     */
    protected $type = 'Terms';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $options = parent::getOptions();
        $terms   = Terms::getOptions((bool)$this->getAttribute('withDates'));

        return array_merge($options, $terms);
    }
}
