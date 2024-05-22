<?php

namespace Joomla\Component\WaterWaysGuide\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Access\Access;

class WaterWaysGuideHelper
{
    public static function addSubmenu($vName = '')
    {
        JHtmlSidebar::addEntry(
            Text::_('COM_WATERWAYS_GUIDE_TITLE_GUIDES'),
            'index.php?option=com_waterways_guide&view=wwg',
            $vName == 'wwg'
        );
        // Add other submenu entries as needed
    }

    public static function getActions($categoryId = 0)
    {
        $user = Factory::getUser();
        $result = new \Joomla\CMS\Object\CMSObject;

        if (empty($categoryId)) {
            $assetName = 'com_waterways_guide';
            $level = 'component';
        } else {
            $assetName = 'com_waterways_guide.category.' . (int) $categoryId;
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
