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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/mapping.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/OrganizerHelper.php';

use THM_OrganizerHelperHTML as HTML;
use Joomla\CMS\Uri\Uri;

/**
 * Class creates a select box for (degree) program mappings.
 */
class JFormFieldPrograms extends \Joomla\CMS\Form\FormField
{
    /**
     * @var  string
     */
    protected $type = 'programs';

    /**
     * Returns a select box where stored degree program can be chosen
     *
     * @return string  the HTML for the select box
     */
    public function getInput()
    {
        $resourceID   = $this->form->getValue('id');
        $contextParts = explode('.', $this->form->getName());
        $resourceType = str_replace('_edit', '', $contextParts[1]);
        $this->addScript($resourceID, $resourceType);

        $ranges           = THM_OrganizerHelperMapping::getResourceRanges($resourceType, $resourceID);
        $selectedPrograms = empty($ranges) ? [] : THM_OrganizerHelperMapping::getSelectedPrograms($ranges);
        $options          = THM_OrganizerHelperMapping::getProgramOptions();

        foreach ($options as $id => $name) {
            if (!THM_OrganizerHelperAccess::allowDocumentAccess('program', $id)) {
                unset($options[$id]);
            }
        }

        $defaultOptions = ['-1' => Languages::_('JNONE')];
        $programs       = $defaultOptions + $options;
        $attributes     = ['multiple' => 'multiple', 'size' => '10'];

        return HTML::selectBox($programs, 'programID', $attributes, $selectedPrograms, true);
    }

    /**
     * Adds the javascript to the page necessary to refresh the parent pool options
     *
     * @param int    $resourceID   the resource's id
     * @param string $resourceType the resource's type
     *
     * @return void
     */
    private function addScript($resourceID, $resourceType)
    {
        ?>
        <script type="text/javascript" charset="utf-8">
            jQuery(document).ready(function () {
                jQuery('#jformprogramID').change(function () {
                    const programInput = jQuery('#jformprogramID'),
                        parentInput = jQuery('#jformparentID'),
                        oldSelectedParents = parentInput.val();
                    let selectedPrograms = programInput.val(),
                        poolUrl;

                    if (selectedPrograms === null)
                    {
                        selectedPrograms = '';
                    }
                    else if (Array.isArray(selectedPrograms))
                    {
                        selectedPrograms = selectedPrograms.join(',');
                    }

                    if (selectedPrograms.includes('-1') !== false)
                    {
                        programInput.find('option').removeAttr('selected');
                        return false;
                    }

                    poolUrl = '<?php echo Uri::root(); ?>index.php?option=com_thm_organizer';
                    poolUrl += '&view=pool_ajax&format=raw&task=parentOptions';
                    poolUrl += "&id=<?php echo $resourceID; ?>";
                    poolUrl += "&type=<?php echo $resourceType; ?>";
                    poolUrl += '&programIDs=' + selectedPrograms;

                    jQuery.get(poolUrl, function (options) {
                        parentInput.html(options);
                        const newSelectedParents = parentInput.val();
                        let selectedParents = [];
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
                    const chosenElement = jQuery('#' + id);
                    chosenElement.chosen('destroy');
                    chosenElement.chosen();
                }

                function toggleElement(chosenElement, value)
                {
                    const parentInput = jQuery('#jformparentID');
                    parentInput.chosen('destroy');
                    jQuery('select#jformparentID option').each(function () {
                        if (chosenElement === jQuery(this).innerHTML)
                        {
                            jQuery(this).prop('selected', value);
                        }
                    });
                    parentInput.chosen();
                }

                function addAddHandler()
                {
                    jQuery('#jformparentID_chzn').find('div.chzn-drop').click(function (element) {
                        toggleElement(element.target.innerHTML, true);
                        addRemoveHandler();
                    });
                }

                function addRemoveHandler()
                {
                    jQuery('div#jformparentID_chzn').find('a.search-choice-close').click(function (element) {
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
