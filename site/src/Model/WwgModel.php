<?php
// src/Model/WwgModel.php
namespace Waterwaysguide\Component\Waterways_guide\Site\Model;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;
use Joomla\CMS\Factory;

class WwgModel extends ListModel
{
    protected function populateState($ordering = null, $direction = null)
    {
        // Initialize pagination state
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
        // Get the database object and a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Build the query
        $query->select('*')
            ->from($db->quoteName('#__waterways_guides'));

        // Add ordering clause
        $orderCol = $this->getState('list.ordering', 'id');
        $orderDirn = $this->getState('list.direction', 'asc');
        $query->order($db->quoteName($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }
}
