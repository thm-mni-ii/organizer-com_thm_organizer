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

$noStar     = JHtml::image(JUri::root() . '/media/com_thm_organizer/images/0stars.png', 'COM_THM_ORGANIZER_ZERO_STARS');
$oneStar    = JHtml::image(JUri::root() . '/media/com_thm_organizer/images/1stars.png', 'COM_THM_ORGANIZER_ONE_STAR');
$twoStars   = JHtml::image(JUri::root() . '/media/com_thm_organizer/images/2stars.png', 'COM_THM_ORGANIZER_TWO_STARS');
$threeStars = JHtml::image(JUri::root() . '/media/com_thm_organizer/images/3stars.png', 'COM_THM_ORGANIZER_THREE_STARS');

$displayExpertise  = isset($this->item->expertise) ? $this->displayStarAttribute($this->item->expertise) : false;
$displayMethodComp = isset($this->item->method_competence) ? $this->displayStarAttribute($this->item->method_competence) : false;
$displaySocialComp = isset($this->item->social_competence) ? $this->displayStarAttribute($this->item->social_competence) : false;
$displaySelfComp   = isset($this->item->self_competence) ? $this->displayStarAttribute($this->item->self_competence) : false;

$prerequisites  = $this->getDependencies('pre');
$postrequisites = $this->getDependencies('post');

?>
<div class="toolbar">
	<div class="tool-wrapper language-switches">
		<?php
		foreach ($this->languageSwitches AS $switch)
		{
			echo $switch;
		}
		?>
	</div>
</div>
<div class="clearfix"></div>
<?php

if (!empty($this->item->name))
{
	?>
	<h1 class="componentheading"><?php echo $this->item->name; ?></h1>
	<?php
}
?>
<div class="subject-list">
	<?php

	if (!empty($this->item->externalID))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_MODULE_CODE') . '</div>';
		echo '<div class="subject-content">' . $this->item->externalID . '</div>';
		echo '</div>';
	}

	if (!empty($this->item->short_name))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_SHORT_NAME') . '</div>';
		echo '<div class="subject-content">' . $this->item->short_name . '</div>';
		echo '</div>';
	}

	if (!empty($this->item->executors))
	{
		echo '<div class="subject-item">';

		if (count($this->item->executors) > 1)
		{
			echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_MODULE_COORDINATORS') . '</div>';
			echo '<div class="subject-content">';
			echo '<ul>';
			foreach ($this->item->executors as $executor)
			{
				echo '<li>';
				$this->getTeacherOutput($executor);
				echo '</li>';
			}
			echo '</ul>';
		}
		else
		{
			echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_MODULE_COORDINATOR') . '</div>';
			echo '<div class="subject-content">';
			$executor = array_values($this->item->executors)[0];
			$this->getTeacherOutput($executor);
		}
		echo '</div>';
		echo '</div>';
	}

	echo '<div class="subject-item">';
	if (!empty($this->item->teachers))
	{

		if (count($this->item->teachers) > 1)
		{
			echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_TEACHERS') . '</div>';
			echo '<div class="subject-content">';
			echo '<ul>';
			foreach ($this->item->teachers as $teacher)
			{
				echo '<li>';
				$this->getTeacherOutput($teacher);
				echo '</li>';
			}
			echo '</ul>';
		}
		else
		{
			echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_TEACHER') . '</div>';
			echo '<div class="subject-content">';
			$teacher = array_values($this->item->teachers)[0];
			$this->getTeacherOutput($teacher);
		}
	}
	else
	{
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_TEACHERS') . '</div>';
		echo '<div class="subject-content">';
		echo $this->lang->_('COM_THM_ORGANIZER_TEACHERS_PLACEHOLDER');

	}
	echo '</div>';
	echo '</div>';

	if (!empty($this->item->description))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_SHORT_DESCRIPTION') . '</div>';
		echo '<div class="subject-content">' . $this->item->description . '</div>';
		echo '</div>';
	}

	if (!empty($this->item->objective))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_OBJECTIVES') . '</div>';
		echo '<div class="subject-content">' . $this->item->objective . '</div>';
		echo '</div>';
	}

	if (!empty($this->item->content))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_CONTENTS') . '</div>';
		echo '<div class="subject-content">' . $this->item->content . '</div>';
		echo '</div>';
	}

	if ($displayExpertise)
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_EXPERTISE') . '</div>';
		echo '<div class="subject-content">';

		if ($this->item->expertise == '3')
		{
			echo $threeStars;
		}
		elseif ($this->item->expertise == '2')
		{
			echo $twoStars;
		}
		elseif ($this->item->expertise == '1')
		{
			echo $oneStar;
		}
		elseif ($this->item->expertise == '0')
		{
			echo $noStar;
		}
		echo '</div></div>';
	}

	if ($displayMethodComp)
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_METHOD_COMPETENCE') . '</div>';
		echo '<div class="subject-content">';
		if ($this->item->method_competence == '3')
		{
			echo $threeStars;
		}
		elseif ($this->item->method_competence == '2')
		{
			echo $twoStars;
		}
		elseif ($this->item->method_competence == '1')
		{
			echo $oneStar;
		}
		elseif ($this->item->method_competence == '0')
		{
			echo $noStar;
		}
		echo '</div></div>';
	}

	if ($displaySocialComp)
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_SOCIAL_COMPETENCE') . '</div>';
		echo '<div class="subject-content">';

		if ($this->item->social_competence == 3)
		{
			echo $threeStars;
		}
		elseif ($this->item->social_competence == 2)
		{
			echo $twoStars;
		}
		elseif ($this->item->social_competence == 1)
		{
			echo $oneStar;
		}
		elseif ($this->item->social_competence == 0)
		{
			echo $noStar;
		}
		echo '</div></div>';
	}

	if ($displaySelfComp)
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_SELF_COMPETENCE') . '</div>';
		echo '<div class="subject-content">';

		if ($this->item->self_competence == '3')
		{
			echo $threeStars;
		}
		elseif ($this->item->self_competence == '2')
		{
			echo $twoStars;
		}
		elseif ($this->item->self_competence == '1')
		{
			echo $oneStar;
		}
		elseif ($this->item->self_competence == '0')
		{
			echo $noStar;
		}
		echo '</div></div>';
	}

	if (!empty($this->item->duration))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_DURATION') . '</div>';
		echo '<div class="subject-content">';
		echo $this->item->duration;
		echo '</div>';
		echo '</div>';
	}

	if (!empty($this->item->instructionLanguage))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_INSTRUCTION_LANGUAGE') . '</div>';
		echo '<div class="subject-content">';
		echo ($this->item->instructionLanguage == 'D') ? 'Deutsch' : 'English';
		echo '</div>';
		echo '</div>';
	}

	if (!empty($this->item->expenditureOutput))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_EXPENDITURE') . '</div>';
		echo '<div class="subject-content">' . $this->item->expenditureOutput . '</div>';
		echo '</div>';
	}

	if (!empty($this->item->sws))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_SWS') . '</div>';
		echo '<div class="subject-content">' . $this->item->sws . '</div>';
		echo '</div>';
	}

	if (!empty($this->item->method))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_METHOD') . '</div>';
		echo '<div class="subject-content">' . $this->item->method . '</div>';
		echo '</div>';
	}

	if (!empty($this->item->preliminary_work))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_PRELIMINARY_WORK') . '</div>';
		echo '<div class="subject-content">' . $this->item->preliminary_work . '</div>';
		echo '</div>';
	}

	if (!empty($this->item->proof))
	{
		$method = empty($this->item->pform) ? '' : ' ( ' . $this->item->pform . ' )';
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_PROOF') . '</div>';
		echo '<div class="subject-content">' . $this->item->proof . $method . '</div>';
		echo '</div>';
	}

	if (!empty($this->item->evaluation))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_EVALUATION') . '</div>';
		echo '<div class="subject-content">' . $this->item->evaluation . '</div>';
		echo '</div>';
	}

	if (!empty($this->item->frequency))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_AVAILABILITY') . '</div>';
		echo '<div class="subject-content">' . $this->item->frequency . '</div>';
		echo '</div>';
	}

	if (!empty($this->item->literature))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_LITERATURE') . '</div>';
		echo '<div class="subject-content" id="litverz">' . $this->item->literature . '</div>';
		echo '</div>';
	}

	if (!empty($this->item->aids))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_STUDY_AIDS') . '</div>';
		echo '<div class="subject-content">' . $this->item->aids . '</div>';
		echo '</div>';
	}

	if (!empty($this->item->prerequisites))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_PREREQUISITES') . '</div>';
		echo '<div class="subject-content">';
		echo $this->item->prerequisites;
		echo '</div></div>';
	}

	if (!empty($prerequisites))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_PREREQUISITE_MODULES') . '</div>';
		echo '<div class="subject-content">';
		echo $prerequisites;
		echo '</div></div>';
	}

	if (!empty($this->item->recommended_prerequisites))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_RECOMMENDED_PREREQUISITES') . '</div>';
		echo '<div class="subject-content">';
		echo $this->item->recommended_prerequisites;
		echo '</div></div>';
	}

	if (!empty($this->item->prerequisiteOf))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_PREREQUISITE_FOR') . '</div>';
		echo '<div class="subject-content">';
		echo $this->item->prerequisiteOf;
		echo '</div></div>';
	}

	if (!empty($postrequisites))
	{
		echo '<div class="subject-item">';
		echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_POSTREQUISITE_MODULES') . '</div>';
		echo '<div class="subject-content">';
		echo $postrequisites;
		echo '</div></div>';
	}

	$displayeCollab = JComponentHelper::getParams('com_thm_organizer')->get('displayeCollabLink');

	if (!empty($this->item->externalID) AND !empty($displayeCollab))
	{
		$ecollabLink = JComponentHelper::getParams('com_thm_organizer')->get('eCollabLink');
		$ecollabIcon = JUri::root() . 'media/com_thm_organizer/images/icon-32-moodle.png';
		echo '<div class="subject-item">';
		echo '<div class="subject-label">eCollaboration Link</div>';
		echo '<div class="subject-content">';
		echo '<a href="' . $ecollabLink . $this->item->externalID . '" target="_blank">';
		echo "<img class='eCollabImage' src='$ecollabIcon' title='eCollabLink'></a>";
		echo '</div></div>';
	}

	?>
	<?php echo $this->disclaimer->render($this->disclaimerData); ?>
</div>
