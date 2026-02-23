<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * Guide Form Controller
 *
 * Handles form actions: save, apply, save2new, save2copy, cancel
 */
class GuideController extends FormController
{
    /**
     * The prefix to use for controller messages
     *
     * @var    string
     */
    protected $text_prefix = 'COM_WATERWAYS_GUIDE_GUIDE';

    /**
     * Method to get the URL arguments to append to a redirect
     *
     * @param   integer  $recordId  The record id
     * @param   string   $urlVar    The name of the URL variable for the record id
     *
     * @return  string  The arguments to append to the redirect URL
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id'): string
    {
        return parent::getRedirectToItemAppend($recordId, 'GuideID');
    }

    /**
     * Method to get the URL arguments to append to a list redirect
     *
     * @return  string  The arguments to append to the redirect URL
     */
    protected function getRedirectToListAppend(): string
    {
        return '';
    }
}
