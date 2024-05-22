<?php

namespace Joomla\Component\WaterWaysGuide\Administrator\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        // Set the default view name and format from the Request.
        $app = \Joomla\CMS\Factory::getApplication();
        $viewName = $app->input->getCmd('view', 'wwg');
        $viewFormat = $app->input->getCmd('format', 'html');
        $view = $this->getView($viewName, $viewFormat);
        
        // Ensure the view has the model
        $model = $this->getModel('Wwg');
        if (!$model) {
            $model = $this->createModel('Wwg', 'Joomla\Component\WaterWaysGuide\Administrator\Model');
        }
        $view->setModel($model, true);
        
        // Display the view
        $view->display();
    }
}
