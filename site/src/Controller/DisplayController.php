<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\WaterWaysGuide\Site\Helper\WaterwaysHelper;

class DisplayController extends BaseController
{
    protected $default_view = 'wwg';

    public function display($cachable = false, $urlparams = []): static
    {
        return parent::display($cachable, $urlparams);
    }

    public function generatePdf(): void
    {
        $inputValues = WaterwaysHelper::getPostIfSet([
            'waterway', 'guideaction', 'filteroption', 'GuideMooringCodes', 'GuideHazardCodes'
        ]);

        $view = $this->getView('pdf', 'html');

        if (!$view) {
            throw new \RuntimeException("PDF view not found!");
        }

        $view->set('inputValues', $inputValues);
        $view->display();
    }

    public function generateKml(): void
    {
        $inputValues = WaterwaysHelper::getPostIfSet([
            'waterway', 'guideaction', 'filteroption', 'GuideMooringCodes', 'GuideHazardCodes'
        ]);

        $view = $this->getView('kml', 'html');

        if (!$view) {
            throw new \RuntimeException("KML view not found!");
        }

        $view->set('inputValues', $inputValues);
        $view->display();
    }
}
