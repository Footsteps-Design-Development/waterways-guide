<?php

namespace Joomla\Component\WaterWaysGuide\Site\View\Pdf;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\Component\WaterWaysGuide\Site\Helper\WaterwaysHelper;

// ✅ Ensure TCPDF is loaded
require_once JPATH_LIBRARIES . '/vendor/tecnickcom/tcpdf/tcpdf.php';

use \TCPDF; // ✅ Ensure TCPDF is referenced in the global namespace

// Prevent direct access
defined('_JEXEC') or die;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $db = Factory::getDbo();

        $inputValues = WaterwaysHelper::getPostIfSet([
            'waterway', 'guideaction', 'filteroption', 'GuideMooringCodes', 'GuideHazardCodes'
        ]);

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->qn('#__waterways_guide'))
            ->where($db->qn('GuideStatus') . ' = 1')
            ->order($db->qn(['GuideCountry', 'GuideWaterway', 'GuideOrder']));

        $guides = $db->setQuery($query)->loadAssocList();

        // ✅ Ensure TCPDF exists
        if (!class_exists('\TCPDF')) {
            throw new \Exception("TCPDF library not found!");
        }

        // ✅ Set Up TCPDF
        $pdf = new \TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Waterways Guide');
        $pdf->SetTitle('Waterways Guide PDF Report');
        $pdf->SetSubject('PDF Report');
        $pdf->SetKeywords('PDF, Joomla, Waterways Guide');

        // ✅ Set header and footer
        $pdf->setHeaderData('', 0, 'Waterways Guide', 'Generated on ' . date('Y-m-d H:i:s'));
        $pdf->setFooterData();

        // ✅ Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 15);

        // ✅ Set font
        $pdf->SetFont('helvetica', '', 12);

        // ✅ Add a new page
        $pdf->AddPage();

        // ✅ Ensure content is not empty
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

        // ✅ Send output to the browser
        $pdf->Output('waterways_report.pdf', 'D'); // 'D' forces download
    }
}
