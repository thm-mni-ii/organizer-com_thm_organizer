<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        JFormFieldPrograms
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Class creates a form field for subject-degree program association
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class JFormFieldPrograms extends JFormField
{
	/**
	 * @var  string
	 */
	protected $type = 'programs';

	/**
	 * Returns a select box where stored degree program can be chosen
	 *
	 * @return  string  the HTML for the select box
	 */
	public function getInput()
	{
		$resourceID   = $this->form->getValue('id');
		$contextParts = explode('.', $this->form->getName());
		$resourceType = str_replace('_edit', '', $contextParts[1]);
		$this->addScript($resourceID, $resourceType);

		$ranges           = THM_OrganizerHelperMapping::getResourceRanges($resourceType, $resourceID);
		$selectedPrograms = !empty($ranges) ?
			THM_OrganizerHelperMapping::getSelectedPrograms($ranges) : array();
		$allPrograms      = THM_OrganizerHelperMapping::getAllPrograms();

		$defaultOptions = array(array('value' => '-1', 'text' => JText::_('JNONE')));
		$programs       = array_merge($defaultOptions, $allPrograms);

		$attributes = array('multiple' => 'multiple', 'size' => '10');

		return JHtml::_("select.genericlist", $programs, "jform[programID][]", $attributes, "value", "text", $selectedPrograms);
	}

	/**
	 * Adds the javascript to the page necessary to refresh the parent pool options
	 *
	 * @param int    $resourceID   the resource's id
	 * @param string $resourceType the resource's type
	 *
	 * @return  void
	 */
	private function addScript($resourceID, $resourceType)
	{
		?>
        <script type="text/javascript" charset="utf-8">
            jQuery(document).ready(function ()
            {
                jQuery('#jformprogramID').change(function ()
                {
                    var programInput = jQuery('#jformprogramID'), selectedPrograms = programInput.val(),
                        parentInput = jQuery('#jformparentID'), oldSelectedParents = parentInput.val();

                    if (selectedPrograms === null)
                    {
                        selectedPrograms = '';
                    }
                    else
                    {
                        selectedPrograms = selectedPrograms.join(',');
                    }

                    if (jQuery.inArray('-1', selectedPrograms) != '-1')
                    {
                        programInput.find('option').removeAttr("selected");
                        return false;
                    }

                    var poolUrl = "<?php echo JUri::root(); ?>index.php?option=com_thm_organizer";
                    poolUrl += "&view=pool_ajax&format=raw&task=parentOptions";
                    poolUrl += "&id=<?php echo $resourceID; ?>";
                    poolUrl += "&type=<?php echo $resourceType; ?>";
                    poolUrl += "&programIDs=" + selectedPrograms;
                    jQuery.get(poolUrl, function (options)
                    {
                        parentInput.html(options);
                        var newSelectedParents = parentInput.val();
                        var selectedParents = [];
                        if (newSelectedParents !== null && newSelectedParents.length)
                        {
                            if (oldSelectedParents !== null && oldSelectedParents.length)
                            {
                                selectedParents = jQuery.merge(newSelectedParents, oldSelectedParents);
                            }
                            else
                            {
                                selectedParents = newSelectedParents;
                            }
                        }
                        else if (oldSelectedParents !== null && oldSelectedParents.length)
                        {
                            selectedParents = oldSelectedParents;
                        }

                        parentInput.val(selectedParents);

                        refreshChosen('jformparentID');
                    });
                    refreshChosen('jformparentID');
                });

                function refreshChosen(id)
                {
                    var chosenElement = jQuery("#" + id);
                    chosenElement.chosen("destroy");
                    chosenElement.chosen();
                }

                function toggleElement(chosenElement, value)
                {
                    var parentInput = jQuery("#jformparentID");
                    parentInput.chosen("destroy");
                    jQuery("select#jformparentID option").each(function ()
                    {
                        if (chosenElement == $(this).innerHTML)
                        {
                            jQuery(this).prop('selected', value);
                        }
                    });
                    parentInput.chosen();
                }

                function addAddHandler()
                {
                    jQuery('#jformparentID_chzn').find('div.chzn-drop').click(function (element)
                    {
                        toggleElement(element.target.innerHTML, true);
                        addRemoveHandler();
                    });
                }

                function addRemoveHandler()
                {
                    jQuery('div#jformparentID_chzn').find('a.search-choice-close').click(function (element)
                    {
                        toggleElement(element.target.parentElement.childNodes[0].innerHTML, false);
                        addAddHandler();
                    });
                }

                addRemoveHandler();
                addAddHandler();
            });
        </script>
		<?php
	}
}
