<?php

namespace Joomla\Component\WaterWaysGuide\Administrator\Model;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

class WwgModel extends ListModel
{
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $context = $this->context;

        // Load the filter state.
        $search = $app->getUserStateFromRequest($context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        // Load the pagination state.
        $limit = $app->getUserStateFromRequest($context . '.list.limit', 'limit', $app->get('list_limit', 20), 'int');
        $this->setState('list.limit', $limit);
        $limitstart = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $limitstart);

        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select all columns from the #__waterways_guide table
        $query->select('*')
              ->from($db->quoteName('#__waterways_guide'));

        // Apply search filter
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = '%' . str_replace(' ', '%', $db->escape($search, true)) . '%';
            $query->where($db->quoteName('GuideName') . ' LIKE ' . $db->quote($search));
        }

        // Add the list ordering clause.
        $orderCol = $this->state->get('list.ordering', 'GuideID');
        $orderDirn = $this->state->get('list.direction', 'asc');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    public function getFilterForm($data = array(), $loadData = true)
    {
        return $this->loadForm($this->context . '.filter', 'filter_wwg', array('control' => 'filter', 'load_data' => $loadData));
    }
}
