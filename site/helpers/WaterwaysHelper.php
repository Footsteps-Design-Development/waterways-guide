<?php

namespace Joomla\Component\WaterWaysGuide\Site\Helper;

use Joomla\CMS\Factory;

// Prevent direct access
defined('_JEXEC') or die;

class WaterwaysHelper
{
    /**
     * Retrieves GET/POST request variables safely using Joomla Input.
     */
    public static function getPostIfSet(array $testVars)
    {
        $app = Factory::getApplication();
        $input = $app->input;
        $values = [];

        foreach ($testVars as $testVar) {
            $values[$testVar] = $input->get($testVar, '', 'STRING');
        }

        return $values;
    }

    /**
     * Converts decimal GPS coordinates to degrees format.
     */
    public static function decimalToDegree($decimalCoord, $latOrLon = "")
    {
        $degrees = abs(intval($decimalCoord)) . '&deg;' . (($decimalCoord * 60) % 60) . '&apos;' . number_format(fmod($decimalCoord * 3600, 60), 2) . '"';

        if ($latOrLon == 'LAT') return $degrees . ($decimalCoord < 0 ? 'S' : 'N');
        elseif ($latOrLon == 'LON') return $degrees . ($decimalCoord < 0 ? 'W' : 'E');
        else return 'N/A';
    }

    /**
     * Splits a date into Year, Month, Day.
     */
    public static function splitDate($dt)
    {
        $elements = explode(" ", $dt);
        return explode("-", $elements[0]);
    }

    /**
     * Converts a date to a human-readable format.
     */
    public static function dateToHuman($dt)
    {
        return self::splitDate($dt);
    }

    /**
     * Formats a date according to predefined formats.
     */
    public static function dateToFormat($dt, $format)
    {
        $dateEnum = [
            'dt'  => 'jS F, Y - h:ma',
            'ymd' => 'Y-m-d',
            'dmy' => 'd-m-Y'
        ];

        if (isset($dateEnum[$format])) {
            return date($dateEnum[$format], strtotime($dt));
        }
        return date("jS F, Y", strtotime($dt));
    }
}
