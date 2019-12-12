<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Organizer\Helpers\Languages;

/**
 * Class loads the subject into the display context.
 */
class SubjectItem extends ItemView
{
    /**
     * Renders a number of stars appropriate to the value
     *
     * @param string $value the value of the star attribute
     *
     * @return void outputs HTML
     */
    public function renderStarValue($value)
    {
        $invalid = (is_null($value) or $value > 3);
        if ($invalid) {
            return;
        }

        $option = 'THM_ORGANIZER_';
        switch ($value) {
            case 3:
                $stars = '<span class="icon-featured"></span>';
                $stars .= '<span class="icon-featured"></span>';
                $stars .= '<span class="icon-featured"></span>';
                $aria  = Languages::_($option . 'THREE_STARS');
                break;
            case 2:
                $stars = '<span class="icon-featured"></span>';
                $stars .= '<span class="icon-featured"></span>';
                $stars .= '<span class="icon-unfeatured"></span>';
                $aria  = Languages::_($option . 'TWO_STARS');
                break;
            case 1:
                $stars = '<span class="icon-featured"></span>';
                $stars .= '<span class="icon-unfeatured"></span>';
                $stars .= '<span class="icon-unfeatured"></span>';
                $aria  = Languages::_($option . 'ONE_STAR');
                break;
            case 0:
            default:
                $stars = '<span class="icon-unfeatured"></span>';
                $stars .= '<span class="icon-unfeatured"></span>';
                $stars .= '<span class="icon-unfeatured"></span>';
                $aria  = Languages::_($option . 'NO_STARS');
                break;
        }

        echo '<span aria-label="' . $aria . '">' . $stars . '</span>';
    }
}
