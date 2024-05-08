<?php

// src/View/Requests/tmpl/default.php
defined('_JEXEC') or die;

echo '<h1>Waterways Guide Requests</h1>';

if (!empty($this->items)) {
    foreach ($this->items as $item) {
        echo '<h2>Member ID: ' . htmlspecialchars($item->memberid) . '</h2>';
        echo '<p>Country: ' . htmlspecialchars($item->GuideCountry) . '</p>';
        echo '<p>Waterway: ' . htmlspecialchars($item->GuideWaterway) . '</p>';
        echo '<p>Request Date: ' . htmlspecialchars($item->GuideRequestDate) . '</p>';
        echo '<p>Request Method: ' . htmlspecialchars($item->GuideRequestMethod) . '</p>';
        echo '<p>Request Status: ' . htmlspecialchars($item->GuideRequestStatus) . '</p>';
        echo '<hr>';
    }
}

echo $this->pagination->getListFooter();
