<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;

class WaterWaysGuideHelper
{
    /**
     * Configure the admin submenu
     *
     * @param   string  $vName  The current view name
     *
     * @return  void
     */
    public static function addSubmenu(string $vName): void
    {
        \JHtmlSidebar::addEntry(
            Text::_('COM_WATERWAYS_GUIDE_SUBMENU_GUIDES'),
            'index.php?option=com_waterways_guide&view=guides',
            $vName === 'guides'
        );

        \JHtmlSidebar::addEntry(
            Text::_('COM_WATERWAYS_GUIDE_SUBMENU_REQUESTS'),
            'index.php?option=com_waterways_guide&view=requests',
            $vName === 'requests'
        );

        \JHtmlSidebar::addEntry(
            Text::_('COM_WATERWAYS_GUIDE_SUBMENU_CHANGELOGS'),
            'index.php?option=com_waterways_guide&view=changelogs',
            $vName === 'changelogs'
        );
    }

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

        if ($actions) {
            foreach ($actions as $action) {
                $result->set($action->name, $user->authorise($action->name, $assetName));
            }
        }

        return $result;
    }
}
