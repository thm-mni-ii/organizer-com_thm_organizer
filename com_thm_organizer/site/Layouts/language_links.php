<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use THM_OrganizerHelperLanguages as Languages;
use Joomla\CMS\Uri\Uri;

$current            = Languages::getShortTag();
$lang               = Languages::getLanguage();
$supportedLanguages = ['en' => $lang->_('THM_ORGANIZER_ENGLISH'), 'de' => $lang->_('THM_ORGANIZER_GERMAN')];
unset($supportedLanguages[$current]);

echo '<div class="tool-wrapper language-links">';
if (empty($displayData['view'])) {
    $js = 'document.getElementById(\'languageTag\').value=\'XXX\';document.getElementById(\'adminForm\').submit();';

    foreach ($supportedLanguages as $languageTag => $text) {
        $onClick = str_replace('XXX', $languageTag, $js);
        echo '<a onclick="' . $onClick . '"><span class="icon-world"></span>' . $text . '</a>';
    }
} else {
    $params           = $displayData;
    $params['option'] = 'com_thm_organizer';
    $params           = array_merge($displayData, $params);
    $menuID           = \THM_OrganizerHelperComponent::getInput()->getInt('Itemid');
    if (!empty($menuID)) {
        $params['Itemid'] = $menuID;
    }

    foreach ($supportedLanguages as $languageTag => $text) {
        $params['languageTag'] = $languageTag;
        $href                  = Uri::buildQuery($params);
        echo '<a href="index.php?' . $href . '"><span class="icon-world"></span>' . $text . '</a>';
    }

}
echo '</div>';
