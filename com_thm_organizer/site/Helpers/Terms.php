<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;
use Organizer\Tables\Terms as TermsTable;

/**
 * Provides general functions for term access checks, data retrieval and display.
 */
class Terms extends ResourceHelper implements Selectable
{
	/**
	 * Gets the id of the term whose dates encompass the current date
	 *
	 * @return int the id of the term for the dates used on success, otherwise 0
	 */
	public static function getCurrentID()
	{
		$date  = date('Y-m-d');
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('id')
			->from('#__thm_organizer_terms')
			->where("'$date' BETWEEN startDate and endDate");
		$dbo->setQuery($query);

		return (int) OrganizerHelper::executeQuery('loadResult');
	}

	/**
	 * Checks for the term end date for a given term id
	 *
	 * @param   string  $termID  the term's id
	 *
	 * @return mixed  string the end date of the term could be resolved, otherwise null
	 */
	public static function getEndDate($termID)
	{
		$table = new TermsTable;

		return $table->load($termID) ? $table->endDate : null;
	}

	/**
	 * Checks for the term entry in the database, creating it as necessary.
	 *
	 * @param   array  $data  the term's data
	 *
	 * @return mixed  int the id if the room could be resolved/added, otherwise null
	 */
	public static function getID($data)
	{
		if (empty($data))
		{
			return null;
		}

		$table        = new TermsTable;
		$loadCriteria = ['startDate' => $data['startDate'], 'endDate' => $data['endDate']];

		if ($table->load($loadCriteria))
		{
			return $table->id;
		}

		return $table->save($data) ? $table->id : null;
	}

	/**
	 * Retrieves the ID of the term occurring immediately after the reference term.
	 *
	 * @param   int  $currentID  the id of the reference term
	 *
	 * @return int the id of the subsequent term if successful, otherwise 0
	 */
	public static function getNextID($currentID = 0)
	{
		if (empty($currentID))
		{
			$currentID = self::getCurrentID();
		}

		$currentEndDate = self::getEndDate($currentID);

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('id')
			->from('#__thm_organizer_terms')
			->where("startDate > '$currentEndDate'")
			->order('startDate ASC');
		$dbo->setQuery($query);

		return (int) OrganizerHelper::executeQuery('loadResult');
	}

	/**
	 * Retrieves the selectable options for the resource.
	 *
	 * @param   bool  $withDates  if true the start and end date will be displayed as part of the name
	 *
	 * @return array the available options
	 */
	public static function getOptions($withDates = false)
	{
		$tag     = Languages::getTag();
		$options = [];
		foreach (Terms::getResources() as $term)
		{
			$name = $term["name_$tag"];
			if ($withDates)
			{
				$shortSD = Dates::formatDate($term['startDate']);
				$shortED = Dates::formatDate($term['endDate']);
				$name    .= " ($shortSD - $shortED)";
			}

			$options[] = HTML::_('select.option', $term['id'], $name);
		}

		return $options;
	}

	/**
	 * Retrieves the ID of the term occurring immediately after the reference term.
	 *
	 * @param   int  $currentID  the id of the reference term
	 *
	 * @return int the id of the subsequent term if successful, otherwise 0
	 */
	public static function getPreviousID($currentID = 0)
	{
		if (empty($currentID))
		{
			$currentID = self::getCurrentID();
		}

		$currentStartDate = self::getStartDate($currentID);

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('id')
			->from('#__thm_organizer_terms')
			->where("endDate < '$currentStartDate'")
			->order('endDate DESC');
		$dbo->setQuery($query);

		return (int) OrganizerHelper::executeQuery('loadResult');
	}

	/**
	 * Retrieves the resource items.
	 *
	 * @return array the available resources
	 */
	public static function getResources()
	{
		$dbo = Factory::getDbo();

		$query = $dbo->getQuery(true);
		$query->select('DISTINCT term.*')->from('#__thm_organizer_terms AS term')->order('startDate');
		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadAssocList', []);
	}

	/**
	 * Checks for the term start date for a given term id
	 *
	 * @param   string  $termID  the term's id
	 *
	 * @return mixed  string the end date of the term could be resolved, otherwise null
	 */
	public static function getStartDate($termID)
	{
		$table = new TermsTable;

		return $table->load($termID) ? $table->startDate : null;
	}
}
