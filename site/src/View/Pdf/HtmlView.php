<?php

namespace Joomla\Component\WaterWaysGuide\Site\View\Pdf;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\Component\WaterWaysGuide\Site\Helper\WaterwaysHelper;
use TCPDF;

// Prevent direct access
defined('_JEXEC') or die;

class HtmlView extends HtmlView
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

        $pdf = new TCPDF();
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
