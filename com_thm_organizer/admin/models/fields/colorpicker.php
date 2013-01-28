<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		JFormFieldColorPicker
 * @description JFormFieldColorPicker component admin field
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Class JFormFieldColorPicker for component com_thm_organizer
 *
 * Class provides methods to create a form field
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class JFormFieldColorPicker extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 * @since  1.0
	 */
	protected $type = 'ColorPicker';

	/**
	 * Returns a colorpicker
	 *
	 * @return select box
	 */
	public function getInput()
	{
		$scriptDir = str_replace(JPATH_SITE . DS, '', "administrator/components/com_thm_organizer/models/fields/");
		$document = & JFactory::getDocument();
		$document->addStyleSheet(JUri::root() . '/administrator/components/com_thm_organizer/models/fields/mooRainbow.css');

		// Add script-code to the document head
		JHTML::script('mooRainbow.js', $scriptDir, false);
		$img = JUri::root() . '/administrator/components/com_thm_organizer/models/fields/images/';
		?>
<script>
            var i=0;
            function change_<?php echo $this->fieldname ?>(){
                var r = new MooRainbow('<?php echo $this->name; ?>', {
                    id: '<?php echo $this->fieldname ?>' + i,
                    startColor: $('<?php echo $this->name; ?>').style.backgroundColor,
                    'onChange': function(color) {
                        $('<?php echo $this->name; ?>').value = color.hex;
                        $('<?php echo $this->name; ?>').setStyle("backgroundColor", color.hex);
                    },
                    imgPath: '<?php echo $img; ?>'
                });
                i++;
            }

        </script>
<?php
$html = "<input id='" . $this->name . "' name='" . $this->name . "' type='text' size='13' value='" .
		$this->value . "' style='background-color:" . $this->value . ";' onfocus='change_" . $this->fieldname . "()'/>";

return $html;
	}

}
