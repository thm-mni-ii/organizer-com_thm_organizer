<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

// Course Status
$current = $this->lang->_('COM_THM_ORGANIZER_CURRENT');
$expired = $this->lang->_('COM_THM_ORGANIZER_EXPIRED');

// Personal Status
$none          = '-';
$notLoggedIn   = '<span class="icon-warning"></span>' . $this->lang->_('COM_THM_ORGANIZER_NOT_LOGGED_IN');
$notRegistered = '<span class="icon-checkbox-unchecked"></span>' . $this->lang->_('COM_THM_ORGANIZER_COURSE_NOT_REGISTERED');
$waitList      = '<span class="icon-checkbox-partial"></span>' . $this->lang->_('COM_THM_ORGANIZER_WAIT_LIST');
$registered    = '<span class="icon-checkbox-checked"></span>' . $this->lang->_('COM_THM_ORGANIZER_COURSE_REGISTERED');
$manage        = '<span class="icon-cogs"></span>' . $this->lang->_("COM_THM_ORGANIZER_MANAGE");

$menuID = JFactory::getApplication()->input->getInt('Itemid', 0);

$pathPrefix      = "index.php?option=com_thm_organizer";
$subjectURL      = "{$pathPrefix}&view=subject_details&languageTag={$this->shortTag}";
$subjectURL      .= empty($menuID) ? '' : "&Itemid=$menuID";
$managerURL      = "{$pathPrefix}&view=course_manager&languageTag={$this->shortTag}";
$managerURL      .= empty($menuID) ? '' : "&Itemid=$menuID";
$registrationURL = "{$pathPrefix}&task=participant.register&languageTag={$this->shortTag}";
$registrationURL .= empty($menuID) ? '' : "&Itemid=$menuID";

foreach ($this->items as $item)
{
	$subjectRoute = JRoute::_($subjectURL . "&id={$item->subjectID}");

	$startDate   = THM_OrganizerHelperComponent::formatDate($item->start);
	$endDate     = THM_OrganizerHelperComponent::formatDate($item->end);
	$displayDate = $startDate == $endDate ? $endDate : "$startDate - $endDate";

	if (!empty(JFactory::getUser()->id))
	{
		$lessonURL = "&lessonID={$item->lessonID}";

		if ($item->admin)
		{
			$managerRoute = JRoute::_($managerURL . $lessonURL);
			$userStatus   = "<a href='$managerRoute'>$manage</a>";
			$register     = '';
		}
		else
		{
			$regState = THM_OrganizerHelperCourse::getRegistrationState($item->lessonID);

			if ($item->expired)
			{
				if (!empty($regState))
				{
					if ($regState["status"] == 1)
					{
						$userStatus = '<span class="disabled">' . $registered . '</span>';
					}
					else
					{
						$userStatus = '<span class="disabled">' . $waitList . '</span>';
					}
				}
				else
				{
					$userStatus = '<span class="disabled">' . $none . '</span>';
				}

				$register = '';
			}
			else
			{
				$registerRoute = JRoute::_($registrationURL . $lessonURL);
				$disabled      = THM_OrganizerHelperCourse::isRegistrationOpen($item->lessonID) ? '' : 'disabled';

				if (!empty($regState))
				{
					$registerText = '<span class="icon-out-2"></span>' . $this->lang->_('JLOGOUT');

					if ($regState["status"] == 1)
					{
						$userStatus = $registered;
					}
					else
					{
						$userStatus = $waitList;
					}
				}
				else
				{
					$userStatus   = $item->expired ? $none : $notRegistered;
					$registerText = '<span class="icon-apply"></span>' . $this->lang->_('JLOGIN');
				}

				$register = "<a href='$registerRoute' class='$disabled' type='button'>$registerText</a>";
			}
		}
	}
	else
	{
		$userStatus = $item->expired ? $none : '<span class="disabled">' . $notLoggedIn . '</span>';
		$register   = '';
	}

	$courseStatus = $item->expired ? '<span class="disabled">' . $expired . '</span>' : $current;

	?>
	<tr class='row'>
		<td>
			<a href='<?php echo $subjectRoute; ?>'>
				<?php echo $item->name; ?>
			</a>
		</td>
		<td><?php echo $displayDate; ?></td>
		<td class="course-state"><?php echo $courseStatus ?></td>
		<td class="user-state"><?php echo $userStatus ?></td>
		<td class="registration"><?php echo $register ?></td>
	</tr>
	<?php
}
