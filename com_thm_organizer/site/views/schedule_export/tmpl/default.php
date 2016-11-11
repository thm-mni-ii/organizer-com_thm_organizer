<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

$attribs = array();

$fileFormats = array();
$fileFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_ICS_CALENDAR'), 'value' => 'ics');
$fileFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_PDF_DOCUMENT'), 'value' => 'pdf');
$fileFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_XLS_SPREADSHEET'), 'value' => 'xls');
$defaultFileFormat = 'pdf';

$documentFormats = array();
$documentFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_A3_SHEET'), 'value' => 'A3');
$documentFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_A4_SHEET'), 'value' => 'A4');
$defaultDocumentFormat = 'A4';

$displayFormats = array();
$displayFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_LIST'), 'value' => 'list');
$displayFormats[] = array('text' => JText::_('COM_THM_ORGANIZER_TIME_TABLE'), 'value' => 'timeTable');
$defaultDisplayFormat = 'timeTable';

?>
<div id="j-main-container">
	<div id="header-container" class="header-container">
		<?php echo JText::_('COM_THM_ORGANIZER_SCHEDULE_EXPORT_TITLE'); ?>
	</div>
	<form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm" target="_blank">
<?php
		echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'resources'));
		echo JHtml::_('bootstrap.addTab', 'myTab', 'resources', $this->lang->_('COM_THM_ORGANIZER_RESOURCE_SETTINGS'));

		foreach ($this->fields['resourceSettings'] as $resourceID => $resource)
		{
			echo '<div class="control-label">';
			echo '<label for="' . $resourceID . '">' . $resource['label'] . '</label>';
			echo '</div>';
			echo '<div class="controls">';
			echo $resource['input'];
			echo '</div>';
		}

		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.addTab', 'myTab', 'format', $this->lang->_('COM_THM_ORGANIZER_FORMAT_SETTINGS'));

		foreach ($this->fields['formatSettings'] as $formatFieldID => $formatField)
		{
			echo '<div class="control-label">';
			echo '<label for="' . $formatFieldID . '">' . $formatField['label'] . '</label>';
			echo '</div>';
			echo '<div class="controls">';
			echo $formatField['input'];
			echo '</div>';
		}

		echo JHtml::_('bootstrap.endTab');
		echo JHtml::_('bootstrap.endTabSet');
?>
	</form>
</div>
