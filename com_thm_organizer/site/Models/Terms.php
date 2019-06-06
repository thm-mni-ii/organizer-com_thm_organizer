<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Organizer\Helpers\Terms as TermsHelper;

/**
 * Class provides term options for a given department/program. Called from the room statistics view.
 */
class Terms extends BaseModel
{
    /**
     * Gets the pool options as a string
     *
     * @return string the concatenated group options
     */
    public function getOptions()
    {
        $terms   = TermsHelper::getTerms();
        $options = [];

        foreach ($terms as $term) {
            $shortSD = Dates::formatDate($term['startDate']);
            $shortED = Dates::formatDate($term['endDate']);

            $option['value'] = $term['id'];
            $option['text']  = "{$term['name']} ($shortSD - $shortED)";
            $options[]       = $option;
        }

        return json_encode($options);
    }
}
