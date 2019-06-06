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
use Organizer\Helpers\OrganizerHelper;
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
        $function = OrganizerHelper::getInput()->getString('task');
        if ($function === 'getLessons') {
            $parameters = $this->getLessonParameters();
            echo json_encode(SchedulesHelper::getLessons($parameters));
        } else {
            echo json_encode(SchedulesHelper::$function());
        }
    }

    /**
     * Gets the parameters necessary to retrieve lessons.
     *
     * @return array the lesson parameters
     */
    private function getLessonParameters()
    {

        $input       = OrganizerHelper::getInput();
        $inputParams = $input->getArray();
        $inputKeys   = array_keys($inputParams);
        $parameters  = [];
        foreach ($inputKeys as $key) {
            if (preg_match('/\w+IDs/', $key)) {
                $parameters[$key] = explode(',', $inputParams[$key]);
            }
        }

        $parameters['userID']          = Factory::getUser()->id;
        $parameters['mySchedule']      = $input->getBool('mySchedule', false);
        $parameters['date']            = $input->getString('date', date('Y-m-d', time()));
        $parameters['dateRestriction'] = $input->getString('dateRestriction');

        if (empty($parameters['dateRestriction'])) {
            $oneDay                        = $input->getBool('oneDay', false);
            $parameters['dateRestriction'] = $oneDay ? 'day' : 'week';
        }

        $parameters['format'] = '';
        $deltaDays            = $input->getString('deltaDays', '14');
        $parameters['delta']  = empty($deltaDays) ? '' : date('Y-m-d', strtotime('-' . $deltaDays . ' days'));

        return $parameters;
    }
}
