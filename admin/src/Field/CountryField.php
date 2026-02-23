<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Factory;

/**
 * Country Field class
 *
 * Provides a dropdown of countries from #__waterways_guide_country table
 */
class CountryField extends ListField
{
    /**
     * The form field type
     *
     * @var    string
     */
    protected $type = 'Country';

    /**
     * Method to get the field options
     *
     * @return  array  The field option objects
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName(['iso', 'printable_name']))
            ->from($db->quoteName('#__waterways_guide_country'))
            ->order($db->quoteName('printable_name'));

        $db->setQuery($query);
        $countries = $db->loadObjectList();

        foreach ($countries as $country) {
            $options[] = (object) [
                'value' => $country->iso,
                'text'  => $country->printable_name,
            ];
        }

        return $options;
    }
}
