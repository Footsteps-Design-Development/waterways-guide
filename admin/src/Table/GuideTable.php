<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Factory;

/**
 * Guide Table class
 *
 * Maps to #__waterways_guide table
 */
class GuideTable extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database driver object
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__waterways_guide', 'GuideID', $db);
    }

    /**
     * Overloaded check function
     *
     * @return  boolean  True on success
     */
    public function check(): bool
    {
        // Ensure GuideName is set
        if (empty($this->GuideName)) {
            $this->setError('Guide name is required');
            return false;
        }

        // Set defaults for new records
        if (empty($this->GuideVer)) {
            $this->GuideVer = 1;
        }

        if (empty($this->GuideLat)) {
            $this->GuideLat = '51.1';
        }

        if (empty($this->GuideLong)) {
            $this->GuideLong = '2.2';
        }

        return true;
    }

    /**
     * Overloaded store function
     *
     * @param   boolean  $updateNulls  True to update null values
     *
     * @return  boolean  True on success
     */
    public function store($updateNulls = true): bool
    {
        // Set the update date
        $this->GuideUpdate = Factory::getDate()->toSql();

        // Set posting date if new
        if (empty($this->GuideID) && empty($this->GuidePostingDate)) {
            $this->GuidePostingDate = Factory::getDate()->toSql();
        }

        return parent::store($updateNulls);
    }
}
