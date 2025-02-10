<?php

namespace Joomla\Component\WaterWaysGuide\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\Component\WaterWaysGuide\Site\Helper\WaterwaysHelper;

// Prevent direct access
defined('_JEXEC') or die;

class DisplayController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        return parent::display($cachable, $urlparams);
    }

    /**
     * Generates the PDF file.
     */
    public function generatePdf()
    {
        $app = Factory::getApplication();
        $inputValues = WaterwaysHelper::getPostIfSet([
            'waterway', 'guideaction', 'filteroption', 'GuideMooringCodes', 'GuideHazardCodes'
        ]);

        $view = $this->getView('pdf', 'html', 'site'); // Ensure 'pdf' matches the view folder
        $view->assign('inputValues', $inputValues);
        $view->display();
    }

    /**
     * Generates the KML file.
     */
    public function generateKml()
    {
        $app = Factory::getApplication();
        $inputValues = WaterwaysHelper::getPostIfSet([
            'waterway', 'guideaction', 'filteroption', 'GuideMooringCodes', 'GuideHazardCodes'
        ]);

        $view = $this->getView('kml', 'html', 'site'); // Ensure 'kml' matches the view folder
        $view->assign('inputValues', $inputValues);
        $view->display();
    }
}
