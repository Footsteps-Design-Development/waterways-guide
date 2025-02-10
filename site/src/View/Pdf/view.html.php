<?php

namespace Joomla\Component\WaterWaysGuide\Site\View\Pdf;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use TCPDF;

class HtmlView extends HtmlView
{
    public function display($tpl = null)
    {
        // Get the document object
        $app = Factory::getApplication();
        $input = $app->input;

        // Set PDF Headers
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Waterways Guide');
        $pdf->SetTitle('Waterways Guide PDF Report');
        $pdf->SetSubject('PDF Report');
        $pdf->SetKeywords('PDF, Joomla, Waterways Guide');

        // Add a Page
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        // Example Content
        $pdf->Write(0, 'This is an example PDF generated from Joomla!');

        // Output the PDF to the browser (force download)
        $pdf->Output('waterways_report.pdf', 'D'); // 'D' forces download

        return;
    }
}
