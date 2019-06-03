<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Organizer\Fields;

defined('_JEXEC') or die;

use Organizer\Helpers\HTML;

/**
 * Provides a select list of integers with specified first, last and step values.
 */
class IntegerField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    protected $type = 'Integer';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $options = array();

        // Initialize some field attributes.
        $first = (int)$this->element['first'];
        $last  = (int)$this->element['last'];
        $step  = (int)$this->element['step'];

        // Sanity checks.
        if ($step == 0) {
            // Step of 0 will create an endless loop.
            return $options;
        } elseif ($first < $last && $step < 0) {
            // A negative step will never reach the last number.
            return $options;
        } elseif ($first > $last && $step > 0) {
            // A position step will never reach the last number.
            return $options;
        } elseif ($step < 0) {
            // Build the options array backwards.
            for ($i = $first; $i >= $last; $i += $step) {
                $options[] = HTML::_('select.option', $i);
            }
        } else {
            // Build the options array.
            for ($i = $first; $i <= $last; $i += $step) {
                $options[] = HTML::_('select.option', $i);
            }
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
