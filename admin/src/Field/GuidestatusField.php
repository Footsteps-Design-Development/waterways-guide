<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

/**
 * Guide Status Field class
 *
 * Provides status options: 0=Pending, 1=Live, 2=Archived
 */
class GuidestatusField extends ListField
{
    /**
     * The form field type
     *
     * @var    string
     */
    protected $type = 'Guidestatus';

    /**
     * Method to get the field options
     *
     * @return  array  The field option objects
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();

        $options[] = (object) [
            'value' => '0',
            'text'  => Text::_('COM_WATERWAYS_GUIDE_STATUS_PENDING'),
        ];

        $options[] = (object) [
            'value' => '1',
            'text'  => Text::_('COM_WATERWAYS_GUIDE_STATUS_LIVE'),
        ];

        $options[] = (object) [
            'value' => '2',
            'text'  => Text::_('COM_WATERWAYS_GUIDE_STATUS_ARCHIVED'),
        ];

        return $options;
    }
}
