<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_waterways_guide
 *
 * @copyright   Copyright (C) 2025
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Waterways_guide\Site\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Waterways Guide Controller
 */
class WwgController extends BaseController
{
    /**
     * Generate PDF version of the Waterways Guide.
     *
     * Route: index.php?option=com_waterways_guide&task=wwg.generatepdf
     *
     * @return  void
     */
    public function generatepdf(): void
    {
        $app = Factory::getApplication();

        // Path to the legacy PDF generator file
        $pdfFile = JPATH_COMPONENT_SITE . '/pdf/guides_list_to_pdf.php';

        if (!is_file($pdfFile)) {
            throw new \RuntimeException('PDF generator file not found: ' . $pdfFile, 404);
        }

        // Execute the generator (streams PDF to browser)
        require_once $pdfFile;

        // Stop Joomla from rendering further output
        $app->close();
    }
}
