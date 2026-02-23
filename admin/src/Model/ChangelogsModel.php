<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;

/**
 * Changelogs List Model
 *
 * Handles listing change log entries (read-only audit log)
 */
class ChangelogsModel extends ListModel
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
                'LogID', 'a.LogID',
                'User', 'a.User',
                'MemberID', 'a.MemberID',
                'Subject', 'a.Subject',
                'ChangeDesc', 'a.ChangeDesc',
                'ChangeDate', 'a.ChangeDate',
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

        $query->select('a.*')
            ->from($db->quoteName('#__waterways_guide_changelog', 'a'));

        // Filter by subject
        $subject = $this->getState('filter.subject');
        if ($subject) {
            $query->where($db->quoteName('a.Subject') . ' = ' . $db->quote($subject));
        }

        // Filter by date range
        $dateFrom = $this->getState('filter.date_from');
        if ($dateFrom) {
            $query->where($db->quoteName('a.ChangeDate') . ' >= ' . $db->quote($dateFrom . ' 00:00:00'));
        }

        $dateTo = $this->getState('filter.date_to');
        if ($dateTo) {
            $query->where($db->quoteName('a.ChangeDate') . ' <= ' . $db->quote($dateTo . ' 23:59:59'));
        }

        // Filter by search (username or description)
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('a.MemberID') . ' = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true)) . '%');
                $query->where('(' . $db->quoteName('a.User') . ' LIKE ' . $search .
                    ' OR ' . $db->quoteName('a.ChangeDesc') . ' LIKE ' . $search . ')');
            }
        }

        // Ordering
        $orderCol  = $this->state->get('list.ordering', 'a.ChangeDate');
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
    protected function populateState($ordering = 'a.ChangeDate', $direction = 'DESC'): void
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $subject = $this->getUserStateFromRequest($this->context . '.filter.subject', 'filter_subject', '');
        $this->setState('filter.subject', $subject);

        $dateFrom = $this->getUserStateFromRequest($this->context . '.filter.date_from', 'filter_date_from', '');
        $this->setState('filter.date_from', $dateFrom);

        $dateTo = $this->getUserStateFromRequest($this->context . '.filter.date_to', 'filter_date_to', '');
        $this->setState('filter.date_to', $dateTo);

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
