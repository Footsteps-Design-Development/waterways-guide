<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\Component\WaterWaysGuide\Administrator\Table\ChangelogTable;

/**
 * Guide Admin Model
 *
 * Handles single guide item for editing
 */
class GuideModel extends AdminModel
{
    /**
     * The prefix to use with controller messages
     *
     * @var    string
     */
    protected $text_prefix = 'COM_WATERWAYS_GUIDE';

    /**
     * Method to get the table object
     *
     * @param   string  $name     Table name
     * @param   string  $prefix   Table prefix
     * @param   array   $options  Configuration array
     *
     * @return  Table
     */
    public function getTable($name = 'Guide', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to get the form
     *
     * @param   array    $data      Data for the form
     * @param   boolean  $loadData  True if the form is to load its own data
     *
     * @return  \Joomla\CMS\Form\Form|boolean
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_waterways_guide.guide',
            'guide',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form
     *
     * @return  mixed  The data for the form
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data
        $app = Factory::getApplication();
        $data = $app->getUserState('com_waterways_guide.edit.guide.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Method to save the form data
     *
     * @param   array  $data  The form data
     *
     * @return  boolean  True on success
     */
    public function save($data): bool
    {
        $isNew = empty($data['GuideID']);
        $oldData = null;

        // Get old data for changelog if editing
        if (!$isNew) {
            $oldItem = $this->getItem($data['GuideID']);
            $oldData = $oldItem ? (array) $oldItem : null;
        }

        // Save the record
        $result = parent::save($data);

        if ($result) {
            // Log the change
            $user = Factory::getApplication()->getIdentity();
            $changeDesc = $isNew
                ? 'Created new guide: ' . ($data['GuideName'] ?? 'Unknown')
                : 'Updated guide: ' . ($data['GuideName'] ?? 'Unknown');

            ChangelogTable::logChange(
                'Guides',
                $changeDesc,
                (int) $user->id,
                $user->username
            );
        }

        return $result;
    }

    /**
     * Prepare and sanitise the table data prior to saving
     *
     * @param   Table  $table  A reference to a Table object
     *
     * @return  void
     */
    protected function prepareTable($table): void
    {
        // Increment version on update
        if (!empty($table->GuideID)) {
            $table->GuideVer = (int) $table->GuideVer + 1;
        }

        // Set editor member number
        $user = Factory::getApplication()->getIdentity();
        $table->GuideEditorMemNo = (string) $user->id;
    }

    /**
     * Method to delete one or more records
     *
     * @param   array  &$pks  An array of record primary keys
     *
     * @return  boolean  True if successful
     */
    public function delete(&$pks): bool
    {
        $result = parent::delete($pks);

        if ($result) {
            $user = Factory::getApplication()->getIdentity();
            $count = count($pks);

            ChangelogTable::logChange(
                'Guides',
                "Deleted {$count} guide(s)",
                (int) $user->id,
                $user->username
            );
        }

        return $result;
    }

    /**
     * Method to change the published state of one or more records
     *
     * @param   array    &$pks   A list of the primary keys to change
     * @param   integer  $value  The value of the published state
     *
     * @return  boolean  True on success
     */
    public function publish(&$pks, $value = 1): bool
    {
        $result = parent::publish($pks, $value);

        if ($result) {
            $user = Factory::getApplication()->getIdentity();
            $count = count($pks);
            $action = $value == 1 ? 'Published' : ($value == 0 ? 'Unpublished' : 'Archived');

            ChangelogTable::logChange(
                'Guides',
                "{$action} {$count} guide(s)",
                (int) $user->id,
                $user->username
            );
        }

        return $result;
    }
}
