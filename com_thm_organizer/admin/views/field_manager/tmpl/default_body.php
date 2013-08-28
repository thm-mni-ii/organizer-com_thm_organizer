<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view colors default body
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
foreach ($this->items as $i => $item)
{
?>
<tr class="row<?php echo $i % 2; ?>">
    <td align="center">
        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
    </td>
    <td>
        <a href="index.php?option=com_thm_organizer&view=field_edit&id=<?php echo $item->id; ?>">
            <?php echo $item->field; ?>
        </a>
    </td>
    <td>
        <a href="index.php?option=com_thm_organizer&view=field_edit&id=<?php echo $item->id; ?>">
            <?php echo $item->gpuntisID; ?>
        </a>
    </td>
    <td align="center" style="background-color: <?php echo "#$item->color"; ?>">
        <a style="color: black;" href="index.php?option=com_thm_organizer&view=field_edit&id=<?php echo $item->id; ?>">
            <?php echo $item->name; ?>
        </a>
    </td>
</tr>
<?php
}
