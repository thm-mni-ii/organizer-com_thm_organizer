<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Campuses;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class which loads data into the view output context
 */
class Courses extends ListView
{
	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_COURSES_TITLE'), 'contract-2');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'course.add', false);
		$toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'course.edit', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('THM_ORGANIZER_DELETE'),
			'course.delete',
			true
		);
		HTML::setPreferencesButton();
	}

    /**
     * Function determines whether the user may access the view.
     *
     * @return bool true if the user may access the view, otherwise false
     */
    protected function allowAccess()
    {
        return true;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering  = $this->state->get('list.ordering');
        $direction = $this->state->get('list.direction');
        $headers   = [];
        $headers['checkbox']        = '';
        $headers['name']            = HTML::sort('NAME', 'name', $direction, $ordering);
        $headers['campus']          = Languages::_('THM_ORGANIZER_CAMPUS');
        $headers['term']            = Languages::_('THM_ORGANIZER_TERM');
        $headers['maxParticipants'] = Languages::_('THM_ORGANIZER_MAX_PARTICIPANTS');
        $headers['state']           = Languages::_('THM_ORGANIZER_STATE');

		return $headers;
	}

	/**
	 * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
	 *
	 * @return void processes the class items property
	 */
	protected function structureItems()
	{
		if (empty($this->items))
		{
			return;
		}

		$index          = 0;
		$link           = "index.php?option=com_thm_organizer&view=course_edit&id=";
		$processedItems = [];

		foreach ($this->items as $item)
		{

			$campus          = Campuses::getName($item->campusID);
			$maxParticipants = empty($item->maxParticipants) ? 1000 : $item->maxParticipants;

			$today = date('Y-m-d');
			if ($item->end < $today)
			{
				$status = Languages::_('THM_ORGANIZER_EXPIRED');
			}
			elseif ($item->start > $today)
			{
				$status = Languages::_('THM_ORGANIZER_PENDING');
			}
			else
			{
				$status = Languages::_('THM_ORGANIZER_CURRENT');
			}

			$thisLink                                  = $link . $item->id;
			$processedItems[$index]                    = [];
			$processedItems[$index]['checkbox']        = HTML::_('grid.id', $index, $item->id);
			$processedItems[$index]['name']            = HTML::_('link', $thisLink, $item->name);
			$processedItems[$index]['campus']          = HTML::_('link', $thisLink, $campus);
			$processedItems[$index]['term']            = HTML::_('link', $thisLink, $item->term);
			$processedItems[$index]['maxParticipants'] = HTML::_('link', $thisLink, $maxParticipants);
			$processedItems[$index]['status']          = HTML::_('link', $thisLink, $status);

			$index++;
		}
		$this->items = $processedItems;
	}
}
