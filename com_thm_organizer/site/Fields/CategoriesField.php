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

use Organizer\Helpers\Categories;

/**
 * Class creates a select box for plan programs.
 */
class CategoriesField extends OptionsField
{

    /**
     * @var  string
     */
    protected $type = 'Categories';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return array the options for the select box
     */
    protected function getOptions()
    {
        $options    = parent::getOptions();
        $categories = Categories::getOptions();

        return array_merge($options, $categories);
    }
}
