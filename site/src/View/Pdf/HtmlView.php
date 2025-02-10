<?php

namespace Joomla\Component\WaterWaysGuide\Site\View\Pdf;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\Component\WaterWaysGuide\Site\Helper\WaterwaysHelper;

// ✅ Ensure TCPDF is loaded correctly
require_once JPATH_LIBRARIES . '/vendor/tecnickcom/tcpdf/tcpdf.php';

use \TCPDF; // ✅ This ensures TCPDF is not loaded under Joomla's namespace

// Prevent direct access
defined('_JEXEC') or die;

class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $inputValues = WaterwaysHelper::getPostIfSet([
            'waterway', 'guideaction', 'filteroption', 'GuideMooringCodes', 'GuideHazardCodes'
        ]);

        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->qn('#__waterways_guide'))
            ->where($db->qn('GuideStatus') . ' = 1')
            ->order($db->qn(['GuideCountry', 'GuideWaterway', 'GuideOrder']));

        $guides = $db->setQuery($query)->loadAssocList();

        // ✅ Ensure TCPDF is correctly referenced as \TCPDF
        if (!class_exists('\TCPDF')) {
            throw new \Exception("TCPDF library not found!");
        }

        $pdf = new \TCPDF(); // ✅ Correctly referenced with global namespace
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Waterways Guide');
        $pdf->SetTitle('Waterways Guide PDF Report');
        $pdf->SetSubject('PDF Report');
        $pdf->SetKeywords('PDF, Joomla, Waterways Guide');
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);

        foreach ($guides as $row) {
            $pdf->Write(0, "Guide Name: " . stripslashes($row["GuideName"]));
            $pdf->Ln();
            $pdf->Write(0, "Location: " . stripslashes($row["GuideLocation"]));
            $pdf->Ln();
            $pdf->Write(0, "Lat/Long: " . stripslashes($row["GuideLat"]) . " , " . stripslashes($row["GuideLong"]));
            $pdf->Ln(10);
        }

        $pdf->Output('waterways_report.pdf', 'D'); // 'D' forces download
    }
}
