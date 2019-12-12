<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

HTML::_('searchtools.form', '#languageForm', []);
$languageAction = OrganizerHelper::dynamic() ? "?option=com_thm_organizer&view=$view&id=$resourceID" : '?';
$selectedTag    = Languages::getTag();
$languages      = [Languages::_('THM_ORGANIZER_ENGLISH') => 'en', Languages::_('THM_ORGANIZER_GERMAN') => 'de'];
ksort($languages);
$options = [];
foreach ($languages as $language => $tag)
{
	$selected  = $selectedTag === $tag ? ' selected="selected"' : '';
	$options[] = "<option value=\"$tag\"$selected>$language</option>";
}
$options = implode('', $options);
?>
<form id="languageForm" name="languageForm" method="post" action="<?php echo $languageAction; ?>"
      class="form-horizontal">
    <div class="js-stools clearfix">
        <div class="clearfix">
            <div class="js-stools-container-list">
                <div class="ordering-select">
                    <div class="js-stools-field-list">
                        <select id="languageTag" name="languageTag" onchange="this.form.submit();">
							<?php echo $options ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
