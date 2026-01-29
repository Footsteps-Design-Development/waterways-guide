<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_waterways_guide
 */

namespace Joomla\Component\WaterWaysGuide\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;



/**
 * Waterways Guide main controller
 */
class WwgController extends BaseController
{
    /**
     * Generate PDF task
     *
     * URL: index.php?option=com_waterways_guide&task=wwg.generatepdf
     */
    public function generatepdf()
    {
        $app = Factory::getApplication();
        $input = $app->input;

        // Path to your legacy generator script
        $pdfScript = JPATH_COMPONENT_SITE . '/tmpl/wwg/guides_list_to_pdf.php';

        if (!file_exists($pdfScript)) {
            throw new \Exception('PDF generator script not found: ' . $pdfScript, 500);
        }

        require $pdfScript;
        $app->close();
    }

    /**
     * Generate KML task
     *
     * URL: index.php?option=com_waterways_guide&task=wwg.generatekml
     */
    public function generatekml()
    {
        $app = Factory::getApplication();
        $input = $app->input;

        $kmlScript = JPATH_COMPONENT_SITE . '/tmpl/wwg/guides_list_to_kml.php';

        if (!file_exists($kmlScript)) {
            throw new \Exception('KML generator script not found: ' . $kmlScript, 500);
        }

        require $kmlScript;
        $app->close();
    }
}
