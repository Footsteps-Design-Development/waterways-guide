<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;

class WaterWaysGuideHelper
{
    public static function getActions(int $categoryId = 0): CMSObject
    {
        $user = Factory::getApplication()->getIdentity();
        $result = new CMSObject();

        if (empty($categoryId)) {
            $assetName = 'com_waterways_guide';
            $level = 'component';
        } else {
            $assetName = 'com_waterways_guide.category.' . $categoryId;
            $level = 'category';
        }

        $actions = Access::getActionsFromFile(
            JPATH_ADMINISTRATOR . '/components/com_waterways_guide/access.xml',
            "/access/section[@name='{$level}']/"
        );

        foreach ($actions as $action) {
            $result->set($action->name, $user->authorise($action->name, $assetName));
        }

        return $result;
    }
}
