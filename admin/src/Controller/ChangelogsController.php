<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Changelogs List Controller
 *
 * Read-only controller for change log entries
 */
class ChangelogsController extends AdminController
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
    protected $text_prefix = 'COM_WATERWAYS_GUIDE_CHANGELOGS';

    /**
     * Proxy for getModel
     *
     * @param   string  $name    The model name
     * @param   string  $prefix  The model prefix
     * @param   array   $config  Configuration array
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
     */
    public function getModel($name = 'Changelogs', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
