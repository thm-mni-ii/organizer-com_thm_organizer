<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers\Validators;

use Exception;
use Organizer\Helpers as Helpers;
use Organizer\Helpers\Languages;
use Organizer\Tables\Terms as TermsTable;
use stdClass;

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class Terms extends Helpers\ResourceHelper implements UntisXMLValidator
{
	/**
	 * Retrieves the resource id using the term code. Creates the resource id if unavailable.
	 *
	 * @param   Schedules &$model  the validating schedule model
	 * @param   string     $code   the textual id of the term
	 *
	 * @return void modifies the model, setting the id property of the resource
	 */
	public static function setID(&$model, $code)
	{
		$exists       = false;
		$loadCriteria = [
			['code' => $code],
			['endDate' => $model->term->endDate, 'startDate' => $model->term->startDate]
		];

		$table = new TermsTable;

		foreach ($loadCriteria as $criterion)
		{
			if ($exists = $table->load($criterion))
			{
				break;
			}
		}

		if (!$exists)
		{
			$term         = (array) $model->term;
			$term['code'] = $code;
			$shortEndYear = date('y', $term['endDate']);
			$startYear    = date('Y', $term['startDate']);
			$endYear      = date('Y', $term['endDate']);
			$shortYear    = $endYear !== $startYear ? "$startYear/$shortEndYear" : $startYear;

			switch ($term['name'])
			{
				case 'SS':
					$term['name_de']     = "SS $shortYear";
					$term['name_en']     = "Spring $startYear";
					$term['fullName_de'] = "Sommersemester $shortYear";
					$term['fullName_en'] = "Spring Term $startYear";
					break;
				case 'WS':
					$term['name_de']     = "WS $shortYear";
					$term['name_en']     = "Fall $startYear";
					$term['fullName_de'] = "Wintersemester $shortYear";
					$term['fullName_en'] = "Fall Term $startYear";
					break;
				default:
					$term['name_de']     = "{$term['name']} $shortYear";
					$term['name_en']     = "{$term['name']} $startYear";
					$term['fullName_de'] = "{$term['name']} $shortYear";
					$term['fullName_en'] = "{$term['name']} $startYear";
					break;
			}
			$table->save($term);
		}

		$model->termID = $table->id;

		return;
	}

	/**
	 * Checks whether XML node has the expected structure and required information.
	 *
	 * @param   Schedules &  $model  the validating schedule model
	 * @param   object &     $node   the node to be validated
	 *
	 * @return void
	 * @throws Exception
	 */
	public static function validate(&$model, &$node)
	{
		$model->schoolYear            = new stdClass;
		$model->schoolYear->endDate   = trim((string) $node->schoolyearenddate);
		$model->schoolYear->startDate = trim((string) $node->schoolyearbegindate);

		$validSYED = $model->validateDate($model->schoolYear->endDate, 'SCHOOL_YEAR_END_DATE');
		$validSYSD = $model->validateDate($model->schoolYear->startDate, 'SCHOOL_YEAR_START_DATE');
		$valid     = ($validSYED and $validSYSD);

		$term            = new stdClass;
		$term->endDate   = trim((string) $node->termenddate);
		$validTED        = $model->validateDate($term->endDate, 'TERM_END_DATE');
		$term->name      = trim((string) $node->footer);
		$validTN         = $model->validateText($term->name, 'TERM_NAME', '/[\#\;]/');
		$term->startDate = trim((string) $node->termbegindate);
		$validTSD        = $model->validateDate($term->startDate, 'TERM_START_DATE');
		$valid           = ($valid and $validTED and $validTN and $validTSD);

		// Data type / value checks failed.
		if (!$valid)
		{
			$model->errors[] = Languages::_('THM_ORGANIZER_TERM_INVALID');

			return false;
		}

		echo "<pre>" . print_r($term, true) . "</pre>";
		echo "<pre>" . print_r($model->schoolYear, true) . "</pre>";
		$endTimeStamp = strtotime($term->endDate);
		$invalidEnd   = $endTimeStamp > strtotime($model->schoolYear->endDate);

		$startTimeStamp = strtotime($term->startDate);
		$invalidStart   = $startTimeStamp < strtotime($model->schoolYear->startDate);

		$invalidPeriod = $startTimeStamp >= $endTimeStamp;
		$invalid       = ($invalidStart or $invalidEnd or $invalidPeriod);

		// Consistency among the dates failed.
		if ($invalid)
		{
			$model->errors[] = Languages::_('THM_ORGANIZER_TERM_INVALID');

			return false;
		}

		$model->term = $term;
		$code        = date('y', $term->endDate) . $term->code;

		self::setID($model, $code);
	}
}
