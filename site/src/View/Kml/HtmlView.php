<?php

namespace Joomla\Component\WaterWaysGuide\Site\View\Kml;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\Component\WaterWaysGuide\Site\Helper\WaterwaysHelper;
use DOMDocument;

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

        $dom = new DOMDocument('1.0', 'UTF-8');
        $root = $dom->createElementNS('http://www.opengis.net/kml/2.2', 'kml');
        $parNode = $dom->appendChild($root);
        $dnode = $dom->createElement('Document');
        $docNode = $parNode->appendChild($dnode);

        foreach ($guides as $row) {
            $node = $dom->createElement('Placemark');
            $placeNode = $docNode->appendChild($node);

            $nameNode = $dom->createElement('name', htmlspecialchars(stripslashes($row["GuideName"])));
            $placeNode->appendChild($nameNode);

            $pointNode = $dom->createElement('Point');
            $placeNode->appendChild($pointNode);

            $coorStr = stripslashes($row["GuideLong"]) . ',' . stripslashes($row["GuideLat"]);
            $coorNode = $dom->createElement('coordinates', $coorStr);
            $pointNode->appendChild($coorNode);
        }

        header("Content-Disposition: attachment; filename='waterways.kml'");
        header("Content-type: application/vnd.google-earth.kml+xml");
        echo $dom->saveXML();
    }
}
