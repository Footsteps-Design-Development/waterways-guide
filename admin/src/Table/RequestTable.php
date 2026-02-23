<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Request Table class
 *
 * Maps to #__waterways_guide_requests table
 * Note: This table uses a composite key (memberid, GuideCountry, GuideWaterway)
 */
class RequestTable extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database driver object
     */
    public function __construct(DatabaseDriver $db)
    {
        // Using memberid as primary key for simplicity in Joomla's Table class
        // The actual table uses a composite key
        parent::__construct('#__waterways_guide_requests', 'memberid', $db);
    }

    /**
     * Overloaded check function
     *
     * @return  boolean  True on success
     */
    public function check(): bool
    {
        if (empty($this->memberid)) {
            $this->setError('Member ID is required');
            return false;
        }

        return true;
    }
}
