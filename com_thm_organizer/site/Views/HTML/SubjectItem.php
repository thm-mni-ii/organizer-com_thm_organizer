<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Router\Route;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads the subject into the display context.
 */
class SubjectItem extends ItemView
{
    /**
     * Creates a basic output for processed values
     *
     * @param string $attribute the attribute name
     * @param mixed  $data      the data to be displayed array|string
     *
     * @return void outputs HTML
     */
    public function renderAttribute($attribute, $data)
    {
        if (empty($data['label']) or empty($data['value'])) {
            return;
        }

        $starAttributes = ['expertise', 'methodCompetence', 'selfCompetence', 'socialCompetence'];
        echo '<div class="subject-item">';
        echo '<div class="subject-label">' . $data['label'] . '</div>';
        echo '<div class="subject-content attribute-' . $attribute . '">';
        if (is_array($data['value'])) {
            $this->renderListValue($attribute, $data['value']);
        } elseif (in_array($attribute, $starAttributes)) {
            $this->renderStarValue($data['value']);
        } elseif ($attribute == 'campus') {
            if (!empty($data['location'])) {
                $pin = Campuses::getPin($data['location']);
                echo "$pin {$data['value']}";
            } else {
                echo $data['value'];
            }
        } else {
            echo $data['value'];
        }
        echo '</div></div>';
    }

    /**
     * Renders array values as lists
     *
     * @param array $value the array value to render
     *
     * @return void outputs html directly
     */
    private function renderListValue($attribute, $value)
    {
        $linkAttribs = ['target' => '_blank'];
        $subjectHref = "index.php?view=subject_item&languageTag={$this->tag}&id=";
        echo '<ul>';
        foreach ($value as $id => $data) {
            echo '<li>';
            if (is_array($data)) {
                echo $id;
                $this->renderListValue($attribute, $data);
            } else {
                if ($attribute == 'preRequisiteModules' or $attribute == 'postRequisiteModules') {
                    echo HTML::link(Route::_($subjectHref . $id), $data, $linkAttribs);
                } else {
                    echo $data;
                }
            }
            echo '</li>';
        }
        echo '</ul>';
    }

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
