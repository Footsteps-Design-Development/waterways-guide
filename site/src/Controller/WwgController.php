<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

class WwgController extends BaseController
{
    public function generatepdf(): void
    {
        $pdfScript = JPATH_COMPONENT_SITE . '/tmpl/wwg/guides_list_to_pdf.php';

        if (!file_exists($pdfScript)) {
            throw new \RuntimeException('PDF generator script not found: ' . $pdfScript, 500);
        }

        require $pdfScript;
        $this->app->close();
    }

    public function generatekml(): void
    {
        $kmlScript = JPATH_COMPONENT_SITE . '/tmpl/wwg/guides_list_to_kml.php';

        if (!file_exists($kmlScript)) {
            throw new \RuntimeException('KML generator script not found: ' . $kmlScript, 500);
        }

        require $kmlScript;
        $this->app->close();
    }
}
