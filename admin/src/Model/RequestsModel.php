<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;

/**
 * Requests List Model
 *
 * Handles listing pending mooring requests
 */
class RequestsModel extends ListModel
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
                'memberid', 'a.memberid',
                'GuideCountry', 'a.GuideCountry',
                'GuideWaterway', 'a.GuideWaterway',
                'GuideRequestDate', 'a.GuideRequestDate',
                'GuideRequestStatus', 'a.GuideRequestStatus',
                'country_name',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Build an SQL query to load the list data
     *
     * @return  QueryInterface
     */
    protected function getListQuery(): QueryInterface
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select([
            'a.*',
            'c.printable_name AS country_name',
        ])
            ->from($db->quoteName('#__waterways_guide_requests', 'a'))
            ->join('LEFT', $db->quoteName('#__waterways_guide_country', 'c') . ' ON c.iso = a.GuideCountry');

        // Filter by status
        $status = $this->getState('filter.status');
        if ($status !== '' && $status !== null) {
            $query->where($db->quoteName('a.GuideRequestStatus') . ' = ' . $db->quote($status));
        }

        // Filter by country
        $country = $this->getState('filter.country');
        if ($country) {
            $query->where($db->quoteName('a.GuideCountry') . ' = ' . $db->quote($country));
        }

        // Filter by search (waterway or member id)
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('a.memberid') . ' = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true)) . '%');
                $query->where('(' . $db->quoteName('a.GuideWaterway') . ' LIKE ' . $search . ')');
            }
        }

        // Ordering
        $orderCol  = $this->state->get('list.ordering', 'a.GuideRequestDate');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    /**
     * Method to auto-populate the model state
     *
     * @param   string  $ordering   Column for ordering
     * @param   string  $direction  Ordering direction
     *
     * @return  void
     */
    protected function populateState($ordering = 'a.GuideRequestDate', $direction = 'DESC'): void
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status', '');
        $this->setState('filter.status', $status);

        $country = $this->getUserStateFromRequest($this->context . '.filter.country', 'filter_country', '');
        $this->setState('filter.country', $country);

        parent::populateState($ordering, $direction);
    }

    /**
     * Get the filter form
     *
     * @param   array    $data      Data
     * @param   boolean  $loadData  Load current data
     *
     * @return  \Joomla\CMS\Form\Form|null
     */
    public function getFilterForm($data = [], $loadData = true)
    {
        return parent::getFilterForm($data, $loadData);
    }
}
