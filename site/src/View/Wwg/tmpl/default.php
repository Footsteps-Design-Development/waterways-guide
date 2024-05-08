<?php
// src/View/Wwg/tmpl/default.php
defined('_JEXEC') or die;

echo '<h1>Waterways Guide</h1>';

if (!empty($this->items)) {
    foreach ($this->items as $item) {
        echo '<h2>' . htmlspecialchars($item->GuideName) . '</h2>';
        echo '<p>' . htmlspecialchars($item->GuideSummary) . '</p>';
        echo '<p>Country: ' . htmlspecialchars($item->GuideCountry) . '</p>';
        echo '<p>Waterway: ' . htmlspecialchars($item->GuideWaterway) . '</p>';
        echo '<p>Location: ' . htmlspecialchars($item->GuideLatLong) . '</p>';
        echo '<p>Ref: ' . htmlspecialchars($item->GuideRef) . '</p>';
        echo '<p>Posted: ' . htmlspecialchars($item->GuidePostingDate) . '</p>';
        echo '<p>Updated: ' . htmlspecialchars($item->GuideUpdate) . '</p>';
        echo '<hr>';
    }
}

echo $this->pagination->getListFooter();
