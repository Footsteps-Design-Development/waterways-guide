<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Language\Text;

/**
 * Guides List Controller
 *
 * Handles list actions: publish, unpublish, archive, delete
 */
class GuidesController extends AdminController
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
    protected $text_prefix = 'COM_WATERWAYS_GUIDE_GUIDES';

    /**
     * Proxy for getModel
     *
     * @param   string  $name    The model name
     * @param   string  $prefix  The model prefix
     * @param   array   $config  Configuration array
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
     */
    public function getModel($name = 'Guide', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
