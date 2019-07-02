<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;

$current            = Languages::getTag();
$supportedLanguages = ['en' => Languages::_('THM_ORGANIZER_ENGLISH'), 'de' => Languages::_('THM_ORGANIZER_GERMAN')];
unset($supportedLanguages[$current]);

echo '<div class="tool-wrapper language-links">';
if (empty($displayData['view'])) {
    $js = 'document.getElementById(\'languageTag\').value=\'XXX\';document.getElementById(\'adminForm\').submit();';

    foreach ($supportedLanguages as $tag => $text) {
        $onClick = str_replace('XXX', $tag, $js);
        echo '<a onclick="' . $onClick . '"><span class="icon-world"></span>' . $text . '</a>';
    }
} else {
    $params           = $displayData;
    $params['option'] = 'com_thm_organizer';
    $params           = array_merge($displayData, $params);
    $menuID           = Input::getItemid();
    if (!empty($menuID)) {
        $params['Itemid'] = $menuID;
    }

    foreach ($supportedLanguages as $tag => $text) {
        $params['languageTag'] = $tag;
        $href                  = Uri::buildQuery($params);
        echo '<a href="index.php?' . $href . '"><span class="icon-world"></span>' . $text . '</a>';
    }
}
echo '</div>';
