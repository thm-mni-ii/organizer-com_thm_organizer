<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use DateTime;
use Joomla\CMS\Factory;

/**
 * Class provides generalized functions regarding dates and times.
 */
class Colors
{
    /**
     * Returns the color value for a given colorID.
     *
     * @param int $colorID the id of the color
     *
     * @return string the hex value of the color
     */
    public static function getColor($colorID)
    {
        $table = OrganizerHelper::getTable('Colors');

        return $table->load($colorID) ? $table->color : '';
    }

    /**
     * Creates a container to output text with a system specific color.
     *
     * @param string $text    the text to display
     * @param ing    $colorID the id of the color
     *
     * @return string
     */
    public static function getListDisplay($text, $colorID)
    {
        $styles = ['text-align:center;'];
        if (!empty($colorID)) {
            $bgColor   = self::getColor($colorID);
            $styles[]  = "background-color:$bgColor;";
            $textColor = self::getDynamicTextColor($bgColor);
            $styles[]  = "color:$textColor;";
        }

        return '<div style="' . implode($styles) . '">' . $text . '</div>';
    }

    /**
     * Gets an appropriate value for contrasting text color for a given background color.
     *
     * @param string $bgColor the background color with which do
     *
     * @return string  the hexadecimal value for an appropriate text color
     */
    public static function getDynamicTextColor($bgColor)
    {
        $color              = substr($bgColor, 1);
        $params             = Input::getParams();
        $red                = hexdec(substr($color, 0, 2));
        $green              = hexdec(substr($color, 2, 2));
        $blue               = hexdec(substr($color, 4, 2));
        $relativeBrightness = ($red * 299) + ($green * 587) + ($blue * 114);
        $brightness         = $relativeBrightness / 1000;
        if ($brightness >= 128) {
            return $params->get('darkTextColor', '#4a5c66');
        } else {
            return $params->get('lightTextColor', '#ffffff');
        }
    }
}
