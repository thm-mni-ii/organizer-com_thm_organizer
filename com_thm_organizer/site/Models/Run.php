<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

use Exception;
use Organizer\Helpers\Access;
use Organizer\Helpers\Input;

/**
 * Class which manages stored run data.
 */
class Run extends BaseModel
{
    /**
     * Attempts to save the resource.
     *
     * @param array $data form data which has been preprocessed by inheriting classes.
     *
     * @return mixed int id of the resource on success, otherwise boolean false
     * @throws Exception => unauthorized access
     */
    public function save($data = [])
    {
        if (!Access::isAdmin()) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $data = empty($data) ? Input::getFormItems()->toArray() : $data;

        $runs = [];
        $index = 1;
        foreach ($data['run'] as $row) {
            $runs[$index] = $row;
            ++$index;
        }

        $run = ['runs' => $runs];
        $data['run'] = json_encode($run, JSON_UNESCAPED_UNICODE);

        $table = $this->getTable();
        $success = $table->save($data);

        return $success ? $table->id : false;
    }
}
