<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;

/**
 * Guides List Model
 *
 * Provides list functionality for the guides backend
 */
class GuidesModel extends ListModel
{
    /**
     * Constructor
     *
     * @param   array  $config  Configuration array
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'GuideID', 'a.GuideID',
                'GuideName', 'a.GuideName',
                'GuideCountry', 'a.GuideCountry',
                'GuideWaterway', 'a.GuideWaterway',
                'GuideStatus', 'a.GuideStatus',
                'GuideUpdate', 'a.GuideUpdate',
                'GuideCategory', 'a.GuideCategory',
                'GuideRating', 'a.GuideRating',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Build the query for the list
     *
     * @return  QueryInterface
     */
    protected function getListQuery(): QueryInterface
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select all fields from the main table
        $query->select('a.*')
            ->from($db->quoteName('#__waterways_guide', 'a'));

        // Join with country table for display name
        $query->select($db->quoteName('c.printable_name', 'country_name'))
            ->leftJoin(
                $db->quoteName('#__waterways_guide_country', 'c') .
                ' ON ' . $db->quoteName('a.GuideCountry') . ' = ' . $db->quoteName('c.iso')
            );

        // Filter by search
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = '%' . str_replace(' ', '%', $db->escape(trim($search), true)) . '%';
            $query->where(
                '(' . $db->quoteName('a.GuideName') . ' LIKE ' . $db->quote($search) .
                ' OR ' . $db->quoteName('a.GuideWaterway') . ' LIKE ' . $db->quote($search) .
                ' OR ' . $db->quoteName('a.GuideRef') . ' LIKE ' . $db->quote($search) . ')'
            );
        }

        // Filter by status
        $status = $this->getState('filter.GuideStatus');
        if (is_numeric($status)) {
            $query->where($db->quoteName('a.GuideStatus') . ' = ' . (int) $status);
        }

        // Filter by country
        $country = $this->getState('filter.GuideCountry');
        if (!empty($country)) {
            $query->where($db->quoteName('a.GuideCountry') . ' = ' . $db->quote($country));
        }

        // Filter by waterway
        $waterway = $this->getState('filter.GuideWaterway');
        if (!empty($waterway)) {
            $query->where($db->quoteName('a.GuideWaterway') . ' = ' . $db->quote($waterway));
        }

        // Filter by category
        $category = $this->getState('filter.GuideCategory');
        if (!empty($category)) {
            $query->where($db->quoteName('a.GuideCategory') . ' = ' . $db->quote($category));
        }

        // Add ordering
        $orderCol = $this->state->get('list.ordering', 'a.GuideID');
        $orderDir = $this->state->get('list.direction', 'DESC');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDir));

        return $query;
    }

    /**
     * Method to auto-populate the model state
     *
     * @param   string  $ordering   An optional ordering field
     * @param   string  $direction  An optional direction (asc|desc)
     *
     * @return  void
     */
    protected function populateState($ordering = 'a.GuideID', $direction = 'DESC'): void
    {
        // Load filter states
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);

        $status = $this->getUserStateFromRequest($this->context . '.filter.GuideStatus', 'filter_GuideStatus', '', 'string');
        $this->setState('filter.GuideStatus', $status);

        $country = $this->getUserStateFromRequest($this->context . '.filter.GuideCountry', 'filter_GuideCountry', '', 'string');
        $this->setState('filter.GuideCountry', $country);

        $waterway = $this->getUserStateFromRequest($this->context . '.filter.GuideWaterway', 'filter_GuideWaterway', '', 'string');
        $this->setState('filter.GuideWaterway', $waterway);

        $category = $this->getUserStateFromRequest($this->context . '.filter.GuideCategory', 'filter_GuideCategory', '', 'string');
        $this->setState('filter.GuideCategory', $category);

        parent::populateState($ordering, $direction);
    }

    /**
     * Get the filter form
     *
     * @param   array    $data      Data for the form
     * @param   boolean  $loadData  Load current data
     *
     * @return  \Joomla\CMS\Form\Form|null
     */
    public function getFilterForm($data = [], $loadData = true)
    {
        return $this->loadForm(
            $this->context . '.filter',
            'filter_guides',
            ['control' => 'filter', 'load_data' => $loadData]
        );
    }

    /**
     * Method to get a store id based on model configuration state
     *
     * @param   string  $id  A prefix for the store id
     *
     * @return  string  A store id
     */
    protected function getStoreId($id = ''): string
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.GuideStatus');
        $id .= ':' . $this->getState('filter.GuideCountry');
        $id .= ':' . $this->getState('filter.GuideWaterway');
        $id .= ':' . $this->getState('filter.GuideCategory');

        return parent::getStoreId($id);
    }
}
