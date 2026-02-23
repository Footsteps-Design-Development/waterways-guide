<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Factory;

/**
 * Changelog Table class
 *
 * Maps to #__waterways_guide_changelog table
 */
class ChangelogTable extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database driver object
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__waterways_guide_changelog', 'LogID', $db);
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
        // Set the change date if not set
        if (empty($this->ChangeDate)) {
            $this->ChangeDate = Factory::getDate()->toSql();
        }

        return parent::store($updateNulls);
    }

    /**
     * Static helper to log a change
     *
     * @param   string  $subject     The subject (e.g., "Guides")
     * @param   string  $changeDesc  Description of the change
     * @param   int     $memberId    Member ID who made the change
     * @param   string  $username    Username who made the change
     *
     * @return  boolean  True on success
     */
    public static function logChange(string $subject, string $changeDesc, int $memberId = 0, string $username = ''): bool
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $table = new self($db);

        $table->Subject = $subject;
        $table->ChangeDesc = $changeDesc;
        $table->MemberID = $memberId;
        $table->User = $username ?: Factory::getApplication()->getIdentity()->username;
        $table->ChangeDate = Factory::getDate()->toSql();

        return $table->store();
    }
}
