<?php

declare(strict_types=1);

namespace Joomla\Component\WaterWaysGuide\Site\View\Pdf;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\WaterWaysGuide\Site\Helper\WaterwaysHelper;
use TCPDF;

// Load TCPDF
if (!class_exists('TCPDF')) {
    $tcpdfPath = JPATH_LIBRARIES . '/vendor/tecnickcom/tcpdf/tcpdf.php';
    if (file_exists($tcpdfPath)) {
        require_once $tcpdfPath;
    }
}

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $inputValues = WaterwaysHelper::getPostIfSet([
            'waterway', 'guideaction', 'filteroption', 'GuideMooringCodes', 'GuideHazardCodes'
        ]);

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__waterways_guide'))
            ->where($db->quoteName('GuideStatus') . ' = 1')
            ->order($db->quoteName(['GuideCountry', 'GuideWaterway', 'GuideOrder']));

        $guides = $db->setQuery($query)->loadAssocList();

        if (!class_exists('TCPDF')) {
            throw new \RuntimeException("TCPDF library not found!");
        }

        // Set up TCPDF
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Waterways Guide');
        $pdf->SetTitle('Waterways Guide PDF Report');
        $pdf->SetSubject('PDF Report');
        $pdf->SetKeywords('PDF, Joomla, Waterways Guide');

        // Set header and footer
        $pdf->setHeaderData('', 0, 'Waterways Guide', 'Generated on ' . date('Y-m-d H:i:s'));
        $pdf->setFooterData();

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(true, 15);

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // Add a new page
        $pdf->AddPage();

        // Ensure content is not empty
        if (empty($guides)) {
            $pdf->Write(0, "No waterways guides available.", '', 0, 'C', true, 0, false, false, 0);
        } else {
            foreach ($guides as $row) {
                $pdf->SetFont('helvetica', 'B', 14);
                $pdf->Write(0, "Guide Name: " . stripslashes($row["GuideName"]));
                $pdf->Ln();
                $pdf->SetFont('helvetica', '', 12);
                $pdf->Write(0, "Location: " . stripslashes($row["GuideLocation"]));
                $pdf->Ln();
                $pdf->Write(0, "Lat/Long: " . stripslashes($row["GuideLat"]) . " , " . stripslashes($row["GuideLong"]));
                $pdf->Ln(10);
            }
        }

        // Send output to the browser
        $pdf->Output('waterways_report.pdf', 'D');
    }
}
