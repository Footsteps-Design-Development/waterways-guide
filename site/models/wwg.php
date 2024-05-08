<?php 

// site/models/wwg.php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;
use Joomla\CMS\Factory;

class WaterwaysGuideModelWwg extends ListModel
{
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $context = $this->context . '.';

        $limit = $app->getUserStateFromRequest($context . 'list.limit', 'limit', $app->get('list_limit', 20), 'uint');
        $this->setState('list.limit', $limit);

        $limitstart = $app->input->getUint('limitstart', 0);
        $this->setState('list.start', $limitstart);

        parent::populateState($ordering, $direction);
    }

    protected function getListQuery(): DatabaseQuery
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__waterways_guide'));

        $orderCol = $this->getState('list.ordering', 'GuideID');
        $orderDirn = $this->getState('list.direction', 'asc');
        $query->order($db->quoteName($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
