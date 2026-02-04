<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;

class WwgModel extends ListModel
{
    protected function populateState($ordering = null, $direction = null): void
    {
        $app = $this->getApplication();
        $context = $this->context;

        // Load the filter state.
        $search = $this->getUserStateFromRequest($context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        // Load the pagination state.
        $limit = $this->getUserStateFromRequest($context . '.list.limit', 'limit', $app->get('list_limit', 20), 'int');
        $this->setState('list.limit', $limit);
        $limitstart = $app->getInput()->get('limitstart', 0, 'uint');
        $this->setState('list.start', $limitstart);

        parent::populateState($ordering, $direction);
    }

    protected function getListQuery(): QueryInterface
    {
        $db = $this->getDatabase();
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

    public function getFilterForm($data = [], $loadData = true)
    {
        return $this->loadForm($this->context . '.filter', 'filter_wwg', ['control' => 'filter', 'load_data' => $loadData]);
    }
}
