<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

\JLoader::registerNamespace('Joomla\\Component\\WaterWaysGuide', JPATH_COMPONENT . '/src', false, false, 'psr4');

$app = Factory::getApplication();
$controller = BaseController::getInstance('WaterWaysGuide');
$controller->execute($app->input->getCmd('task'));
$controller->redirect();
