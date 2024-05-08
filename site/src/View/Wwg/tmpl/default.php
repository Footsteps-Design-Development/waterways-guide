<?php
// src/View/Wwg/tmpl/default.php
defined('_JEXEC') or die;

echo '<h1>Waterways Guide</h1>';

if (!empty($this->items)) {
    foreach ($this->items as $item) {
        echo '<p>' . htmlspecialchars($item->title) . '</p>';
    }
}

echo $this->pagination->getListFooter();
