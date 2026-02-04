<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

class WaterwaysHelper
{
    /**
     * Retrieves GET/POST request variables safely using Joomla Input.
     */
    public static function getPostIfSet(array $testVars): array
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $values = [];

        foreach ($testVars as $testVar) {
            $values[$testVar] = $input->get($testVar, '', 'STRING');
        }

        return $values;
    }

    /**
     * Converts decimal GPS coordinates to degrees format.
     */
    public static function decimalToDegree($decimalCoord, string $latOrLon = ""): string
    {
        if ($decimalCoord === '' || $decimalCoord === null) {
            return 'N/A';
        }

        $decimalCoord = (float) $decimalCoord;
        $degrees = abs(intval($decimalCoord)) . '&deg;' . (($decimalCoord * 60) % 60) . '&apos;' . number_format(fmod($decimalCoord * 3600, 60), 2) . '"';

        if ($latOrLon === 'LAT') {
            return $degrees . ($decimalCoord < 0 ? 'S' : 'N');
        }

        if ($latOrLon === 'LON') {
            return $degrees . ($decimalCoord < 0 ? 'W' : 'E');
        }

        return 'N/A';
    }

    /**
     * Splits a date into Year, Month, Day.
     */
    public static function splitDate(string $dt): array
    {
        $elements = explode(" ", $dt);
        return explode("-", $elements[0]);
    }

    /**
     * Converts a date to a human-readable format.
     */
    public static function dateToHuman(string $dt): array
    {
        return self::splitDate($dt);
    }

    /**
     * Formats a date according to predefined formats.
     */
    public static function dateToFormat(string $dt, string $format): string
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

    /**
     * Unescapes coded strings (legacy function from commonV3.php)
     */
    public static function unEscape(string $codedString): string
    {
        $search = ["#", "~", "^", "&", "£"];
        $replace = ["\"", ",", "<br>", "&amp;", "&pound;"];
        return str_replace($search, $replace, $codedString);
    }

    /**
     * Gets a component parameter value
     */
    public static function getParam(string $key, $default = null)
    {
        $params = ComponentHelper::getParams('com_waterways_guide');
        return $params->get($key, $default);
    }

    /**
     * Gets all component parameters
     */
    public static function getParams(): Registry
    {
        return ComponentHelper::getParams('com_waterways_guide');
    }

    /**
     * Gets the database driver
     */
    public static function getDatabase()
    {
        return Factory::getContainer()->get('DatabaseDriver');
    }

    /**
     * Gets the current user
     */
    public static function getUser()
    {
        return Factory::getApplication()->getIdentity();
    }

    /**
     * Register a variable from request (replacement for legacy register function)
     */
    public static function register(string $varname, $default = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();

        return $input->get($varname, $default, 'RAW');
    }
}
