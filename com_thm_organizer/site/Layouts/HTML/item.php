<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;

$resourceID = Input::getID();
$view       = Input::getView();

$action = "?option=com_thm_organizer&view=$view&id=$resourceID";
require_once 'language_selection.php';
echo OrganizerHelper::getApplication()->JComponentTitle; ?>
<div id="j-main-container" class="span10">
    <?php
    foreach ($this->item as $key => $attribute) {
        echo '<div class="attribute-item">';
        echo '<div class="attribute-label">' . $attribute['label'] . '</div>';
        echo '<div class="attribute-content">';
        switch ($attribute['type']) {
            case 'list':
                $urlAttribs = ['target' => '_blank'];
                $url        = empty($attribute['url']) ? '' : $attribute['url'];
                $this->renderListValue($attribute['value'], $url, $urlAttribs);
                break;
            case 'location':
                $pin = Campuses::getPin($attribute['location']);
                echo "$pin {$attribute['value']}";
                break;
            case 'star':
                $this->renderStarValue($attribute['value']);
                break;
            case 'text':
            default:
                echo $attribute['value'];
                break;
        }
        echo '</div></div>';
    }
    echo $this->disclaimer;
    ?>
</div>
