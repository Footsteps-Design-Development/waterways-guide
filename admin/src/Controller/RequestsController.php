<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\Component\WaterWaysGuide\Administrator\Table\ChangelogTable;

/**
 * Requests List Controller
 *
 * Handles approve, reject, and delete actions for mooring requests
 */
class RequestsController extends AdminController
{
    /**
     * The component option
     *
     * @var    string
     */
    protected $option = 'com_waterways_guide';

    /**
     * The prefix to use for controller messages
     *
     * @var    string
     */
    protected $text_prefix = 'COM_WATERWAYS_GUIDE_REQUESTS';

    /**
     * Proxy for getModel
     *
     * @param   string  $name    The model name
     * @param   string  $prefix  The model prefix
     * @param   array   $config  Configuration array
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
     */
    public function getModel($name = 'Request', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Approve selected requests
     *
     * @return  void
     */
    public function approve(): void
    {
        $this->checkToken();

        $app   = Factory::getApplication();
        $pks   = (array) $app->getInput()->get('cid', [], 'array');

        if (empty($pks)) {
            $app->enqueueMessage(Text::_('JGLOBAL_NO_ITEM_SELECTED'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_waterways_guide&view=requests', false));
            return;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        try {
            foreach ($pks as $pk) {
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__waterways_guide_requests'))
                    ->set($db->quoteName('GuideRequestStatus') . ' = ' . $db->quote('approved'))
                    ->where($db->quoteName('memberid') . ' = ' . (int) $pk);
                $db->setQuery($query)->execute();
            }

            // Log the change
            $user = $app->getIdentity();
            ChangelogTable::logChange(
                'Requests',
                'Approved ' . count($pks) . ' request(s)',
                (int) $user->id,
                $user->username
            );

            $app->enqueueMessage(Text::plural('COM_WATERWAYS_GUIDE_N_REQUESTS_APPROVED', count($pks)), 'success');
        } catch (\Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_waterways_guide&view=requests', false));
    }

    /**
     * Reject selected requests
     *
     * @return  void
     */
    public function reject(): void
    {
        $this->checkToken();

        $app   = Factory::getApplication();
        $pks   = (array) $app->getInput()->get('cid', [], 'array');

        if (empty($pks)) {
            $app->enqueueMessage(Text::_('JGLOBAL_NO_ITEM_SELECTED'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_waterways_guide&view=requests', false));
            return;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        try {
            foreach ($pks as $pk) {
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__waterways_guide_requests'))
                    ->set($db->quoteName('GuideRequestStatus') . ' = ' . $db->quote('rejected'))
                    ->where($db->quoteName('memberid') . ' = ' . (int) $pk);
                $db->setQuery($query)->execute();
            }

            // Log the change
            $user = $app->getIdentity();
            ChangelogTable::logChange(
                'Requests',
                'Rejected ' . count($pks) . ' request(s)',
                (int) $user->id,
                $user->username
            );

            $app->enqueueMessage(Text::plural('COM_WATERWAYS_GUIDE_N_REQUESTS_REJECTED', count($pks)), 'success');
        } catch (\Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_waterways_guide&view=requests', false));
    }

    /**
     * Delete selected requests
     *
     * @return  void
     */
    public function delete(): void
    {
        $this->checkToken();

        $app   = Factory::getApplication();
        $pks   = (array) $app->getInput()->get('cid', [], 'array');

        if (empty($pks)) {
            $app->enqueueMessage(Text::_('JGLOBAL_NO_ITEM_SELECTED'), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_waterways_guide&view=requests', false));
            return;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        try {
            foreach ($pks as $pk) {
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__waterways_guide_requests'))
                    ->where($db->quoteName('memberid') . ' = ' . (int) $pk);
                $db->setQuery($query)->execute();
            }

            // Log the change
            $user = $app->getIdentity();
            ChangelogTable::logChange(
                'Requests',
                'Deleted ' . count($pks) . ' request(s)',
                (int) $user->id,
                $user->username
            );

            $app->enqueueMessage(Text::plural('COM_WATERWAYS_GUIDE_N_REQUESTS_DELETED', count($pks)), 'success');
        } catch (\Exception $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
        }

        $this->setRedirect(Route::_('index.php?option=com_waterways_guide&view=requests', false));
    }
}
