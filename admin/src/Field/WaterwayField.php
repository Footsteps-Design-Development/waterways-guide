<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Factory;

/**
 * Waterway Field class
 *
 * Provides a dropdown of distinct waterways from guides table
 */
class WaterwayField extends ListField
{
    /**
     * The form field type
     *
     * @var    string
     */
    protected $type = 'Waterway';

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
            ->select('DISTINCT ' . $db->quoteName('GuideWaterway'))
            ->from($db->quoteName('#__waterways_guide'))
            ->where($db->quoteName('GuideWaterway') . ' IS NOT NULL')
            ->where($db->quoteName('GuideWaterway') . ' != ' . $db->quote(''))
            ->order($db->quoteName('GuideWaterway'));

        $db->setQuery($query);
        $waterways = $db->loadColumn();

        foreach ($waterways as $waterway) {
            $options[] = (object) [
                'value' => $waterway,
                'text'  => $waterway,
            ];
        }

        return $options;
    }
}
