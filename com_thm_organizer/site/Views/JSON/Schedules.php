<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\JSON;

use Exception;
use Joomla\CMS\Factory;
use Organizer\Helpers\Dates;
use Organizer\Helpers\Input;
use Organizer\Helpers\Schedules as SchedulesHelper;

/**
 * Class answers dynamic schedule related queries
 */
class Schedules extends BaseView
{
    /**
     * loads model data into view context
     *
     * @return void
     * @throws Exception
     */
    public function display()
    {
        $function = Input::getTask();
        if (method_exists('Organizer\\Helpers\\Schedules', $function)) {
            if ($function === 'getLessons') {
                $parameters = $this->getLessonParameters();
                echo json_encode(SchedulesHelper::getLessons($parameters));
            } else {
                echo json_encode(SchedulesHelper::$function());
            }
        } else {
            echo false;
        }
    }

    /**
     * Gets the parameters necessary to retrieve lessons.
     *
     * @return array the lesson parameters
     */
    private function getLessonParameters()
    {
        $inputParams = Input::getInput()->getArray();
        $inputKeys   = array_keys($inputParams);
        $parameters  = [];
        foreach ($inputKeys as $key) {
            if (preg_match('/\w+IDs/', $key)) {
                $parameters[$key] = explode(',', $inputParams[$key]);
            }
        }

        $parameters['userID']     = Factory::getUser()->id;
        $parameters['mySchedule'] = Input::getBool('mySchedule', false);
        $parameters['date']       = Dates::standardizeDate(Input::getCMD('date'));
        $parameters['interval']   = Input::getCMD('interval');

        $parameters['format'] = '';
        $deltaDays            = Input::getInt('deltaDays', 14);
        $parameters['delta']  = empty($deltaDays) ? false : date('Y-m-d', strtotime('-' . $deltaDays . ' days'));

        return $parameters;
    }
}
